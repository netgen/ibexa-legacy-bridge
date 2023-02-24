<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Controller;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Netgen\Bundle\IbexaSiteApiBundle\Controller\PreviewController as BasePreviewController;
use Ibexa\Core\MVC\Symfony\SiteAccess;
use Symfony\Component\HttpFoundation\Request;

class SiteApiPreviewController extends BasePreviewController
{
    protected function getForwardRequest(Location $location, Content $content, SiteAccess $previewSiteAccess, Request $request, $language): Request
    {
        $request = parent::getForwardRequest($location, $content, $previewSiteAccess, $request, $language);
        // If the preview siteaccess is configured in legacy_mode, we forward to the LegacyKernelController.
        if ($this->configResolver->getParameter('legacy_mode', 'ibexa.site_access.config', $previewSiteAccess->name)) {
            $request->attributes->set('_controller', 'ezpublish_legacy.controller:indexAction');
        }

        return $request;
    }
}
