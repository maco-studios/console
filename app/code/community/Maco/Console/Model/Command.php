<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

class Maco_Console_Model_Command extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('maco_console/command');
    }

    /**
     * Get command configuration from config.xml
     *
     * @param string $commandName
     * @return array|null
     */
    public function getCommandConfig($commandName)
    {
        try {
            $config = Mage::getConfig()->getNode('global/console/commands');
            if ($config) {
                foreach ($config->children() as $commandNode) {
                    if ((string) $commandNode->name === $commandName) {
                        return [
                            'class' => (string) $commandNode->class,
                            'name' => (string) $commandNode->name,
                            'description' => (string) $commandNode->description
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            Mage::log('Error getting command config for ' . $commandName . ': ' . $e->getMessage(), Zend_Log::ERR, 'console.log');
        }

        return null;
    }

    /**
     * Get all available command names
     *
     * @return array
     */
    public function getAllCommandNames()
    {
        $commandNames = [];

        try {
            $config = Mage::getConfig()->getNode('global/console/commands');
            if ($config) {
                foreach ($config->children() as $commandNode) {
                    $commandNames[] = (string) $commandNode->name;
                }
            }
        } catch (Exception $e) {
            Mage::log('Error getting all command names: ' . $e->getMessage(), Zend_Log::ERR, 'console.log');
        }

        return $commandNames;
    }

    /**
     * Validate command exists
     *
     * @param string $commandName
     * @return bool
     */
    public function commandExists($commandName)
    {
        return in_array($commandName, $this->getAllCommandNames());
    }
}
