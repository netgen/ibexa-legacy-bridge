<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\SignalSlot;

use Ibexa\Core\SignalSlot\Signal;
use eZContentCacheManager;
use eZContentObject;
use eZContentOperationCollection;

/**
 * A legacy slot handling CopyContentSignal.
 */
class LegacyCopyContentSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \Ibexa\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\CopyContentSignal) {
            return;
        }

        $this->runLegacyKernelCallback(
            static function () use ($signal) {
                eZContentCacheManager::clearContentCacheIfNeeded($signal->dstContentId);
                eZContentOperationCollection::registerSearchObject($signal->dstContentId);
                eZContentObject::clearCache(); // Clear all object memory cache to free memory
            }
        );
    }
}
