<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Maco_Console_Command_Abstract as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Maco_Console_Command_Indexer_Abstract extends Command
{
    protected function getIndexer()
    {
        return Mage::getSingleton('index/indexer');
    }

    /** @return Mage_Index_Model_Process[] keyed by code */
    protected function getProcessesMap()
    {
        $map = array();
        foreach ($this->getIndexer()->getProcessesCollection() as $process) {
            /** @var Mage_Index_Model_Process $process */
            $map[$process->getIndexerCode()] = $process;
        }
        return $map;
    }

    /** @return string[] */
    protected function allCodes()
    {
        return array_keys($this->getProcessesMap());
    }

    /** If no codes provided => all. Validates against available codes. */
    protected function resolveRequestedCodes(InputInterface $input, OutputInterface $output, $argName = 'indexers')
    {
        $requested = (array) $input->getArgument($argName);
        $requested = array_values(array_filter($requested, 'strlen'));

        if (empty($requested)) {
            return $this->allCodes();
        }

        $requested = array_map('strtolower', $requested);
        $valid = $this->allCodes();
        $unknown = array_diff($requested, $valid);

        if (!empty($unknown)) {
            $output->writeln('<error>Unknown indexers: ' . implode(', ', $unknown) . '</error>');
            $output->writeln('Available indexers: ' . implode(', ', $valid));
            return array();
        }
        return $requested;
    }

    protected function configureIndexersArgument($desc = 'Indexer codes (space-separated). Empty = all.')
    {
        $this->addArgument('indexers', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, $desc);
    }

    protected function m1ModeToM2($m1Mode)
    {
        // M1: real_time|manual  →  M2: realtime|schedule
        return $m1Mode === Mage_Index_Model_Process::MODE_REAL_TIME ? 'realtime' : 'schedule';
    }

    protected function m2ModeToM1($m2Mode)
    {
        $mode = strtolower(trim($m2Mode));
        if (in_array($mode, array('realtime', 'real-time', 'real_time'), true)) {
            return Mage_Index_Model_Process::MODE_REAL_TIME;
        }
        if (in_array($mode, array('schedule', 'scheduled', 'manual'), true)) {
            return Mage_Index_Model_Process::MODE_MANUAL;
        }
        throw new InvalidArgumentException('Invalid mode. Use "realtime" or "schedule".');
    }

    protected function statusToM2Label(Mage_Index_Model_Process $process)
    {
        $status = $process->getStatus();
        if ($status === Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX) {
            return 'Reindex required';
        }
        if ($status === Mage_Index_Model_Process::STATUS_RUNNING) {
            return 'Processing';
        }
        // pending, unknown → treat as Ready
        return 'Ready';
    }
}
