<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Ibexa\Core\MVC\Symfony\Controller\Content\PreviewController;
use eZ\Bundle\EzPublishLegacyBundle\Controller\PreviewController as LegacyPreviewController;
use eZ\Bundle\EzPublishLegacyBundle\Controller\SiteApiPreviewController as LegacySiteApiPreviewController;
use Netgen\Bundle\IbexaSiteApiBundle\Controller\PreviewController as SiteApiPreviewController;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class PreviewControllerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(PreviewController::class)) {
            return;
        }

        if (class_exists(SiteApiPreviewController::class)) {
            $container->findDefinition(PreviewController::class)
                ->setClass(LegacySiteApiPreviewController::class);

            return;
        }

        $container->findDefinition(PreviewController::class)
            ->setClass(LegacyPreviewController::class)
            ->addMethodCall('setConfigResolver', [new Reference('ibexa.config.resolver')]);
    }
}
