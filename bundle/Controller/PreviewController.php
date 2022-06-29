<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Symfony\Controller\Content\PreviewController as BasePreviewController;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;

class PreviewController extends BasePreviewController
{
    /**
     * @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @param \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    protected function getForwardRequest(Location $location, Content $content, SiteAccess $previewSiteAccess, Request $request, $language)
    {
        $request = parent::getForwardRequest($location, $content, $previewSiteAccess, $request, $language);
        // If the preview siteaccess is configured in legacy_mode, we forward to the LegacyKernelController.
        if ($this->configResolver->getParameter('legacy_mode', 'ibexa.site_access.config', $previewSiteAccess->name)) {
            $request->attributes->set('_controller', 'ezpublish_legacy.controller:indexAction');
        }

        return $request;
    }
}
