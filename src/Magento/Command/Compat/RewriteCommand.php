<?php

namespace TiB\PatchPal\Magento\Command\Compat;

use TiB\PatchPal\Check\Rewrite;
use TiB\PatchPal\Magento\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RewriteCommand extends AbstractCommand
{
    const MSG_COMPLETE = 'Rewrite check found %s collision(s).';

    protected function configure()
    {
        $this
            ->setName('patch:compat:rewrite')
            ->addArgument('file_path', InputArgument::REQUIRED, 'Path to the patch file.')
            ->setDescription('Check install for rewrites affecting same files as patch.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        $root = $this->getApplication()->getMagentoRootFolder();

        $touchedFiles = $this->harvestChangeset($input->getArgument('file_path'));

        $check = new Rewrite($root);
        $collisions = $check->check($touchedFiles);

        if (count($collisions)) {
            $headings = array('Original Path', 'Overload Path');
            $t = $this->getHelper('table');
            $t->setHeaders($headings)->renderByFormat($output, $collisions);
        }

        $output->writeln('<info>Done: <comment>'.sprintf(self::MSG_COMPLETE, count($collisions)).'</comment></info>');    }
}