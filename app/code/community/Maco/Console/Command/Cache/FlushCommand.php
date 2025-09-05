<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Cache_FlushCommand extends Maco_Console_Command_Cache_Abstract
{
    protected function configure()
    {
        $this->setName('cache:flush')
            ->setDescription('Flush cache storage (Magento 2 behavior).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->flushStorage();
        $output->writeln('<info>Flushed cache storage.</info>');
        return 0;
    }


}
