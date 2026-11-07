<?php
use App\Http\Middleware\EnsureDealershipAdmin;
use App\Http\Middleware\RequestTrace;
use App\Http\Middleware\SecurityHeaders;
use App\Support\ErrorResponder;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function(Middleware $middleware): void {
        $middleware->alias(['admin'=>EnsureDealershipAdmin::class]);
        $middleware->prepend(RequestTrace::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->redirectGuestsTo(fn()=>route('manage.login'));
    })
    ->withExceptions(function(Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn(Request $r) => $r->is('api/*')||$r->expectsJson());

        // Laravel receives requests rejected by its PHP POST-size middleware.
        // Apache or a hosting proxy may reject larger requests before PHP runs.
        $exceptions->render(function(PostTooLargeException $e, Request $request) {
            Log::warning('Request exceeds PHP post_max_size', [
                'request_id' => $request->attributes->get('carfolk_request_id'),
                'path' => $request->path(),
                'content_length' => (int) $request->server('CONTENT_LENGTH', 0),
            ]);
            return null; // Keep the framework's 413 status and exception lifecycle.
        });

        $exceptions->respond(function(Response $response): Response {
            $request = request();
            $status = $response->getStatusCode();
            if(in_array($status, [404, 413, 429, 500, 503], true)
                && !($status >= 500 && config('app.debug'))) {
                return ErrorResponder::render($request, $response);
            }
            if($reference = $request->attributes->get('carfolk_request_id')) {
                $response->headers->set('X-Request-ID', $reference);
            }
            return $response;
        });
    })
    ->create();
