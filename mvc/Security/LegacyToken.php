<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Legacy\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Security token for legacy mode usage.
 * Wraps the real token and ensures it is always marked as authenticated, authentication being done by legacy kernel.
 *
 * DO NOT USE OUTSIDE OF LEGACY MODE.
 */
class LegacyToken implements TokenInterface
{
    /**
     * @var TokenInterface
     */
    private $innerToken;

    public function __construct(TokenInterface $innerToken)
    {
        $this->innerToken = $innerToken;
    }

    public function serialize(): string
    {
        return serialize($this->__serialize());
    }

    public function unserialize($serialized)
    {
        $this->__unserialize(is_array($serialized) ? $serialized : unserialize($serialized));
    }

    public function __serialize(): array
    {
        return [
            'innerToken' => $this->innerToken,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->innerToken = $data['innerToken'];
    }

    public function __toString()
    {
        return $this->innerToken->__toString();
    }

    public function getRoles()
    {
        return $this->innerToken->getRoles();
    }

    public function getCredentials()
    {
        return $this->innerToken->getCredentials();
    }

    public function getRoleNames(): array
    {
        return $this->innerToken->getRoleNames();
    }

    public function getUser()
    {
        return $this->innerToken->getUser();
    }

    public function setUser($user)
    {
        $this->innerToken->setUser($user);
    }

    public function getUsername()
    {
        return $this->innerToken->getUsername();
    }

    public function isAuthenticated()
    {
        return true;
    }

    public function setAuthenticated($isAuthenticated)
    {
        $this->innerToken->setAuthenticated($isAuthenticated);
    }

    public function eraseCredentials()
    {
        $this->innerToken->eraseCredentials();
    }

    public function getAttributes()
    {
        return $this->innerToken->getAttributes();
    }

    public function setAttributes(array $attributes)
    {
        $this->innerToken->setAttributes($attributes);
    }

    public function hasAttribute($name)
    {
        return $this->innerToken->hasAttribute($name);
    }

    public function getAttribute($name)
    {
        return $this->innerToken->getAttribute($name);
    }

    public function setAttribute($name, $value)
    {
        $this->innerToken->setAttribute($name, $value);
    }
}
