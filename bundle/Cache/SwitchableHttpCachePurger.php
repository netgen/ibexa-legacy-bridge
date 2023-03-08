<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Cache;

use Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface;
use Ibexa\Contracts\HttpCache\Handler\ContentTagInterface;

/**
 * A PurgeClient decorator that allows the actual purger to be switched on/off.
 */
class SwitchableHttpCachePurger implements PurgeClientInterface
{
    use Switchable;

    /**
     * @var \Ibexa\Contracts\HttpCache\PurgeClient\PurgeClientInterface
     */
    private $purgeClient;

    public function __construct(PurgeClientInterface $purgeClient)
    {
        $this->purgeClient = $purgeClient;
    }

    public function purge(array $locationIds): void
    {
        if ($this->isSwitchedOff()) {
            return;
        }

        // Add the appropiate cache tag prefix for locations
        $locationIds = array_map(
            static function ($locationId) {
                $locationId = is_numeric($locationId) ? ContentTagInterface::LOCATION_PREFIX . $locationId : $locationId;
                return $locationId;
            },
            $locationIds
        );

        $this->purgeClient->purge($locationIds);
    }

    public function purgeAll(): void
    {
        if ($this->isSwitchedOff()) {
            return;
        }

        $this->purgeClient->purgeAll();
    }

    /**
     * Implemented for BC with deprecated PurgeClientInterface::purgeForContent from eZ kernel.
     *
     * @param int $contentId
     * @param array $locationIds
     */
    public function purgeForContent($contentId, $locationIds = [])
    {
        if ($this->isSwitchedOff() || !method_exists($this->purgeClient, 'purgeForContent')) {
            return;
        }

        $this->purgeClient->purgeForContent($contentId, $locationIds);
    }
}
