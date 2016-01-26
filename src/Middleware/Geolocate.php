<?php declare(strict_types=1);

namespace Psr7Middlewares\Middleware;

use RuntimeException;
use Psr7Middlewares\Middleware;
use Psr\Http\Message\{ServerRequestInterface, ResponseInterface};
use Ivory\HttpAdapter\FopenHttpAdapter;
use Geocoder\{
    Geocoder,
    ProviderAggregator,
    Provider\FreeGeoIp,
    Model\AddressCollection
};

/**
 * Middleware to geolocate the client using the ip.
 */
class Geolocate
{
    const KEY = 'GEOLOCATE';

    /**
     * @var Geocoder
     */
    private $geocoder;

    /**
     * Returns the client location.
     *
     * @param ServerRequestInterface $request
     *
     * @return AddressCollection|null
     */
    public static function getLocation(ServerRequestInterface $request)
    {
        return Middleware::getAttribute($request, self::KEY);
    }

    /**
     * Constructor. Set the geocoder instance.
     *
     * @param null|Geocoder $geocoder
     */
    public function __construct(Geocoder $geocoder = null)
    {
        if ($geocoder === null) {
            $geocoder = new ProviderAggregator();
            $geocoder->registerProvider(new FreeGeoIp(new FopenHttpAdapter()));
        }

        $this->geocoder = $geocoder;
    }

    /**
     * Execute the middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        if (!Middleware::hasAttribute($request, ClientIp::KEY)) {
            throw new RuntimeException('Geolocate middleware needs ClientIp executed before');
        }

        $ip = ClientIp::getIp($request);

        if ($ip !== null) {
            $request = Middleware::setAttribute($request, self::KEY, $this->geocoder->geocode($ip));
        }

        return $next($request, $response);
    }
}
