<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

abstract class Maco_Console_Command_Cache_Abstract extends Maco_Console_Command_Abstract
{
    protected function getCacheTypesMap()
    {
        // id, tags, description
        $types = Mage::app()->getCacheInstance()->getTypes();
        $enabled = Mage::app()->useCache(); // [code => 1|0]

        $map = array();
        foreach ($types as $type) {
            $code = $type->getId();
            $map[$code] = array(
                'code' => $code,
                'description' => (string) $type->getDescription(),
                'enabled' => isset($enabled[$code]) ? (bool) $enabled[$code] : false,
            );
        }
        return $map;
    }

    protected function allTypeCodes()
    {
        return array_keys($this->getCacheTypesMap());
    }

    /** @return string[] validated type codes (lowercased). If empty input => all. */
    protected function resolveRequestedTypes(InputInterface $input, OutputInterface $output)
    {
        $requested = (array) $input->getArgument('types');
        $requested = array_values(array_filter($requested, 'strlen'));

        if (empty($requested)) {
            return $this->allTypeCodes();
        }

        $requested = array_map('strtolower', $requested);
        $valid = $this->allTypeCodes();
        $unknown = array_diff($requested, $valid);

        if (!empty($unknown)) {
            $output->writeln('<error>Unknown cache types: ' . implode(', ', $unknown) . '</error>');
            $output->writeln('Available types: ' . implode(', ', $valid));
            return array();
        }

        return $requested;
    }

    /** Enable/disable like Magento 2: no args => all */
    protected function saveUseCacheFlags(array $codes, $enabled)
    {
        $current = Mage::app()->useCache();
        foreach ($codes as $code) {
            $current[$code] = $enabled ? 1 : 0;
        }
        Mage::app()->saveUseCache($current);
    }

    protected function cleanTypes(array $codes)
    {
        $cache = Mage::app()->getCacheInstance();
        foreach ($codes as $code) {
            $cache->cleanType($code);
        }
    }

    protected function flushStorage()
    {
        Mage::app()->getCacheInstance()->flush();
    }

    protected function configureTypesArgument()
    {
        $this->addArgument(
            'types',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'Cache types (space-separated). Empty = all.'
        );
    }
}
