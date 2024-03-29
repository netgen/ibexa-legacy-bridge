<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\EventListener;

use eZ\Publish\Core\MVC\Legacy\Kernel\Loader;
use Ibexa\Core\MVC\Symfony\Event\ScopeChangeEvent;
use Ibexa\Core\MVC\Symfony\MVCEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConfigScopeListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\Core\MVC\Legacy\Kernel\Loader
     */
    private $kernelLoader;

    public function __construct(Loader $kernelLoader)
    {
        $this->kernelLoader = $kernelLoader;
    }

    public static function getSubscribedEvents()
    {
        return [
            MVCEvents::CONFIG_SCOPE_CHANGE => 'onConfigScopeChange',
            MVCEvents::CONFIG_SCOPE_RESTORE => 'onConfigScopeChange',
        ];
    }

    public function onConfigScopeChange(ScopeChangeEvent $event)
    {
        $this->kernelLoader->resetKernel();
    }
}
