<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Security;

use Ibexa\Core\MVC\Symfony\Security\EventListener\SecurityListener as BaseListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class SecurityListener extends BaseListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        // In legacy_mode, roles and policies must be delegated to legacy kernel.
        if ($this->configResolver->getParameter('legacy_mode')) {
            return;
        }

        parent::onKernelRequest($event);
    }
}
