<?php declare(strict_types=1);

namespace Psr7Middlewares\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Helper functions.
 */
class Helpers
{
    private static $hash_equals;

    /**
     * helper function to fix paths '//' or '/./' or '/foo/../' in a path.
     *
     * @param string $path Path to resolve
     *
     * @return string
     */
    public static function fixPath(string $path): string
    {
        $path = str_replace('\\', '/', $path); //windows paths
        $replace = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];

        do {
            $path = preg_replace($replace, '/', $path, -1, $n);
        } while ($n > 0);

        return $path;
    }

    /**
     * Join several pieces into a path.
     * 
     * @param string
     *               ...
     * 
     * @return string
     */
    public static function joinPath(): string
    {
        return self::fixPath(implode('/', func_get_args()));
    }

    /**
     * Check whether a request is or not ajax.
     * 
     * @param RequestInterface $request
     * 
     * @return bool
     */
    public static function isAjax(RequestInterface $request): bool
    {
        return strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    /**
     * Check if a request is post or any similar method.
     * 
     * @param RequestInterface $request
     * 
     * @return bool
     */
    public static function isPost(RequestInterface $request): bool
    {
        switch (strtoupper($request->getMethod())) {
            case 'GET':
            case 'HEAD':
            case 'CONNECT':
            case 'TRACE':
            case 'OPTIONS':
                return false;
        }

        return true;
    }

    /**
     * Check whether a response is a redirection.
     * 
     * @param ResponseInterface $response
     * 
     * @return bool
     */
    public static function isRedirect(ResponseInterface $response): bool
    {
        return in_array($response->getStatusCode(), [302, 301]);
    }

    /**
     * Return the output buffer.
     * 
     * @param int $level
     * 
     * @return string
     */
    public static function getOutput(int $level): string
    {
        $output = '';

        while (ob_get_level() >= $level) {
            $output .= ob_get_clean();
        }

        return $output;
    }
}
