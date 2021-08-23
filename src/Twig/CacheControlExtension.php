<?php declare(strict_types=1);


namespace GravitateNZ\fta\cache\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;

class CacheControlExtension extends AbstractExtension
{

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return TwigFunction[]
     * @codeCoverageIgnore
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('maxage', [$this, 'setMaxAge']),
            new TwigFunction('s_maxage', [$this, 'setSMaxAge']),
            new TwigFunction('surrogateControl', [$this, 'setSurrogateControl']),
            new TwigFunction('public', [$this, 'setPublic']),
            new TwigFunction('private', [$this, 'setPrivate']),
            new TwigFunction('disableAutoCacheControl', [$this, 'disableAutoCacheControl']),
            new TwigFunction('surrogate_keys', [$this, 'addSurrogateKeys']),
            new TwigFunction('do_not_cache', [$this, 'doNotCache']),
            new TwigFunction('no_cache', [$this, 'noCache']),
            new TwigFunction('no_store', [$this, 'noStore']),
            new TwigFunction('must_revalidate', [$this, 'mustRevalidate']),
        ];
    }

    /**
     * Add a cache control header
     *
     * @param string $name
     * @param $value
     */
    protected function setCacheControlOption(string $name, $value): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $request->attributes->set($name, $value);
    }

    /**
     * sets the max age
     *
     * @param int $maxage
     */
    public function setMaxAge(int $maxage): void
    {
        $this->setCacheControlOption('_maxage', $maxage);
    }

    /**
     * sets the shared max age
     * @param int $sMaxAge
     */
    public function setSMaxAge(int $sMaxAge): void
    {
        $this->setCacheControlOption('_s-maxage', $sMaxAge);
    }

    /**
     * allows us to set the object as public
     */
    public function setPublic(): void
    {
        $this->setCacheControlOption('_public', true);
    }

    /**
     * allows us to set the object as private
     */
    public function setPrivate(): void
    {
        $this->setCacheControlOption('_private', true);
    }

    /**
     * disables the symfony autocache control
     * DANGEROUS!!!
     */
    public function disableAutoCacheControl(): void
    {
        $this->setCacheControlOption('_auto-cache-control', false);
    }

    /**
     * Sets the surrogate key max age
     * @param int $surrogateMaxAge
     */
    public function setSurrogateMaxAge(int $surrogateMaxAge): void
    {
        $this->setCacheControlOption('_surrogate_maxage', $surrogateMaxAge);
    }

    public function surrogateNoStore(): void
    {
        $this->setCacheControlOption('_surrogate_no-store', true);
    }

    public function surrogateNoCache(): void
    {
        $this->setCacheControlOption('_surrogate_no-cache', true);
    }

    public function surrogateMustRevalidate(): void
    {
        $this->setCacheControlOption('_surrogate_must-revalidate', true);
    }

    /**
     * adds a surrogate key
     * @param array $newSurrogateTags
     */
    public function addSurrogateKeys(array $newSurrogateTags): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $surrogateKeys = $request->attributes->get('_surrogate_keys', []);
        $request->attributes->set('_surrogate_keys', array_merge($surrogateKeys, $newSurrogateTags));
    }

    public function mustRevalidate(): void
    {
        $this->setCacheControlOption('_must-revalidate', true);
    }

    public function noCache(): void
    {
        $this->setCacheControlOption('_no-cache', true);
    }

    public function noStore(): void
    {
        $this->setCacheControlOption('_no-store', true);
    }

    /**
     * Helper to tell things to to even attempt to cache this page
     */
    public function doNotCache(): void
    {
        $this->disableAutoCacheControl();
        $this->setMaxAge(0);
        $this->setSurrogateMaxAge(0);
        $this->noStore();
        $this->setPrivate();
        $this->surrogateNoStore();
    }

}