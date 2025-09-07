<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Installation Options Command
 *
 * Command to display available installation options (locales, currencies, timezones)
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Command_Install_OptionsCommand extends Maco_Console_Command_Install_Abstract
{
    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('install:options')
            ->setDescription('Display available installation options (locales, currencies, timezones)')
            ->setHelp('This command displays all available options for Magento installation including supported locales, currencies, and timezones.');
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Initialize Magento app (but not installer since it's already installed)
            $this->initializeMagento($input, $output);

            $output->writeln('<info>Available Installation Options:</info>');
            $output->writeln('');

            // Get and display options
            $options = [
                'locale' => $this->getApp()->getLocale()->getOptionLocales(),
                'currency' => $this->getApp()->getLocale()->getOptionCurrencies(),
                'timezone' => $this->getApp()->getLocale()->getOptionTimezones(),
            ];

            foreach ($options as $type => $optionList) {
                $output->writeln('<comment>' . ucfirst($type) . ' Options:</comment>');

                if (is_array($optionList)) {
                    foreach ($optionList as $option) {
                        if (is_array($option) && isset($option['value']) && isset($option['label'])) {
                            $output->writeln('  ' . $option['value'] . ' - ' . $option['label']);
                        } else {
                            $output->writeln('  ' . $option);
                        }
                    }
                }
                $output->writeln('');
            }

            return 0;

        } catch (Exception $e) {
            $output->writeln('<error>Failed to get installation options: ' . $e->getMessage() . '</error>');
            return 1;
        }
    }
}
