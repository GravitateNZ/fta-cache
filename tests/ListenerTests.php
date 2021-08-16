<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 17/07/18
 * Time: 2:02 PM
 */

namespace GravitateNZ\fta\csp\Tests;


use GravitateNZ\fta\cache\Twig\CacheControlExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use GravitateNZ\fta\cache\Event\CacheControlListener;

/**
 * @covers \GravitateNZ\fta\cache\Twig\CacheControlExtension
 * @covers \GravitateNZ\fta\cache\Event\CacheControlListener
 */
class ListenerTests extends TestCase
{

    protected HttpKernelInterface $kernel;
    protected RequestStack $requestStack;
    protected CacheControlExtension $extension;

    public function setUp(): void
    {
        $this->kernel = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $this->requestStack = new RequestStack();
        $this->requestStack->push(new Request([], [], []));
        $this->extension = new CacheControlExtension($this->requestStack);
    }

    protected function getEvent(): ResponseEvent
    {
        $request = $this->requestStack->getMasterRequest();
        $response = new Response('', 200, []);
        return new ResponseEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST, $response);
    }


    public function testSetMaxAge(): void
    {

        $this->extension->setMaxAge(0);

        $subscriber = new CacheControlListener();
        $event = $this->getEvent();
        $subscriber->onKernelResponse($event);

        $this->assertEquals(0, $event->getResponse()->getMaxAge());

        $this->assertStringContainsString("max-age=0", $event->getResponse()->headers->get('cache-control'));
    }


    public function testSharedMaxAge(): void
    {

        $this->extension->setSMaxAge(0);

        $subscriber = new CacheControlListener();
        $event = $this->getEvent();
        $subscriber->onKernelResponse($event);

        $this->assertEquals(0, $event->getResponse()->getMaxAge());

        $this->assertStringContainsString("s-maxage=0", $event->getResponse()->headers->get('cache-control'));

    }


    public function testSetPublic(): void
    {
        $this->extension->setPublic();

        $subscriber = new CacheControlListener();
        $event = $this->getEvent();
        $subscriber->onKernelResponse($event);

        $this->assertTrue($event->getResponse()->headers->getCacheControlDirective('public'));
        $this->assertNull($event->getResponse()->headers->getCacheControlDirective('private'));
    }

    public function testSetPrivate(): void
    {
        $this->extension->setPrivate();

        $subscriber = new CacheControlListener();
        $event = $this->getEvent();
        $subscriber->onKernelResponse($event);

        $this->assertNull($event->getResponse()->headers->getCacheControlDirective('public'));
        $this->assertTrue($event->getResponse()->headers->getCacheControlDirective('private'));

    }

    public function testAddSurrogateKeys(): void
    {
        $this->extension->addSurrogateKeys([
            'eep', 'opp', 'ork', 'ah-ah'
        ]);

        $subscriber = new CacheControlListener();
        $event = $this->getEvent();
        $subscriber->onKernelResponse($event);

        $this->assertTrue($event->getResponse()->headers->has('Surrogate-Key'));
        $this->assertTrue($event->getResponse()->headers->contains('Surrogate-Key', 'eep'));
        $this->assertTrue($event->getResponse()->headers->contains('Surrogate-Key', 'opp'));
        $this->assertTrue($event->getResponse()->headers->contains('Surrogate-Key', 'ork'));
        $this->assertTrue($event->getResponse()->headers->contains('Surrogate-Key', 'ah-ah'));

    }


    public function testSurrogateMaxAge(): void
    {
        $this->extension->setSurrogateMaxAge(0);

        $subscriber = new CacheControlListener();
        $event = $this->getEvent();
        $subscriber->onKernelResponse($event);

        $this->assertStringContainsString("max-age=0", $event->getResponse()->headers->get('surrogate-control'));


    }
    public function testSubscribedEvents(): void
    {
        $this->assertEquals(array(KernelEvents::RESPONSE => 'onKernelResponse'), CacheControlListener::getSubscribedEvents());
    }

    public function testDisableAutoCacheControl(): void{
        $this->extension->disableAutoCacheControl();

        $subscriber = new CacheControlListener();
        $event = $this->getEvent();
        $subscriber->onKernelResponse($event);

        $this->assertTrue($event->getResponse()->headers->has(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER));
    }
}
