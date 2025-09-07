<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

/**
 * Console Installer
 *
 * Custom installer that uses env.php instead of local.xml
 * for the console installation process
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Model_Installer_Console extends Mage_Install_Model_Installer_Console
{
    /**
     * Install configuration using env.php
     *
     * @param array $configData
     * @return $this
     */
    public function installConfig($configData)
    {
        $configInstaller = new Maco_Console_Model_Installer_Config();
        $configInstaller->setConfigData($configData);
        $configInstaller->install();

        return $this;
    }

    /**
     * Get installer instance
     *
     * @return Mage_Install_Model_Installer
     */
    protected function _getInstaller()
    {
        if (is_null($this->_installer)) {
            $this->_installer = Mage::getModel('install/installer');
            $this->_installer->setDataModel($this->_getDataModel());
        }
        return $this->_installer;
    }

    /**
     * Replace temporary install date in env.php
     *
     * @param string|null $date
     * @return $this
     */
    public function replaceTmpInstallDate($date = null)
    {
        $configInstaller = new Maco_Console_Model_Installer_Config();
        $configInstaller->replaceTmpInstallDate($date);

        return $this;
    }

    /**
     * Replace temporary encryption key in env.php
     *
     * @param string|null $key
     * @return $this
     */
    public function replaceTmpEncryptKey($key = null)
    {
        $configInstaller = new Maco_Console_Model_Installer_Config();
        $configInstaller->replaceTmpEncryptKey($key);

        return $this;
    }

    /**
     * Override installEnryptionKey to use our custom installer
     *
     * @param string $key
     * @return $this
     */
    public function installEnryptionKey($key)
    {
        $this->replaceTmpEncryptKey($key);
        return $this;
    }

    /**
     * Override install method to use our custom config installer
     *
     * @return bool
     */
    public function install()
    {
        try {
            /**
             * Check if already installed
             */
            if (Mage::isInstalled()) {
                $this->addError('ERROR: Magento is already installed');
                return false;
            }

            /**
             * Skip URL validation, if set
             */
            $this->_getDataModel()->setSkipUrlValidation($this->_args['skip_url_validation']);
            $this->_getDataModel()->setSkipBaseUrlValidation($this->_args['skip_url_validation']);

            /**
             * Prepare data
             */
            $this->_prepareData();

            if ($this->hasErrors()) {
                return false;
            }

            $installer = $this->_getInstaller();

            /**
             * Install configuration using our custom installer
             */
            $this->installConfig($this->_getDataModel()->getConfigData());

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Replace temporary install date with actual date
             */
            $this->replaceTmpInstallDate();

            /**
             * Reinitialize configuration (to use new config data)
             */
            $this->_app->cleanCache();
            Mage::getConfig()->reinit();

            /**
             * Install database
             */
            $installer->installDb();

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Apply data updates only for core modules first
             * Skip third-party modules that might have dependencies
             */
            try {
                // Only apply core Magento data updates
                $coreModules = array('Mage_Core', 'Mage_Install', 'Mage_Admin', 'Mage_Customer', 'Mage_Catalog', 'Mage_Sales');
                foreach ($coreModules as $module) {
                    $setup = new Mage_Core_Model_Resource_Setup($module);
                    $setup->applyDataUpdates();
                }
            } catch (Exception $e) {
                // Log the error but don't fail the installation
                Mage::log('Warning: Some data updates failed: ' . $e->getMessage(), Zend_Log::WARN);
            }

            /**
             * Validate entered data for administrator user
             */
            $user = $installer->validateAndPrepareAdministrator($this->_getDataModel()->getAdminData());

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Prepare encryption key and validate it
             */
            $encryptionKey = empty($this->_args['encryption_key'])
                ? md5(Mage::helper('core')->getRandomString(10))
                : $this->_args['encryption_key'];
            $this->_getDataModel()->setEncryptionKey($encryptionKey);
            $installer->validateEncryptionKey($encryptionKey);

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Create primary administrator user
             */
            $installer->createAdministrator($user);

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Save encryption key or create if empty
             */
            $this->installEnryptionKey($encryptionKey);

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Installation finish - skip the original finish method
             * as it tries to read local.xml
             */
            // $installer->finish(); // Skip this as it references local.xml

            if ($this->hasErrors()) {
                return false;
            }

            /**
             * Change directories mode to be writable by apache user
             */
            @chmod('var/cache', 0777);
            @chmod('var/session', 0777);
        } catch (Exception $e) {
            $this->addError('ERROR: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
