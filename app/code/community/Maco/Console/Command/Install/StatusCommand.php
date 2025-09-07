<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installation Status Command
 *
 * Command to check the installation status of Magento
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Command_Install_StatusCommand extends Maco_Console_Command_Install_Abstract
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('install:status')
            ->setDescription('Check Magento installation status')
            ->setHelp('This command checks whether Magento is installed and displays version information.');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->showInstallationStatus($output);
            return 0;

        } catch (Exception $e) {
            $output->writeln('<error>Failed to check installation status: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }
}
