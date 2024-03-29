<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\SignalSlot;

use Ibexa\Core\SignalSlot\Signal;
use eZContentCacheManager;
use eZRole;

/**
 * A legacy slot handling AssignUserToUserGroupSignal.
 */
class LegacyAssignUserToUserGroupSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \Ibexa\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\UserService\AssignUserToUserGroupSignal) {
            return;
        }

        $this->runLegacyKernelCallback(
            static function () {
                eZContentCacheManager::clearAllContentCache();
                eZRole::expireCache();
            }
        );
    }
}
