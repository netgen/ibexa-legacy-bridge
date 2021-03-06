<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishLegacyBundle\Command;

use Closure;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LegacyEmbedScriptCommand extends Command
{
    /**
     * @var \Closure
     */
    private $legacyKernel;

    /**
     * @var \Closure
     */
    private $cliHandler;

    public function __construct(Closure $legacyKernel, Closure $cliHandler)
    {
        parent::__construct();

        $this->legacyKernel = $legacyKernel;
        $this->cliHandler = $cliHandler;
    }

    protected function configure()
    {
        $this
            ->setName('ezpublish:legacy:script')
            ->addArgument('script', InputArgument::REQUIRED, 'Path to legacy script you want to run. Path must be relative to the legacy root')
            ->addOption('legacy-help', null, InputOption::VALUE_NONE, 'Use this option if you want to display help for the legacy script')
            ->setDescription('Runs an eZ Publish legacy script.')
            ->setHelp(
                <<<EOT
The command <info>%command.name%</info> runs a <info>legacy script</info>.
Passed <info>script</info> argument must be relative to eZ Publish legacy root (e.g. bin/php/eztc.php, extension/myextension/bin/php/myscript.php).
EOT
            );

        // Ignore validation errors to avoid exceptions due to non declared options/arguments (those passed to the legacy script)
        $this->ignoreValidationErrors();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $legacyScript = $input->getArgument('script');

        // Cleanup the input arguments as the legacy kernel expects the script to run as first argument
        foreach ($_SERVER['argv'] as $rawArg) {
            if ($rawArg === $legacyScript) {
                break;
            }

            array_shift($_SERVER['argv']);
            array_shift($GLOBALS['argv']);
        }

        if ($input->getOption('legacy-help')) {
            $_SERVER['argv'][] = '--help';
            $GLOBALS['argv'][] = '--help';
        }

        $siteAccess = $input->getOption('siteaccess');
        if ($siteAccess && !\in_array("--siteaccess=$siteAccess", $_SERVER['argv'])) {
            $_SERVER['argv'][] = "--siteaccess=$siteAccess";
            $GLOBALS['argv'][] = "--siteaccess=$siteAccess";
        }

        $output->writeln("<comment>Running script '$legacyScript' in eZ Publish legacy context</comment>");

        // CLIHandler is contained in $legacyKernel, but we need to inject the script to run separately.
        ($this->cliHandler)()->setEmbeddedScriptPath($legacyScript);
        ($this->legacyKernel)()->run();

        return 0;
    }
}
