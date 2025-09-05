<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * List Seeders Command
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Command_Seeder_ListCommand extends Maco_Console_Command_Seeder_Abstract
{
    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('db:seed:list')
            ->setDescription('List all available seeders');

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
            $seeders = $this->getSeeders();

            if (empty($seeders)) {
                $output->writeln('<info>No seeders found</info>');
                return 0;
            }

            $table = new Table($output);
            $table->setHeaders(['Seeder']);

            foreach ($seeders as $seeder) {
                $table->addRow([$seeder]);
            }

            $output->writeln('<info>Available seeders:</info>');
            $table->render();

            return 0;

        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
    }
}
