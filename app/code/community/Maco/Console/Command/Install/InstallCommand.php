<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Magento Installation Command
 *
 * Command to install Magento using the console interface
 * Replaces the traditional install.php script
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Command_Install_InstallCommand extends Maco_Console_Command_Install_Abstract
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('install:run')
            ->setDescription('Install Magento using console interface')
            ->setHelp('This command installs Magento using the console interface, replacing the traditional install.php script.');

        // Add all installation options
        $this->addInstallationOptions();
    }

    /**
     * Add all installation options to the command
     */
    protected function addInstallationOptions()
    {
        $options = $this->getAvailableOptions();

        foreach ($options as $optionName => $optionConfig) {
            $this->addOption(
                $optionName,
                null,
                $optionConfig['required'] ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL,
                $optionConfig['description']
            );
        }
    }

    /**
     * Override run method to not initialize Magento for installation
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // Bypass Magento initialization for installation
        // We'll handle initialization manually in the execute method

        // Ensure input is bound and validated
        $this->mergeApplicationDefinition();
        $input->bind($this->getDefinition());

        return $this->execute($input, $output);
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
            // Check if Magento is already installed
            if (Mage::isInstalled()) {
                $output->writeln('<error>Magento is already installed</error>');
                return 1;
            }

            // Convert input to installer arguments
            $args = $this->convertInputToArgs($input);

            // Initialize installer without full Magento environment
            $installer = Mage::getSingleton('install/installer_console');

            // Create a minimal app instance for installation
            $app = Mage::app('install');

            if (!$installer->init($app)) {
                $errors = $installer->getErrors();
                foreach ($errors as $error) {
                    $output->writeln('<error>' . $error . '</error>');
                }
                return 1;
            }

            // Validate arguments
            if (!$installer->setArgs($args)) {
                $errors = $installer->getErrors();
                foreach ($errors as $error) {
                    $output->writeln('<error>' . $error . '</error>');
                }
                return 1;
            }

            // Execute installation
            $output->writeln('<info>Starting Magento installation...</info>');

            if (!$installer->install()) {
                $errors = $installer->getErrors();
                foreach ($errors as $error) {
                    $output->writeln('<error>' . $error . '</error>');
                }
                return 1;
            }

            $output->writeln('<info>Installation completed successfully!</info>');
            $output->writeln('<info>Encryption Key: ' . $installer->getEncryptionKey() . '</info>');

            return 0;

        } catch (Exception $e) {
            $output->writeln('<error>Installation failed: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }
}
