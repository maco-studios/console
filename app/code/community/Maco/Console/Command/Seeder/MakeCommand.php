<?php

/**
 * Copyright (c) 2025 Marcos "Marcão" Aurelio
 *
 * @see https://github.com/maco-studios/console
 */

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Make Seeder Command
 *
 * @category   Maco
 * @package    Maco_Console
 */
class Maco_Console_Command_Seeder_MakeCommand extends Maco_Console_Command_Seeder_Abstract
{
    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('make:seeder')
            ->setDescription('Create a new seeder class')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the seeder class'
            );

        parent::configure();
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
            $name = $input->getArgument('name');

            // Add Seeder suffix if not present
            if (!preg_match('/Seeder$/', $name)) {
                $name .= 'Seeder';
            }

            // Remove Seeder suffix to get the base name
            $baseName = preg_replace('/Seeder$/', '', $name);

            $seederPath = BP . '/dev/Database/Seeder/' . $name . '.php';

            // Check if seeder already exists
            if (file_exists($seederPath)) {
                $output->writeln("<error>Seeder {$name} already exists</error>");
                return 1;
            }

            // Create seeder directory if it doesn't exist
            $seederDir = dirname($seederPath);
            if (!is_dir($seederDir)) {
                mkdir($seederDir, 0755, true);
            }

            // Create seeder file
            $content = $this->getSeederTemplate($baseName);
            file_put_contents($seederPath, $content);

            $output->writeln("<info>Seeder created successfully:</info> {$name}");

            return 0;

        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    /**
     * Get seeder template content
     *
     * @param string $name
     * @return string
     */
    protected function getSeederTemplate($name)
    {
        return '<?php

class Dev_Database_Seeder_' . $name . 'Seeder extends Dev_Database_Seeder_SeederAbstract
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Your seeding logic here

        // Example:
        // $this->factory(\'Product\')->count(5)->create();
    }
}';
    }
}
