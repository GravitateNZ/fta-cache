<?php declare(strict_types=1);


namespace GravitateNZ\fta\cache\Event;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheControlListener implements EventSubscriberInterface
{

    public function onKernelResponse(ResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        if ($request->attributes->getBoolean('_private', false)) {
            $response->setPrivate();
        }

        if ($request->attributes->getBoolean('_public', false)) {
            $response->setPublic();
        }

        if (!$request->attributes->getBoolean('_auto-cache-control', true)) {
            $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, false);
        }

        if ("" !== $maxAge = $request->attributes->getDigits('_maxage')) {
            $response->setMaxAge((int) $maxAge);
        }

        if ("" !== $sMaxAge = $request->attributes->getDigits('_s-maxage')) {
            $response->setSharedMaxAge((int) $sMaxAge);
        }

        if ($request->attributes->getBoolean('_no-cache')) {
            $response->headers->addCacheControlDirective('no-cache', true);
        }

        if ($request->attributes->getBoolean('_no-store')) {
            $response->headers->addCacheControlDirective('no-store', true);
        }

        if ($request->attributes->getBoolean('_must-revalidate')) {
            $response->headers->addCacheControlDirective('must-revalidate', true);
        }

        $surrogateMaxAge = $request->attributes->getDigits('_surrogate_maxage');
        $surrogateNoStore = $request->attributes->getBoolean('_surrogate_no-store');
        $surrogateNoCache = $request->attributes->getBoolean('_surrogate_no-cache');
        $surrogateMustRevalidate = $request->attributes->getBoolean('_surrogate_must-revalidate');


        if ($surrogateNoStore || $surrogateMaxAge >= 0 || $surrogateNoCache || $surrogateMustRevalidate) {

            $surrogateOptions = [];
            if ($surrogateMaxAge >= 0) {
                $surrogateOptions[] = "max-age=$surrogateMaxAge";
            }


            if ($surrogateNoStore) {
                $surrogateOptions[] = "no-store";
            }

            if ($surrogateNoCache) {
                $surrogateOptions[] = "no-cache";
            }

            if ($surrogateMustRevalidate) {
                $surrogateOptions[] = "must-revalidate";
            }

            $response->headers->set('Surrogate-Control', implode(', ', $surrogateOptions));

        }

        if ($surrogateKey = $request->attributes->get('_surrogate_keys')) {
            $response->headers->set('Surrogate-Key', $surrogateKey);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onKernelResponse'];
    }

}