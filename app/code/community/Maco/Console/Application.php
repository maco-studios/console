<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

class Maco_Console_Application extends \Symfony\Component\Console\Application
{
    /**
     * @var Maco_Console_Helper_Command
     */
    protected $_commandHelper;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct('OpenMage Console', Mage::getVersion());

        $this->_commandHelper = Mage::helper('maco_console/command');
        $this->addCommands($this->getRegisteredCommands());
    }

    /**
     * Get registered commands from config.xml
     *
     * @return array
     */
    protected function getRegisteredCommands()
    {
        $commands = parent::getDefaultCommands();

        try {
            $commandInstances = $this->_commandHelper->getCommandInstances();
            $commands = array_merge($commands, $commandInstances);
        } catch (Exception $e) {
            Mage::log('Error loading registered commands: ' . $e->getMessage(), Zend_Log::ERR, 'console.log');

            // Fallback to default commands if config loading fails
            $commands = array_merge($commands, $this->getFallbackCommands());
        }

        return $commands;
    }

    /**
     * Get fallback commands when config loading fails
     *
     * @return array
     */
    protected function getFallbackCommands()
    {
        $commands = [];

        try {
            // Add cache commands
            $commands[] = new Maco_Console_Command_Cache_ListCommand();
            $commands[] = new Maco_Console_Command_Cache_StatusCommand();
            $commands[] = new Maco_Console_Command_Cache_CleanCommand();
            $commands[] = new Maco_Console_Command_Cache_EnableCommand();
            $commands[] = new Maco_Console_Command_Cache_DisableCommand();

            // Add seeder commands
            $commands[] = new Maco_Console_Command_Seeder_SeedCommand();
            $commands[] = new Maco_Console_Command_Seeder_MakeCommand();
            $commands[] = new Maco_Console_Command_Seeder_ListCommand();

            // Add install commands
            $commands[] = new Maco_Console_Command_Install_InstallCommand();
            $commands[] = new Maco_Console_Command_Install_StatusCommand();
            $commands[] = new Maco_Console_Command_Install_OptionsCommand();
        } catch (Exception $e) {
            Mage::log('Error loading fallback commands: ' . $e->getMessage(), Zend_Log::ERR, 'console.log');
        }

        return $commands;
    }

    /**
     * Get command helper instance
     *
     * @return Maco_Console_Helper_Command
     */
    public function getCommandHelper()
    {
        return $this->_commandHelper;
    }
}
