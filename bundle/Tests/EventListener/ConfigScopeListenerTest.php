<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Tests\EventListener;

use eZ\Bundle\EzPublishLegacyBundle\EventListener\ConfigScopeListener;
use Ibexa\Core\MVC\Symfony\Event\ScopeChangeEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Legacy\Kernel\Loader;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use PHPUnit\Framework\TestCase;

class ConfigScopeListenerTest extends TestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                MVCEvents::CONFIG_SCOPE_CHANGE => 'onConfigScopeChange',
                MVCEvents::CONFIG_SCOPE_RESTORE => 'onConfigScopeChange',
            ],
            ConfigScopeListener::getSubscribedEvents()
        );
    }

    public function testOnConfigScopeChange()
    {
        $kernelLoader = $this->getKernelLoaderMock();
        $kernelLoader->expects($this->once())->method('resetKernel');

        $listener = new ConfigScopeListener($kernelLoader);
        $siteAccess = new SiteAccess('test');
        $event = new ScopeChangeEvent($siteAccess);
        $listener->onConfigScopeChange($event);
        $this->assertSame($siteAccess, $event->getSiteAccess());
    }

    private function getKernelLoaderMock()
    {
        return $this->createMock(Loader::class);
    }
}
