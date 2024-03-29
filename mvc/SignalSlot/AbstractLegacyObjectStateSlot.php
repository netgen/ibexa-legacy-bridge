<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\SignalSlot;

use Ibexa\Core\SignalSlot\Signal;
use eZCache;

/**
 * An abstract legacy slot common for some ObjectStateService signals.
 */
abstract class AbstractLegacyObjectStateSlot extends AbstractLegacySlot
{
    /**
     * Clears object state limitation cache.
     *
     * Concrete implementation of this class should take care of checking the type of the signal.
     *
     * @param \Ibexa\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        $this->runLegacyKernelCallback(
            static function () {
                // Passing null as $cacheItem parameter is not used by this method
                eZCache::clearStateLimitations(null);
            }
        );
    }
}
