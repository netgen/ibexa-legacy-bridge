<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\SignalSlot;

use Ibexa\Core\SignalSlot\Signal;
use eZExpiryHandler;

/**
 * A legacy slot handling PublishContentTypeDraftSignal.
 */
class LegacyPublishContentTypeDraftSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \Ibexa\Core\SignalSlot\Signal $signal
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentTypeService\PublishContentTypeDraftSignal) {
            return;
        }

        $this->runLegacyKernelCallback(
            static function () use ($signal) {
                eZExpiryHandler::registerShutdownFunction();
                $handler = eZExpiryHandler::instance();
                $time = time();
                $handler->setTimestamp('user-class-cache', $time);
                $handler->setTimestamp('class-identifier-cache', $time);
                $handler->setTimestamp('sort-key-cache', $time);
                $handler->store();
            }
        );
    }
}
