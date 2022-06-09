<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\FieldType\Image\IO;

use Ibexa\Core\IO\IOServiceInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

/**
 * Factory for the Legacy Image IOService.
 * Sets options using the ConfigResolver.
 */
class IOServiceFactory
{
    /** @var \Ibexa\Core\IO\IOServiceInterface */
    private $publishedIOService;

    /** @var \Ibexa\Core\IO\IOServiceInterface */
    private $draftIOService;

    /** @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface */
    private $configResolver;

    public function __construct(IOServiceInterface $publishedIOService, IOServiceInterface $draftIOService, ConfigResolverInterface $configResolver)
    {
        $this->draftIOService = $draftIOService;
        $this->publishedIOService = $publishedIOService;
        $this->configResolver = $configResolver;
    }

    /**
     * Builds the IOService from $class.
     *
     * @param string $class
     *
     * @return \Ibexa\Core\IO\IOServiceInterface
     */
    public function buildService($class)
    {
        $options = [
            'var_dir' => $this->configResolver->getParameter('var_dir'),
            'storage_dir' => $this->configResolver->getParameter('storage_dir'),
            'draft_images_dir' => $this->configResolver->getParameter('image.versioned_images_dir'),
            'published_images_dir' => $this->configResolver->getParameter('image.published_images_dir'),
        ];

        return new $class($this->publishedIOService, $this->draftIOService, $options);
    }
}
