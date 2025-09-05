<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Indexer_ResetCommand extends Maco_Console_Command_Indexer_Abstract
{
    protected function configure()
    {
        $this->setName('indexer:reset')
            ->setDescription('Resets indexer state (unlock stuck ones). Empty = all.');
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
            $process->setStatus(Mage_Index_Model_Process::STATUS_PENDING)->save();
            $output->writeln(sprintf('%-40s %s', $code, 'state reset to pending'));
        }
        return 0;
    }
}
