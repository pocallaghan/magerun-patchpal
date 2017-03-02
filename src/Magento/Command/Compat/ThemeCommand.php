<?php

namespace TiB\PatchPal\Magento\Command\Compat;

use TiB\PatchPal\Check\Theme;
use TiB\PatchPal\Magento\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ThemeCommand extends AbstractCommand
{
    const MSG_COMPLETE = 'Theme check found %s collision(s).';

    protected function configure()
    {
        $this
            ->setName('patch:compat:theme')
            ->addArgument('file_path', InputArgument::REQUIRED, 'Path to the patch file.')
            ->setDescription('Check install for theme overloads affecting patch.')
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

        $check = new Theme($root);
        $collisions = $check->check($touchedFiles);

        if (count($collisions)) {
            $headings = array('Area', 'Custom Theme', 'Original Theme', 'Type', 'File');
            $t = $this->getHelper('table');
            $t->setHeaders($headings)->renderByFormat($output, $collisions);
        }

        $output->writeln('<info>Done: <comment>'.sprintf(self::MSG_COMPLETE, count($collisions)).'</comment></info>');
    }
}