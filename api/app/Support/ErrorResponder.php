<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class ErrorResponder
{
    /**
     * Safe public error messages: never expose stack traces or server internals.
     */
    public static function render(Request $request, Response $original): Response
    {
        $status = $original->getStatusCode();
        $photoUpload = $request->is('manage/vehicles/*/photos')
            || $request->is('api/v1/admin/vehicles/*/images');

        [$code, $title, $message] = match ($status) {
            404 => ['NOT_FOUND', 'Page not found', 'The requested page or resource is unavailable.'],
            413 => ['PAYLOAD_TOO_LARGE', 'Upload too large', $photoUpload
                ? 'Your photos exceed the server upload limit. Select fewer or smaller JPEG, PNG or WebP photos and try again. Each photo must be 8 MB or less.'
                : 'The submitted request is too large. Reduce its size and try again.'],
            429 => ['RATE_LIMITED', 'Too many requests', 'You have made too many attempts. Please try again shortly.'],
            500 => ['SERVER_ERROR', 'Something went wrong', 'We could not complete your request. Please try again or contact Carfolk support.'],
            503 => ['SERVICE_UNAVAILABLE', 'Temporarily unavailable', 'The dealership service is temporarily unavailable. Please try again shortly.'],
            default => ['REQUEST_ERROR', 'Request unsuccessful', 'We could not complete this request.'],
        };

        $reference = (string) $request->attributes->get('carfolk_request_id', '');
        $data = [
            'code' => $code,
            'title' => $title,
            'message' => $message,
            'reference' => $reference,
            'status' => $status,
            'isAdmin' => $request->is('manage/*'),
        ];

        if ($request->is('api/*') || $request->expectsJson()) {
            $response = response()->json([
                'message' => $message,
                'error' => ['code' => $code, 'request_id' => $reference],
            ], $status);
        } else {
            $response = response()->view('errors.carfolk', $data, $status);
        }

        $response->headers->set('Cache-Control', 'no-store');
        if ($reference !== '') {
            $response->headers->set('X-Request-ID', $reference);
        }
        if ($original->headers->has('Retry-After')) {
            $response->headers->set('Retry-After', $original->headers->get('Retry-After'));
        }

        return $response;
    }
}
