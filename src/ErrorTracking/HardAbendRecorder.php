<?php

namespace Trigonon\SharedUi\ErrorTracking;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

/**
 * Records uncaught exceptions ("hard abends") to the error_logs table and
 * emails an alert, and renders formatted error pages for 500/403/404.
 * Registered per-app from bootstrap/app.php:
 *
 *   ->withExceptions(function (Exceptions $exceptions) {
 *       HardAbendRecorder::register($exceptions);
 *   })
 *
 * Laravel already skips its own reportable() hook for routine HTTP exceptions
 * (403/404/419/422/etc. all extend Symfony's HttpException, which is in
 * Handler::$internalDontReport) — so genuine unhandled 500s get recorded via
 * the reportable() callback below, while 403s are recorded explicitly in the
 * render() callback instead, since that's the only hook that ever sees them.
 *
 * 404s are NOT recorded/emailed by default (too high-volume: bots, dead
 * links, scanners) — they only get the formatted page. The exception is a
 * 404 hit by an authenticated user, which is a real signal (a broken internal
 * link, a stale bookmark after a route rename) rather than noise, so those
 * ARE recorded and emailed like a 403 would be.
 */
class HardAbendRecorder
{
    public static function register(Exceptions $exceptions): void
    {
        $recordedIds = [];

        $exceptions->reportable(function (Throwable $e) use (&$recordedIds) {
            $errorLog = static::record($e, request());

            if ($errorLog) {
                $recordedIds[spl_object_id($e)] = $errorLog->id;
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) use (&$recordedIds) {
            return static::renderErrorPage($e, $request, $recordedIds);
        });
    }

    protected static function renderErrorPage(Throwable $e, Request $request, array &$recordedIds): mixed
    {
        // API/JSON consumers keep their normal JSON error responses.
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        if ($status === 404) {
            // Only worth recording when a logged-in user hits it — anonymous
            // 404 traffic is almost entirely bots/scanners/dead links.
            $errorId = $request->user()
                ? static::record($e, $request)?->id
                : null;

            return response()->view('shared-ui::errors.404', ['errorId' => $errorId], 404);
        }

        // Leave every other routine HTTP status (401/419/422/429/etc.) to the
        // app's own renderers or Laravel's defaults.
        if ($status !== 403 && $status < 500) {
            return null;
        }

        // Let local dev see the full debug trace for genuine crashes.
        if ($status >= 500 && config('app.debug')) {
            return null;
        }

        // A real 500 crash was already recorded via the reportable() callback
        // above (same exception instance) — don't record it twice. A 403
        // never reaches reportable() (HttpException is in Laravel's internal
        // dontReport list), so it's recorded here for the first time.
        $errorId = $recordedIds[spl_object_id($e)] ?? static::record($e, $request)?->id;

        return response()->view(
            $status === 403 ? 'shared-ui::errors.403' : 'shared-ui::errors.hard-abend',
            ['errorId' => $errorId],
            $status
        );
    }

    public static function record(Throwable $e, ?Request $request = null): ?ErrorLog
    {
        try {
            $errorLog = ErrorLog::create([
                'app' => config('app.name'),
                'environment' => app()->environment(),
                'exception_class' => get_class($e),
                'status_code' => $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500,
                'message' => Str::limit($e->getMessage(), 2000),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'user_id' => $request?->user()?->id,
                'account_id' => $request?->user()?->account_id,
                'ip_address' => $request?->ip(),
            ]);
        } catch (Throwable $recordingFailure) {
            Log::error('HardAbendRecorder failed to record error: '.$recordingFailure->getMessage());

            return null;
        }

        static::notify($errorLog);

        return $errorLog;
    }

    protected static function notify(ErrorLog $errorLog): void
    {
        $to = config('shared-ui.error_alert_email');

        if (! $to) {
            return;
        }

        try {
            Mail::to($to)->send(new HardAbendMail($errorLog));
            $errorLog->update(['notified_at' => now()]);
        } catch (Throwable $mailFailure) {
            Log::error('HardAbendRecorder failed to send alert email: '.$mailFailure->getMessage());
        }
    }
}
