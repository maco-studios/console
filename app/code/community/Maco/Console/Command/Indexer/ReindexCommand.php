<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Indexer_ReindexCommand extends Maco_Console_Command_Indexer_Abstract
{
    protected function configure()
    {
        $this->setName('indexer:reindex')
            ->setDescription('Reindexes data for given indexers (empty = all).');
        $this->configureIndexersArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $codes = $this->resolveRequestedCodes($input, $output);
        if (empty($codes))
            return 1;

        $map = $this->getProcessesMap();
        foreach ($codes as $code) {
            /** @var Mage_Index_Model_Process $process */
            $process = $map[$code];
            $start = microtime(true);
            $process->reindexEverything();
            $secs = sprintf('%.2f', microtime(true) - $start);
            $output->writeln(sprintf('%s: <info>Reindex complete</info> (%ss)', $code, $secs));
        }
        return 0;
    }
}
