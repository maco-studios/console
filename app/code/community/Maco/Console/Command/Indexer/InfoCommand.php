<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Indexer_InfoCommand extends Maco_Console_Command_Indexer_Abstract
{
    protected function configure()
    {
        $this->setName('indexer:info')
            ->setDescription('Lists all indexers (Magento 2 behavior).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->getProcessesMap() as $code => $process) {
            $output->writeln(sprintf('%-40s %s', $code, $process->getIndexer()->getName()));
        }
        return 0;
    }
}
