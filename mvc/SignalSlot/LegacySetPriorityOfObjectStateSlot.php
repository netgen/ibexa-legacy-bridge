<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\SignalSlot;

use Ibexa\Core\SignalSlot\Signal;

/**
 * A legacy slot handling SetPriorityOfObjectStateSignal.
 */
class LegacySetPriorityOfObjectStateSlot extends AbstractLegacyObjectStateSlot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \Ibexa\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ObjectStateService\SetPriorityOfObjectStateSignal) {
            return;
        }

        parent::receive($signal);
    }
}
