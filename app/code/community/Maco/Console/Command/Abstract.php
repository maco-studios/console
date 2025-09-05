<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Abstract Magento Console Command
 *
 * Base class for all Magento console commands providing common functionality
 * and Magento-specific helpers
 *
 * @category   Maco
 * @package    Maco_Console
 */
abstract class Maco_Console_Command_Abstract extends Command
{
    /**
     * @var Mage_Core_Model_App
     */
    protected $_app;

    /**
     * @var Mage_Core_Model_Store
     */
    protected $_store;

    /**
     * @var Mage_Core_Model_Website
     */
    protected $_website;

    /**
     * Initialize Magento environment
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function initializeMagento(InputInterface $input, OutputInterface $output)
    {
        try {
            // Initialize Magento application
            $this->_app = Mage::app('admin');
            $this->_store = Mage::app()->getStore();
            $this->_website = Mage::app()->getWebsite();

            // Set timezone if not already set
            if (!ini_get('date.timezone')) {
                date_default_timezone_set('UTC');
            }

        } catch (Exception $e) {
            $output->writeln('<error>Failed to initialize Magento: ' . $e->getMessage() . '</error>');
            throw $e;
        }
    }

    /**
     * Get Magento application instance
     *
     * @return Mage_Core_Model_App
     */
    protected function getApp()
    {
        if (!$this->_app) {
            $this->_app = Mage::app();
        }
        return $this->_app;
    }

    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
    protected function getStore()
    {
        if (!$this->_store) {
            $this->_store = Mage::app()->getStore();
        }
        return $this->_store;
    }

    /**
     * Get current website
     *
     * @return Mage_Core_Model_Website
     */
    protected function getWebsite()
    {
        if (!$this->_website) {
            $this->_website = Mage::app()->getWebsite();
        }
        return $this->_website;
    }

    /**
     * Get Magento helper
     *
     * @param string $helperName
     * @return Mage_Core_Helper_Abstract
     */
    public function getHelper($helperName)
    {
        return Mage::helper($helperName);
    }

    /**
     * Get Magento model
     *
     * @param string $modelName
     * @return Mage_Core_Model_Abstract
     */
    protected function getModel($modelName)
    {
        return Mage::getModel($modelName);
    }

    /**
     * Get Magento singleton
     *
     * @param string $modelName
     * @return Mage_Core_Model_Abstract
     */
    protected function getSingleton($modelName)
    {
        return Mage::getSingleton($modelName);
    }

    /**
     * Log message to Magento log
     *
     * @param string $message
     * @param int $level
     * @return void
     */
    protected function log($message, $level = Zend_Log::INFO)
    {
        Mage::log($message, $level, 'console.log');
    }

    /**
     * Write formatted output with Magento styling
     *
     * @param OutputInterface $output
     * @param string $message
     * @param string $type
     * @return void
     */
    protected function writeFormatted(OutputInterface $output, $message, $type = 'info')
    {
        switch ($type) {
            case 'error':
                $output->writeln('<error>' . $message . '</error>');
                break;
            case 'warning':
                $output->writeln('<comment>' . $message . '</comment>');
                break;
            case 'success':
                $output->writeln('<info>' . $message . '</info>');
                break;
            case 'info':
            default:
                $output->writeln('<info>' . $message . '</info>');
                break;
        }
    }

    /**
     * Check if Magento is in maintenance mode
     *
     * @return bool
     */
    protected function isMaintenanceMode()
    {
        return file_exists(Mage::getBaseDir() . '/maintenance.flag');
    }

    /**
     * Get Magento version
     *
     * @return string
     */
    protected function getMagentoVersion()
    {
        return Mage::getVersion();
    }

    /**
     * Get Magento edition
     *
     * @return string
     */
    protected function getMagentoEdition()
    {
        return Mage::getEdition();
    }

    /**
     * Execute command with Magento initialization
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        // Initialize Magento before running command
        $this->initializeMagento($input, $output);

        return parent::run($input, $output);
    }

}
