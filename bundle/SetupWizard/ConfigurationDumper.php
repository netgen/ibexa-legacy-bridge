<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\SetupWizard;

use Ibexa\Core\MVC\Symfony\ConfigDumperInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Filesystem\Filesystem;

class ConfigurationDumper implements ConfigDumperInterface
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $fs;

    /**
     * Path to root dir (kernel.root_dir).
     *
     * @var string
     */
    protected $rootDir;

    /**
     * Path to cache dir (kernel.cache_dir).
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Set of environments to pre-generate config file for.
     * Key is the environment name.
     *
     * @var array
     */
    protected $envs;

    public function __construct(Filesystem $fs, array $envs, $rootDir, $cacheDir)
    {
        $this->fs = $fs;
        $this->rootDir = $rootDir;
        $this->cacheDir = $cacheDir;
        $this->envs = array_fill_keys($envs, true);
    }

    /**
     * Adds an environment to dump a configuration file for.
     *
     * @param string $env
     */
    public function addEnvironment($env)
    {
        $this->envs[$env] = true;
    }

    /**
     * Dumps settings contained in $configArray in ezpublish.yml.
     *
     * @param array $configArray hash of settings
     * @param int $options A binary combination of options. See class OPT_* class constants in {@link \Ibexa\Core\MVC\Symfony\ConfigDumperInterface}
     */
    public function dump(array $configArray, $options = ConfigDumperInterface::OPT_DEFAULT)
    {
        $configPath = "$this->rootDir/config";
        $mainConfigFile = "$configPath/ezpublish.yml";
        if ($this->fs->exists($mainConfigFile) && $options & static::OPT_BACKUP_CONFIG) {
            $this->backupConfigFile($mainConfigFile);
        }

        file_put_contents($mainConfigFile, Yaml::dump($configArray, 7));

        // Now generates environment config files
        foreach (array_keys($this->envs) as $env) {
            $configFile = "$configPath/ezpublish_{$env}.yml";
            // Add the import statement for the root YAML file
            $envConfigArray = [
                'imports' => [['resource' => 'ezpublish.yml']],
            ];

            // File already exists, handle possible options
            if ($this->fs->exists($configFile) && $options & static::OPT_BACKUP_CONFIG) {
                $this->backupConfigFile($configFile);
            }

            file_put_contents($configFile, Yaml::dump($envConfigArray, 7));
        }

        $this->clearCache();
    }

    /**
     * Makes a backup copy of $configFile.
     *
     * @param string $configFile
     */
    protected function backupConfigFile($configFile)
    {
        if ($this->fs->exists($configFile)) {
            $this->fs->copy($configFile, $configFile . '-' . date('Y-m-d_H-i-s'));
        }
    }

    /**
     * Clears the configuration cache.
     */
    protected function clearCache()
    {
        $oldCacheDirName = "{$this->cacheDir}_old";
        $this->fs->rename($this->cacheDir, $oldCacheDirName);
        $this->fs->remove($oldCacheDirName);
    }
}
