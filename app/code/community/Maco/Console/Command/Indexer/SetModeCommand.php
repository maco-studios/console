<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Indexer_SetModeCommand extends Maco_Console_Command_Indexer_Abstract
{
    protected function configure()
    {
        $this->setName('indexer:set-mode')
            ->setDescription('Sets mode: realtime|schedule (empty indexers = all).')
            ->addArgument('mode', InputArgument::REQUIRED, 'realtime|schedule');
        $this->configureIndexersArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $m1Mode = $this->m2ModeToM1($input->getArgument('mode'));
        } catch (InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        $codes = $this->resolveRequestedCodes($input, $output);
        if (empty($codes))
            return 1;

        $map = $this->getProcessesMap();
        foreach ($codes as $code) {
            /** @var Mage_Index_Model_Process $process */
            $process = $map[$code];
            $process->setMode($m1Mode)->save();
            $output->writeln(sprintf('%-40s %s', $code, $this->m1ModeToM2($m1Mode)));
        }
        return 0;
    }
}
