<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Ibexa\Bundle\Core\EventListener\IndexRequestListener as BaseIndexRequestListener;
use eZ\Bundle\EzPublishLegacyBundle\EventListener\IndexRequestListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RequestIndexListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(BaseIndexRequestListener::class)) {
            return;
        }

        $container->findDefinition(BaseIndexRequestListener::class)
            ->setClass(IndexRequestListener::class);
    }
}
