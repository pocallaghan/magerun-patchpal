<?php

namespace TiB\PatchPal\Magento\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use N98\Util\OperatingSystem;

class CompatCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('patch:compat')
            ->setAliases(array('patch:compat:all'))
            ->addArgument('file_path', InputArgument::REQUIRED, 'File path to patch file.')
            ->setDescription('Run all compatibility check against current install.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $commands = array(
            'codepool',
            'rewrite',
            'theme',
            'controller'
        );

        foreach ($commands as $name) {
            $fullName = 'patch:compat:' . $name;
            $command = $this->getApplication()->find($fullName);

            $arguments = array(
                'command'   => $fullName,
                'file_path' => $input->getArgument('file_path')
            );

            $childInput = new ArrayInput($arguments);
            $returnCode = $command->run($childInput, $output);
        }

        $output->writeln('<info>Done: <comment>All compatibility tests complete.</comment></info>');
    }
}