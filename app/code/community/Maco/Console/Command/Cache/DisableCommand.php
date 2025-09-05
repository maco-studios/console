<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Cache_DisableCommand extends Maco_Console_Command_Cache_Abstract
{
    protected function configure()
    {
        $this->setName('cache:disable')
            ->setDescription('Disable cache types (Magento 2 behavior: empty = all).');
        $this->configureTypesArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $codes = $this->resolveRequestedTypes($input, $output);
        if (empty($codes)) {
            return 1;
        }

        $this->saveUseCacheFlags($codes, false);
        $output->writeln('Disabled cache types: <comment>' . implode(', ', $codes) . '</comment>');

        return 0;
    }

}
