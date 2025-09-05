<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Seed Database Command
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Command_Seeder_SeedCommand extends Maco_Console_Command_Seeder_Abstract
{
    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('db:seed')
            ->setDescription('Seed the database with records')
            ->addArgument(
                'class',
                InputArgument::OPTIONAL,
                'The class name of the seeder (without the Seeder suffix)'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Force the operation to run when in production'
            );

        parent::configure();
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
            // Check if Magento is in production mode
            if (!$input->getOption('force') && Mage::getIsDeveloperMode() === false) {
                $output->writeln('<error>Application is in production mode. Use --force to continue.</error>');
                return 1; // Error
            }

            $class = $input->getArgument('class');

            if ($class) {
                $this->runSeeder($class, $output);
            } else {
                $this->runDatabaseSeeder($output);
            }

            return 0; // Success

        } catch (Exception $e) {
            $output->writeln(
                '<error>' . $e->getMessage() . '</error>'
                . '<error>' . $e->getTraceAsString() . '</error>'
            );
            return 1; // Error
        }
    }
}
