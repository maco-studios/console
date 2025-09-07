<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract Install Command
 *
 * Base class for installation-related commands providing common functionality
 * for Magento installation operations
 *
 * @category   Maco
 * @package    Maco_Console
 */
abstract class Maco_Console_Command_Install_Abstract extends Maco_Console_Command_Abstract
{
    /**
     * @var Mage_Install_Model_Installer_Console
     */
    protected $_installer;

    /**
     * @var array
     */
    protected $_installOptions = [];

    /**
     * Initialize installer
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function initializeInstaller(InputInterface $input, OutputInterface $output)
    {
        try {
            // Check if Magento is already installed
            if (Mage::isInstalled()) {
                $output->writeln('<error>Magento is already installed</error>');
                throw new Exception('Magento is already installed');
            }

            // Initialize the console installer (our custom one)
            $this->_installer = Mage::getSingleton('install/installer_console');

            if (!$this->_installer->init($this->getApp())) {
                $errors = $this->_installer->getErrors();
                foreach ($errors as $error) {
                    $output->writeln('<error>' . $error . '</error>');
                }
                throw new Exception('Failed to initialize installer');
            }

        } catch (Exception $e) {
            $output->writeln('<error>Failed to initialize installer: ' . $e->getMessage() . '</error>');
            throw $e;
        }
    }

    /**
     * Get installer instance
     *
     * @return Mage_Install_Model_Installer_Console
     */
    protected function getInstaller()
    {
        if (!$this->_installer) {
            $this->_installer = Mage::getSingleton('install/installer_console');
        }
        return $this->_installer;
    }

    /**
     * Validate installation arguments
     *
     * @param array $args
     * @param OutputInterface $output
     * @return bool
     */
    protected function validateInstallArgs($args, OutputInterface $output)
    {
        $installer = $this->getInstaller();

        // Set arguments and validate
        if (!$installer->setArgs($args)) {
            $errors = $installer->getErrors();
            foreach ($errors as $error) {
                $output->writeln('<error>' . $error . '</error>');
            }
            return false;
        }

        return true;
    }

    /**
     * Execute installation
     *
     * @param OutputInterface $output
     * @return bool
     */
    protected function executeInstallation(OutputInterface $output)
    {
        $installer = $this->getInstaller();

        $output->writeln('<info>Starting Magento installation...</info>');

        if (!$installer->install()) {
            $errors = $installer->getErrors();
            foreach ($errors as $error) {
                $output->writeln('<error>' . $error . '</error>');
            }
            return false;
        }

        $output->writeln('<info>Installation completed successfully!</info>');
        $output->writeln('<info>Encryption Key: ' . $installer->getEncryptionKey() . '</info>');

        return true;
    }

    /**
     * Get available installation options
     *
     * @return array
     */
    protected function getAvailableOptions()
    {
        return [
            'license_agreement_accepted' => ['required' => true, 'description' => 'Accept license agreement (yes/no)'],
            'locale' => ['required' => true, 'description' => 'Locale (e.g., en_US)'],
            'timezone' => ['required' => true, 'description' => 'Time zone (e.g., America/Los_Angeles)'],
            'default_currency' => ['required' => true, 'description' => 'Default currency (e.g., USD)'],
            'db_model' => ['required' => false, 'description' => 'Database type (mysql4 by default)'],
            'db_host' => ['required' => true, 'description' => 'Database host'],
            'db_name' => ['required' => true, 'description' => 'Database name'],
            'db_user' => ['required' => true, 'description' => 'Database username'],
            'db_pass' => ['required' => false, 'description' => 'Database password'],
            'db_prefix' => ['required' => false, 'description' => 'Database table prefix'],
            'url' => ['required' => true, 'description' => 'Store URL'],
            'skip_url_validation' => ['required' => false, 'description' => 'Skip URL validation (yes/no)'],
            'use_rewrites' => ['required' => true, 'description' => 'Use web server rewrites (yes/no)'],
            'use_secure' => ['required' => true, 'description' => 'Use secure URLs (yes/no)'],
            'secure_base_url' => ['required' => true, 'description' => 'Secure base URL'],
            'use_secure_admin' => ['required' => true, 'description' => 'Use secure admin (yes/no)'],
            'admin_lastname' => ['required' => true, 'description' => 'Admin last name'],
            'admin_firstname' => ['required' => true, 'description' => 'Admin first name'],
            'admin_email' => ['required' => true, 'description' => 'Admin email'],
            'admin_username' => ['required' => true, 'description' => 'Admin username'],
            'admin_password' => ['required' => true, 'description' => 'Admin password'],
            'encryption_key' => ['required' => false, 'description' => 'Encryption key (auto-generated if not provided)'],
            'session_save' => ['required' => false, 'description' => 'Session save method (files/db)'],
            'admin_frontname' => ['required' => false, 'description' => 'Admin front name (admin by default)'],
            'enable_charts' => ['required' => false, 'description' => 'Enable charts (yes/no)'],
        ];
    }

    /**
     * Convert Symfony Console input to installer arguments
     *
     * @param InputInterface $input
     * @return array
     */
    protected function convertInputToArgs(InputInterface $input)
    {
        $args = ['install.php']; // First argument is script name

        $options = $this->getAvailableOptions();

        foreach ($options as $optionName => $optionConfig) {
            $value = $input->getOption($optionName);
            if ($value !== null) {
                $args[] = '--' . $optionName;
                if ($value !== true) {
                    $args[] = $value;
                }
            }
        }

        return $args;
    }

    /**
     * Check if Magento is installed
     *
     * @return bool
     */
    protected function isMagentoInstalled()
    {
        return Mage::isInstalled();
    }

    /**
     * Get installation status
     *
     * @param OutputInterface $output
     * @return void
     */
    protected function showInstallationStatus(OutputInterface $output)
    {
        if ($this->isMagentoInstalled()) {
            $output->writeln('<info>Magento is installed</info>');
            $output->writeln('<info>Version: ' . Mage::getVersion() . '</info>');
            $output->writeln('<info>Edition: ' . Mage::getEdition() . '</info>');
        } else {
            $output->writeln('<comment>Magento is not installed</comment>');
        }
    }
}
