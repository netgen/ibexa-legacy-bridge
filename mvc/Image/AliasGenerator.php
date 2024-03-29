<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Image;

use Ibexa\Contracts\Core\Variation\VariationHandler;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo;
use Ibexa\Contracts\Core\Variation\Values\ImageVariation;
use Ibexa\Contracts\Core\Repository\Exceptions\InvalidVariationException;
use eZContentObjectAttribute;
use eZImageAliasHandler;
use Closure;

class AliasGenerator implements VariationHandler
{
    /**
     * @var \Closure
     */
    private $kernelClosure;

    /**
     * @var \eZImageAliasHandler[]
     */
    private $aliasHandlers;

    /**
     * Image variation objects, indexed by <fieldId>-<versionNo>-<variationName>.
     * Storing them avoids to run the legacy kernel each time if there are similar images variations required.
     *
     * @var \Ibexa\Contracts\Core\Variation\Values\ImageVariation[]
     */
    private $variations;

    public function __construct(Closure $legacyKernelClosure)
    {
        $this->kernelClosure = $legacyKernelClosure;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $kernelClosure = $this->kernelClosure;

        return $kernelClosure();
    }

    /**
     * Returns an image variation object.
     * Variation creation will be done through the legacy eZImageAliasHandler, using the legacy kernel.
     *
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\Field $field
     * @param \Ibexa\Contracts\Core\Repository\Values\Content\VersionInfo $versionInfo
     * @param string $variationName
     * @param array $parameters
     *
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidVariationException
     *
     * @return \Ibexa\Contracts\Core\Variation\Values\ImageVariation
     */
    public function getVariation(Field $field, VersionInfo $versionInfo, $variationName, array $parameters = [])
    {
        $variationIdentifier = "$field->id-$versionInfo->versionNo-$variationName";
        if (isset($this->variations[$variationIdentifier])) {
            return $this->variations[$variationIdentifier];
        }

        // Assigning by reference to be able to modify those arrays within the closure (due to PHP 5.3 limitation with access to $this)
        $allAliasHandlers = &$this->aliasHandlers;
        $allVariations = &$this->variations;

        return $this->getLegacyKernel()->runCallback(
            static function () use ($field, $versionInfo, $variationName, &$allAliasHandlers, &$allVariations, $variationIdentifier) {
                $aliasHandlerIdentifier = "$field->id-$versionInfo->versionNo";
                if (!isset($allAliasHandlers[$aliasHandlerIdentifier])) {
                    $allAliasHandlers[$aliasHandlerIdentifier] = new eZImageAliasHandler(
                        eZContentObjectAttribute::fetch($field->id, $versionInfo->versionNo)
                    );
                }

                /** @var $imageAliasHandler \eZImageAliasHandler */
                $imageAliasHandler = $allAliasHandlers[$aliasHandlerIdentifier];
                $aliasArray = $imageAliasHandler->imageAlias($variationName);
                if ($aliasArray === null) {
                    throw new InvalidVariationException($variationName, 'image');
                }

                $allVariations[$variationIdentifier] = new ImageVariation(
                    [
                        'name' => $variationName,
                        'fileName' => $aliasArray['filename'],
                        'dirPath' => $aliasArray['dirpath'],
                        'fileSize' => isset($aliasArray['filesize']) ? $aliasArray['filesize'] : 0,
                        'mimeType' => $aliasArray['mime_type'],
                        'lastModified' => new \DateTime('@' . $aliasArray['timestamp']),
                        'uri' => $aliasArray['url'],
                        'width' => $aliasArray['width'],
                        'height' => $aliasArray['height'],
                        'imageId' => sprintf('%d-%d', $versionInfo->contentInfo->id, $field->id),
                    ]
                );

                return $allVariations[$variationIdentifier];
            },
            false,
            false
        );
    }
}
