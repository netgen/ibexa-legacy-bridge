<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Ibexa\Bundle\Core\Routing\DefaultRouter as BaseDefaultRouter;
use eZ\Bundle\EzPublishLegacyBundle\Routing\DefaultRouter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Pass modifying the default router to inject legacy aware routes.
 */
class RoutingPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('router.default')) {
            return;
        }

        $container->getDefinition('router.default')
            ->setClass(DefaultRouter::class)
            ->addMethodCall(
                'setLegacyAwareRoutes',
                ['%ezpublish.default_router.legacy_aware_routes%']
            );

        if ($container->hasDefinition(BaseDefaultRouter::class)) {
            $container->getDefinition(BaseDefaultRouter::class)
                ->setClass(DefaultRouter::class);
        }
    }
}
