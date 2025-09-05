<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// Define Magento base path if not already defined
if (!defined('BP')) {
    define('BP', dirname(dirname(dirname(dirname(dirname(__DIR__))))));
}

// Register autoloader for Dev_Database classes
Mage::helper('maco_console/autoloader')->registerDatabaseAutoloader();

/**
 * Abstract Seeder Command Class
 *
 * @category   Maco
 * @package    Maco_Console
 */
abstract class Maco_Console_Command_Seeder_Abstract extends Maco_Console_Command_Abstract
{
    /**
     * Get all available seeders
     *
     * @return array
     */
    protected function getSeeders()
    {
        return Mage::helper('maco_console/autoloader')->getAvailableSeeders();
    }

    /**
     * Get all available factories
     *
     * @return array
     */
    protected function getFactories()
    {
        return Mage::helper('maco_console/autoloader')->getAvailableFactories();
    }

    /**
     * Run a specific seeder
     *
     * @param string $seeder
     * @param OutputInterface $output
     * @return void
     */
    protected function runSeeder($seeder, OutputInterface $output)
    {
        // Handle both formats: "ProductSeeder" and "Product"
        $seederName = $seeder;
        if (!strpos($seeder, 'Seeder')) {
            $seederName = $seeder . 'Seeder';
        }

        $seederClass = 'Dev_Database_Seeder_' . $seederName;

        if (!class_exists($seederClass)) {
            throw new RuntimeException("Seeder class {$seederClass} not found. Make sure the seeder file exists in dev/Database/Seeder/");
        }

        $output->writeln("<info>Seeding:</info> {$seeder}");

        try {
            $seederInstance = new $seederClass();
            $seederInstance->run();
            $output->writeln("<info>Seeded:</info> {$seeder}");
        } catch (Exception $e) {
            $output->writeln("<error>Failed:</error> {$seeder} - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run the database seeder
     *
     * @param OutputInterface $output
     * @return void
     */
    protected function runDatabaseSeeder(OutputInterface $output)
    {
        $output->writeln('<info>Running DatabaseSeeder...</info>');

        try {
            $seederClass = 'Dev_Database_Seeder_DatabaseSeeder';
            if (!class_exists($seederClass)) {
                throw new RuntimeException("DatabaseSeeder class not found. Make sure the file exists in dev/Database/Seeder/DatabaseSeeder.php");
            }
            $seeder = new $seederClass();
            $seeder->run();
            $output->writeln('<info>Database seeding completed successfully.</info>');
        } catch (Exception $e) {
            $output->writeln('<error>Database seeding failed:</error> ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get a factory instance
     *
     * @param string $factory Factory name (without "Factory" suffix)
     * @return Dev_Database_Factory_FactoryAbstract
     */
    protected function getFactory($factory)
    {
        $factoryClass = 'Dev_Database_Factory_' . $factory . 'Factory';

        if (!class_exists($factoryClass)) {
            throw new RuntimeException("Factory class {$factoryClass} not found. Make sure the factory file exists in dev/Database/Factory/");
        }

        return new $factoryClass();
    }

    /**
     * Check if a seeder exists
     *
     * @param string $seeder
     * @return bool
     */
    protected function seederExists($seeder)
    {
        $seederName = $seeder;
        if (!strpos($seeder, 'Seeder')) {
            $seederName = $seeder . 'Seeder';
        }

        $seederClass = 'Dev_Database_Seeder_' . $seederName;
        return class_exists($seederClass);
    }

    /**
     * Check if a factory exists
     *
     * @param string $factory
     * @return bool
     */
    protected function factoryExists($factory)
    {
        $factoryClass = 'Dev_Database_Factory_' . $factory . 'Factory';
        return class_exists($factoryClass);
    }
}
