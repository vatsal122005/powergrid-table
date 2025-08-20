<?php

namespace App\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErrorController extends Controller
{
    public function trigger()
    {
        try {
            // Fake error
            throw new \Exception("Test error from Bugsnag + Telescope demo 🚨");
        } catch (\Exception $e) {
            // Log for Telescope + storage/logs
            Log::error('Manually triggered error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // Send to Bugsnag
            Bugsnag::notifyException($e, function ($report) {
                $report->setMetaData([
                    'demo' => [
                        'context' => 'Triggered in TestErrorController@trigger',
                        'user'    => auth()->guard()->id(),
                    ],
                ]);
            });

            return response()->json(['status' => 'Error triggered & sent to Bugsnag'], 500);
        }
    }
}
