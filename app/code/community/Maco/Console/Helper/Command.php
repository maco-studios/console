<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

class Maco_Console_Helper_Command extends Mage_Core_Helper_Abstract
{
    /**
     * Get all registered console commands from config
     *
     * @return array
     */
    public function getRegisteredCommands()
    {
        $commands = [];

        try {
            $config = Mage::getConfig()->getNode('global/console/commands');
            if ($config) {
                foreach ($config->children() as $commandNode) {
                    $commands[] = [
                        'class' => (string) $commandNode->class,
                        'name' => (string) $commandNode->name,
                        'description' => (string) $commandNode->description
                    ];
                }
            }
        } catch (Exception $e) {
            Mage::log('Error loading console commands from config: ' . $e->getMessage(), Zend_Log::ERR, 'console.log');
        }

        return $commands;
    }

    /**
     * Create command instance from class name
     *
     * @param string $className
     * @return Maco_Console_Command_Abstract|null
     */
    public function createCommandInstance($className)
    {
        try {
            if (class_exists($className)) {
                $command = new $className();
                if ($command instanceof Maco_Console_Command_Abstract) {
                    return $command;
                }
            }
        } catch (Exception $e) {
            Mage::log("Error creating command instance for {$className}: " . $e->getMessage(), Zend_Log::ERR, 'console.log');
        }

        return null;
    }

    /**
     * Get all available command instances
     *
     * @return array
     */
    public function getCommandInstances()
    {
        $instances = [];
        $registeredCommands = $this->getRegisteredCommands();

        foreach ($registeredCommands as $commandData) {
            $instance = $this->createCommandInstance($commandData['class']);
            if ($instance) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    /**
     * Validate command configuration
     *
     * @param array $commandData
     * @return bool
     */
    public function validateCommandData($commandData)
    {
        return isset($commandData['class']) &&
            isset($commandData['name']) &&
            class_exists($commandData['class']);
    }

    /**
     * Get command by name
     *
     * @param string $commandName
     * @return Maco_Console_Command_Abstract|null
     */
    public function getCommandByName($commandName)
    {
        $registeredCommands = $this->getRegisteredCommands();

        foreach ($registeredCommands as $commandData) {
            if ($commandData['name'] === $commandName) {
                return $this->createCommandInstance($commandData['class']);
            }
        }

        return null;
    }
}
