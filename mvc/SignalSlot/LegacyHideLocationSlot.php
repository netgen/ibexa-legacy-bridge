<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\SignalSlot;

use Ibexa\Core\SignalSlot\Signal;
use eZContentObject;
use eZContentObjectTreeNode;
use eZSearch;

/**
 * A legacy slot handling HideLocationSignal.
 */
class LegacyHideLocationSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \Ibexa\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\LocationService\HideLocationSignal) {
            return;
        }

        $this->runLegacyKernelCallback(
            static function () use ($signal) {
                $node = eZContentObjectTreeNode::fetch($signal->locationId);
                eZContentObjectTreeNode::clearViewCacheForSubtree($node);
                eZSearch::updateNodeVisibility($signal->locationId, 'hide');
                eZContentObject::clearCache(); // Clear all object memory cache to free memory
            }
        );
    }
}
