<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\DependencyInjection\Configuration;

use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Ibexa\Core\MVC\Exception\ParameterNotFoundException;
use eZINI;

/**
 * Configuration resolver for eZ Publish legacy.
 * Will help you get settings from the legacy kernel (old ini files).
 *
 * <code>
 * // From a controller
 * $legacyResolver = $this->container->get( 'ezpublish_legacy.config.resolver' );
 * // Get [DebugSettings].DebugOutput from site.ini
 * $debugOutput = $legacyResolver->getParameter( 'DebugSettings.DebugOutput' );
 * // Get [ImageMagick].ExecutablePath from image.ini
 * $imageMagickPath = $legacyResolver->getParameter( 'ImageMagick.ExecutablePath', 'image' );
 * // Get [DatabaseSettings].Database from site.ini, for ezdemo_site_admin siteaccess
 * $databaseName = $legacyResolver->getParameter( 'DatabaseSettings.Database', 'site', 'ezdemo_site_admin' );
 *
 * // Note that the examples above are also applicable for hasParameter().
 * </code>
 */
class LegacyConfigResolver implements ConfigResolverInterface
{
    /**
     * @var \Closure
     */
    protected $legacyKernelClosure;

    /**
     * @var string
     */
    protected $defaultNamespace;

    public function __construct(\Closure $legacyKernelClosure, $defaultNamespace)
    {
        $this->legacyKernelClosure = $legacyKernelClosure;
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * @return \eZ\Publish\Core\MVC\Legacy\Kernel
     */
    protected function getLegacyKernel()
    {
        $kernelClosure = $this->legacyKernelClosure;

        return $kernelClosure();
    }

    /**
     * Returns value for $paramName, in $namespace.
     *
     * @param string $paramName String containing dot separated INI group name and param name.
     *                          Must follow the following format: <iniGroupName>.<paramName>
     * @param string $namespace The legacy INI file name, without the suffix (i.e. without ".ini").
     * @param string $scope A specific siteaccess to look into. Defaults to the current siteaccess.
     *
     * @throws \Ibexa\Core\MVC\Exception\ParameterNotFoundException
     *
     * @return mixed
     */
    public function getParameter(string $paramName, ?string $namespace = null, ?string $scope = null)
    {
        if (!$this->isValidParameterName($paramName)) {
            throw new ParameterNotFoundException($paramName, "$namespace.ini");
        }

        $namespace = $namespace ?: $this->defaultNamespace;
        $namespace = str_replace('.ini', '', $namespace);
        list($iniGroup, $paramName) = explode('.', $paramName, 2);

        return $this->getLegacyKernel()->runCallback(
            static function () use ($iniGroup, $paramName, $namespace, $scope) {
                if (isset($scope)) {
                    $ini = eZINI::getSiteAccessIni($scope, "$namespace.ini");
                } else {
                    $ini = eZINI::instance("$namespace.ini");
                }

                if (!$ini->hasVariable($iniGroup, $paramName)) {
                    throw new ParameterNotFoundException($paramName, "$namespace.ini");
                }

                return $ini->variable($iniGroup, $paramName);
            },
            false,
            false
        );
    }

    /**
     * Returns values for $groupName, in $namespace.
     *
     * @param string $groupName string containing an INI group name
     * @param string $namespace The legacy INI file name, without the suffix (i.e. without ".ini").
     * @param string $scope A specific siteaccess to look into. Defaults to the current siteaccess.
     *
     * @throws \Ibexa\Core\MVC\Exception\ParameterNotFoundException
     *
     * @return array
     */
    public function getGroup($groupName, $namespace = null, $scope = null)
    {
        $namespace = $namespace ?: $this->defaultNamespace;
        $namespace = str_replace('.ini', '', $namespace);

        return $this->getLegacyKernel()->runCallback(
            static function () use ($groupName, $namespace, $scope) {
                if (isset($scope)) {
                    $ini = eZINI::getSiteAccessIni($scope, "$namespace.ini");
                } else {
                    $ini = eZINI::instance("$namespace.ini");
                }

                if (!$ini->hasGroup($groupName)) {
                    throw new ParameterNotFoundException($groupName, "$namespace.ini");
                }

                return $ini->group($groupName);
            },
            false,
            false
        );
    }

    /**
     * Checks if $paramName exists in $namespace.
     *
     * @param string $paramName
     * @param string $namespace if null, the default namespace should be used
     * @param string $scope the scope you need $paramName value for
     *
     * @return bool
     */
    public function hasParameter(string $paramName, ?string $namespace = null, ?string $scope = null): bool
    {
        // $paramName must have a '.' as it separates INI section and actual parameter name.
        // e.g. DebugSettings.DebugOutput
        if (!$this->isValidParameterName($paramName)) {
            return false;
        }

        $namespace = $namespace ?: $this->defaultNamespace;
        $namespace = str_replace('.ini', '', $namespace);
        list($iniGroup, $paramName) = explode('.', $paramName, 2);

        return $this->getLegacyKernel()->runCallback(
            static function () use ($iniGroup, $paramName, $namespace, $scope) {
                if (isset($scope)) {
                    $ini = eZINI::getSiteAccessIni($scope, "$namespace.ini");
                } else {
                    $ini = eZINI::instance("$namespace.ini");
                }

                return $ini->hasVariable($iniGroup, $paramName);
            },
            false,
            false
        );
    }

    /**
     * Changes the default namespace to look parameter into.
     *
     * @param string $defaultNamespace
     */
    public function setDefaultNamespace(string $defaultNamespace): void
    {
        $this->defaultNamespace = $defaultNamespace;
    }

    /**
     * Returns the current default namespace.
     *
     * @return string
     */
    public function getDefaultNamespace(): string
    {
        return $this->defaultNamespace;
    }

    /**
     * Checks $paramName validity.
     * $paramName must have a '.' as it separates INI section and actual parameter name.
     * e.g. DebugSettings.DebugOutput.
     *
     * @param string $paramName
     *
     * @return int
     */
    private function isValidParameterName($paramName)
    {
        return strpos($paramName, '.') !== false;
    }
}
