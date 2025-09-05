<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Cache_StatusCommand extends Maco_Console_Command_Cache_Abstract
{
    protected function configure()
    {
        $this->setName('cache:status')
            ->setAliases(array('cache:list')) // alias para compatibilidade
            ->setDescription('Show status of each cache type (Magento 2 behavior).');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $map = $this->getCacheTypesMap();

        foreach ($map as $row) {
            $output->writeln(sprintf(
                '%-24s %s',
                $row['code'],
                $row['enabled'] ? '<info>Enabled</info>' : '<comment>Disabled</comment>'
            ));
        }

        return 0;
    }

}
