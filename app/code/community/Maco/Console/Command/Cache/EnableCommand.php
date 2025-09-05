<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class Maco_Console_Command_Cache_EnableCommand extends Maco_Console_Command_Cache_Abstract
{
    protected function configure()
    {
        $this->setName('cache:enable')
            ->setDescription('Enable cache types (Magento 2 behavior: empty = all).');
        $this->configureTypesArgument();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $codes = $this->resolveRequestedTypes($input, $output);
        if (empty($codes)) {
            return 1;
        }

        $this->saveUseCacheFlags($codes, true);
        $output->writeln('Enabled cache types: <info>' . implode(', ', $codes) . '</info>');

        return 0;
    }

}
