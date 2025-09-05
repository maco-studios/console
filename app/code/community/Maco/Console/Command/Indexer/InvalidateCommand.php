<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Indexer_InvalidateCommand extends Maco_Console_Command_Indexer_Abstract
{
    protected function configure()
    {
        $this->setName('indexer:invalidate')
            ->setDescription('Marks indexers as invalid (reindex required). Empty = all.');
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
            $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
            $output->writeln(sprintf('%-40s %s', $code, 'invalidated (reindex required)'));
        }
        return 0;
    }
}
