<?php namespace DazLab\Usersnap\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectUsersnap
{

    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var \Illuminate\Http\Response $response */
        $response = $next($request);

        if (
            ($response->headers->has('Content-Type') &&
                strpos($response->headers->get('Content-Type'), 'html') === false)
            || $request->getRequestFormat() !== 'html'
            || $response->getContent() === false
            || $this->isJsonRequest($request)
        ) {
            return $response;
        }

        $this->injectUsersnap($response);

        return $response;
    }

    /**
     * Injects the Usersnap into the given Response.
     *
     * @param Response $response A Response instance
     * Based on https://github.com/symfony/WebProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
     * @throws Exception
     */
    public function injectUsersnap(Response $response)
    {
        $content = $response->getContent();

        if (!($appKey = config('usersnap.app_key')) && config('usersnap.enabled') !== false) {
            throw new Exception('Please set Usersnap app key in environment');
        }

        $userSnapInitParams = '';
        $user = null;
        if (app()->resolved('user.auth') && $auth = app()->make('user.auth')) {
            $user = $auth->user();
        }

        if ($user && !empty($user->email)) {
            $userSnapInitParams = "api.on('open', function(event) {
                event.api.setValue('visitor', '{$user->email}');
            });";
        }

        $newContent = "\n<script type=\"text/javascript\">\nwindow.onUsersnapCXLoad = function(api) { api.init(); $userSnapInitParams };
        var script = document.createElement('script');
        script.async = 1;
        script.src = 'https://widget.usersnap.com/load/" . $appKey . "?onload=onUsersnapCXLoad';
        document.getElementsByTagName('head')[0].appendChild(script);\n</script>\n";

        $pos = strripos($content, '</body>');
        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $newContent . substr($content, $pos);
        } else {
            $content = $content . $newContent;
        }

        // Update the new content and reset the content length
        $response->setContent($content);
        $response->headers->remove('Content-Length');
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isJsonRequest(Request $request)
    {
        // If XmlHttpRequest, return true
        if ($request->isXmlHttpRequest()) {
            return true;
        }

        // Check if the request wants Json
        $acceptable = $request->getAcceptableContentTypes();
        return (isset($acceptable[0]) && $acceptable[0] == 'application/json');
    }
}
