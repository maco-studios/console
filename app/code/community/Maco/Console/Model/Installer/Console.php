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

                // Ensure admin role exists after data updates
                $this->ensureAdminRoleExists();

            } catch (Exception $e) {
                // Log the error but don't fail the installation
                Mage::log('Warning: Some data updates failed: ' . $e->getMessage(), Zend_Log::WARN);

                // Try to ensure admin role exists even if data updates failed
                try {
                    $this->ensureAdminRoleExists();
                } catch (Exception $roleException) {
                    Mage::log('Warning: Could not ensure admin role exists: ' . $roleException->getMessage(), Zend_Log::WARN);
                }
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
                ? md5(uniqid(rand(), true))
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
             * Ensure the admin user has the correct role assigned
             */
            $this->ensureAdminUserRole($user);

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

    /**
     * Ensure that the admin role exists with proper permissions
     * This is a fallback in case data updates fail
     */
    protected function ensureAdminRoleExists()
    {
        // Check if admin role already exists
        $adminRole = Mage::getModel('admin/role')->getCollection()
            ->addFieldToFilter('role_name', 'Administrators')
            ->addFieldToFilter('role_type', 'G')
            ->getFirstItem();

        if (!$adminRole->getId()) {
            // Create the admin role if it doesn't exist
            $adminRole = Mage::getModel('admin/role')->setData([
                'parent_id' => 0,
                'tree_level' => 1,
                'sort_order' => 1,
                'role_type' => 'G',
                'user_id' => 0,
                'role_name' => 'Administrators',
            ]);
            $adminRole->save();

            // Create the admin rule with full permissions
            $adminRule = Mage::getModel('admin/rules')->setData([
                'role_id' => $adminRole->getId(),
                'resource_id' => 'all',
                'privileges' => null,
                'assert_id' => 0,
                'role_type' => 'G',
                'permission' => 'allow',
            ]);
            $adminRule->save();

            Mage::log('Created admin role with ID: ' . $adminRole->getId(), Zend_Log::INFO);
        } else {
            // Ensure the role has proper permissions
            $adminRule = Mage::getModel('admin/rules')->getCollection()
                ->addFieldToFilter('role_id', $adminRole->getId())
                ->addFieldToFilter('resource_id', 'all')
                ->getFirstItem();

            if (!$adminRule->getId()) {
                $adminRule = Mage::getModel('admin/rules')->setData([
                    'role_id' => $adminRole->getId(),
                    'resource_id' => 'all',
                    'privileges' => null,
                    'assert_id' => 0,
                    'role_type' => 'G',
                    'permission' => 'allow',
                ]);
                $adminRule->save();
                Mage::log('Created admin rule for existing role ID: ' . $adminRole->getId(), Zend_Log::INFO);
            }
        }
    }

    /**
     * Ensure the admin user has the correct role assigned
     *
     * @param Mage_Admin_Model_User $user
     */
    protected function ensureAdminUserRole($user)
    {
        try {
            // Get the admin role
            $adminRole = Mage::getModel('admin/role')->getCollection()
                ->addFieldToFilter('role_name', 'Administrators')
                ->addFieldToFilter('role_type', 'G')
                ->getFirstItem();

            if ($adminRole->getId()) {
                // Assign the admin role to the user
                $user->setRoleIds([$adminRole->getId()])->saveRelations();
                Mage::log('Assigned admin role ID ' . $adminRole->getId() . ' to user: ' . $user->getUsername(), Zend_Log::INFO);
            } else {
                Mage::log('Warning: Could not find admin role to assign to user: ' . $user->getUsername(), Zend_Log::WARN);
            }
        } catch (Exception $e) {
            Mage::log('Error assigning admin role to user: ' . $e->getMessage(), Zend_Log::ERR);
        }
    }
}
