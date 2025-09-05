<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Indexer_StatusCommand extends Maco_Console_Command_Indexer_Abstract
{
    protected function configure()
    {
        $this->setName('indexer:status')
            ->setDescription('Shows status of indexers (Ready/Reindex required/Processing).');
        $this->configureIndexersArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $codes = $this->resolveRequestedCodes($input, $output);
        if (empty($codes))
            return 1;

        $map = $this->getProcessesMap();
        foreach ($codes as $code) {
            $process = $map[$code];
            $output->writeln(sprintf('%-40s %s', $code, $this->statusToM2Label($process)));
        }
        return 0;
    }
}
