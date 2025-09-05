<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

class Maco_Console_Helper_Autoloader extends Mage_Core_Helper_Abstract
{
    /**
     * @var bool
     */
    protected static $_databaseAutoloaderRegistered = false;

    /**
     * Register autoloader for Dev_Database classes
     *
     * @return void
     */
    public function registerDatabaseAutoloader()
    {
        if (self::$_databaseAutoloaderRegistered) {
            return;
        }

        spl_autoload_register(function ($className) {
            // Only handle Dev_Database classes
            if (strpos($className, 'Dev_Database_') !== 0) {
                return false;
            }

            // Convert class name to file path
            $classPath = str_replace('Dev_Database_', '', $className);
            $classPath = str_replace('_', '/', $classPath);

            // Determine the file path
            $filePath = BP . '/dev/Database/' . $classPath . '.php';

            // Check if file exists and include it
            if (file_exists($filePath)) {
                require_once $filePath;
                return true;
            }

            return false;
        });

        self::$_databaseAutoloaderRegistered = true;
    }

    /**
     * Check if a Dev_Database class exists
     *
     * @param string $className
     * @return bool
     */
    public function databaseClassExists($className)
    {
        if (strpos($className, 'Dev_Database_') !== 0) {
            return false;
        }

        // Convert class name to file path
        $classPath = str_replace('Dev_Database_', '', $className);
        $classPath = str_replace('_', '/', $classPath);

        // Determine the file path
        $filePath = BP . '/dev/Database/' . $classPath . '.php';

        return file_exists($filePath);
    }

    /**
     * Get all available seeder classes
     *
     * @return array
     */
    public function getAvailableSeeders()
    {
        $seeders = [];
        $seederDir = BP . '/dev/Database/Seeder/';

        if (!is_dir($seederDir)) {
            return $seeders;
        }

        $files = scandir($seederDir);
        foreach ($files as $file) {
            if (preg_match('/^([A-Za-z0-9]+)Seeder\.php$/', $file, $matches)) {
                if ($matches[1] !== 'Database' && $matches[1] !== 'Abstract') {
                    $seeders[] = $matches[1];
                }
            }
        }

        return $seeders;
    }

    /**
     * Get all available factory classes
     *
     * @return array
     */
    public function getAvailableFactories()
    {
        $factories = [];
        $factoryDir = BP . '/dev/Database/Factory/';

        if (!is_dir($factoryDir)) {
            return $factories;
        }

        $files = scandir($factoryDir);
        foreach ($files as $file) {
            if (preg_match('/^([A-Za-z0-9]+)Factory\.php$/', $file, $matches)) {
                if ($matches[1] !== 'Abstract' && $matches[1] !== 'Batch') {
                    $factories[] = $matches[1];
                }
            }
        }

        return $factories;
    }
}
