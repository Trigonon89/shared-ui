<?php

namespace Trigonon\SharedUi\ErrorTracking;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Records uncaught exceptions ("hard abends") to the error_logs table and
 * emails an alert. Registered per-app from bootstrap/app.php:
 *
 *   ->withExceptions(function (Exceptions $exceptions) {
 *       HardAbendRecorder::register($exceptions);
 *   })
 *
 * Laravel already skips reporting for routine 4xx/validation/auth exceptions
 * (see Handler::$internalDontReport), so this only fires for genuine
 * unhandled errors — the ones that would otherwise just show an error page.
 */
class HardAbendRecorder
{
    public static function register(Exceptions $exceptions): void
    {
        $exceptions->reportable(function (Throwable $e) {
            static::record($e, request());
        });
    }

    public static function record(Throwable $e, ?Request $request = null): void
    {
        try {
            $errorLog = ErrorLog::create([
                'app' => config('app.name'),
                'environment' => app()->environment(),
                'exception_class' => get_class($e),
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

            return;
        }

        static::notify($errorLog);
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
