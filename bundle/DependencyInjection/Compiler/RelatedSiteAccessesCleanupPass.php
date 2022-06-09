<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to cleanup related siteaccess, i.e. remove from relation map those in legacy mode.
 */
class RelatedSiteAccessesCleanupPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $configResolver = $container->get('ibexa.config.resolver');
        $relationMap = $container->getParameter('ibexa.site_access.relation_map');

        // Exclude siteaccesses in legacy_mode (e.g. admin interface)
        foreach ($relationMap as $repository => &$saByRootLocation) {
            foreach ($saByRootLocation as $rootLocation => $saList) {
                foreach ($saList as $i => $sa) {
                    if ($configResolver->getParameter('legacy_mode', 'ibexa.site_access.config', $sa) === true) {
                        unset($saByRootLocation[$rootLocation][$i]);
                    }
                }
            }
        }
        $container->setParameter('ibexa.site_access.relation_map', $relationMap);

        $saList = $container->getParameter('ibexa.site_access.list');
        foreach ($saList as $sa) {
            if ($configResolver->getParameter('legacy_mode', 'ibexa.site_access.config', $sa) === true) {
                continue;
            }

            $relatedSAs = $configResolver->getParameter('related_siteaccesses', 'ibexa.site_access.config', $sa);
            foreach ($relatedSAs as $i => $relatedSa) {
                if ($configResolver->getParameter('legacy_mode', 'ibexa.site_access.config', $relatedSa) === true) {
                    unset($relatedSAs[$i]);
                }
            }
            $container->setParameter("ibexa.site_access.config.$sa.related_siteaccesses", $relatedSAs);
        }
    }
}
