<?php

namespace TiB\PatchPal\Magento\Command\Compat;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CodePoolCommand extends AbstractMagentoCommand
{
    const MSG_COMPLETE = 'Code pool check found %s collision(s).';

    protected function configure()
    {
        $this
            ->setName('patch:compat:codepool')
            ->addArgument('file_path', InputArgument::REQUIRED, 'Path to the patch file.')
            ->setDescription('Check install for code pool overrides affecting patch.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        // @todo - perform checks
        $collisions = array();

        $output->writeln('<info>Done: <comment>'.sprintf(self::MSG_COMPLETE, count($collisions)).'</comment></info>');
    }
}