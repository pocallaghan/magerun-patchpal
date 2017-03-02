<?php

namespace TiB\PatchPal\Magento\Command\Compat;

use TiB\PatchPal\Check\Controller;
use TiB\PatchPal\Magento\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends AbstractCommand
{
    const MSG_COMPLETE = 'Controller check found %s collision(s).';

    protected function configure()
    {
        $this
            ->setName('patch:compat:controller')
            ->addArgument('file_path', InputArgument::REQUIRED, 'Path to the patch file.')
            ->setDescription('Check install for controllers loaded before core tha can affect patch.')
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

        $check = new Controller($root);
        $collisions = $check->check($touchedFiles);

        if (count($collisions)) {
            $headings = array('Original File', 'Overload File');
            $t = $this->getHelper('table');
            $t->setHeaders($headings)->renderByFormat($output, $collisions);
        }

        $output->writeln('<info>Done: <comment>'.sprintf(self::MSG_COMPLETE, count($collisions)).'</comment></info>');
    }
}
