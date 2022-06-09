<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Ibexa\Core\MVC\Symfony\Security\EventListener\SecurityListener as BaseSecurityListener;
use eZ\Bundle\EzPublishLegacyBundle\Security\SecurityListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SecurityListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(BaseSecurityListener::class)) {
            return;
        }

        $container->findDefinition(BaseSecurityListener::class)
            ->setClass(SecurityListener::class);
    }
}
