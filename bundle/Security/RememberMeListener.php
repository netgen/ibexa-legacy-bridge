<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Security;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Security\Http\Firewall\RememberMeListener as BaseRememberMeListener;

class RememberMeListener extends BaseRememberMeListener
{
    /**
     * @var ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @param ConfigResolverInterface $configResolver
     */
    public function setConfigResolver(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function handle(ResponseEvent $event)
    {
        // In legacy_mode, "remember me" must be delegated to legacy kernel.
        if ($this->configResolver->getParameter('legacy_mode')) {
            return;
        }

        parent::handle($event);
    }
}
