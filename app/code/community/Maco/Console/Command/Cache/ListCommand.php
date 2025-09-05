<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Cache_ListCommand extends Maco_Console_Command_Cache_Abstract
{
    /**
     * Configure command
     */
    protected function configure()
    {
        $this
            ->setName('cache:list')
            ->setDescription('List all available cache types')
            ->setHelp('This command lists all available cache types with their codes and descriptions.');
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $types = array_keys($this->getCacheTypes());

        $output->writeln('<info>Available Cache Types</info>');
        $output->writeln('');

        $this->displayCacheTypesTable($output, $types);

        $output->writeln('');
        $output->writeln(sprintf('<comment>Total: %d cache types available</comment>', count($types)));

        $output->writeln('');
        $output->writeln('<comment>Usage examples:</comment>');
        $output->writeln('  <info>cache:clean config</info>         # Clean config cache');
        $output->writeln('  <info>cache:enable all</info>           # Enable all caches');
        $output->writeln('  <info>cache:disable layout</info>       # Disable layout cache');
        $output->writeln('  <info>cache:status</info>               # Show cache status');

        return 0;
    }
}
