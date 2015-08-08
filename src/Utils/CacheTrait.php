<?php
namespace Psr7Middlewares\Utils;

use RuntimeException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Utilities used by router middlewares
 */
trait CacheTrait
{
    /**
     * Check whether the response can be cached or not
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return boolean
     */
    protected static function isCacheable(ServerRequestInterface $request, ResponseInterface $response)
    {
        if ($response->getMethod() !== 'GET') {
            return false;
        }

        if (!in_array($response->getStatusCode(), [200, 203, 300, 301, 302, 404, 410])) {
            return false;
        }

        //Check http headers
        $cache = static::parseCacheControl($response->getHeaderLine('Cache-Control'));

        if (in_array('no-cache', $cache) || in_array('no-store', $cache) || in_array('private', $cache)) {
            return false;
        }

        return true;
    }

    /**
     * Write the stream to the given path
     *
     * @param StreamInterface $stream
     * @param string          $path
     */
    protected static function writeStream(StreamInterface $stream, $path)
    {
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $handle = fopen($path, 'wb+');

        if (false === $handle) {
            throw new RuntimeException('Unable to write to designated path');
        }

        $stream->rewind();

        while (!$stream->eof()) {
            fwrite($handle, $stream->read(4096));
        }

        fclose($handle);
    }

    /**
     * Parses and returns the cache-control header values
     *
     * @param string $header
     *
     * @return array
     */
    protected static function parseCacheControl($header)
    {
        $cache = [];

        foreach (array_map('trim', explode(',', strtolower($header))) as $part) {
            if (strpos($part, '=') === false) {
                $cache[$part] = true;
            } else {
                $part = array_map('trim', explode('=', $part, 2));
                $cache[$part[0]] = $part[1];
            }
        }

        return $cache;
    }
}