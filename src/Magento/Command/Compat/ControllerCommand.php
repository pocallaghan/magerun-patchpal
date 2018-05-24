<?php

namespace TiB\PatchPal\Magento\Command\Compat;

use TiB\PatchPal\Check\Controller;
use TiB\PatchPal\Magento\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ControllerCommand extends AbstractCommand
{
    const MSG_COMPLETE = 'Controller check found %s collision(s).';

    protected function configure()
    {
        $this
            ->setName('patch:compat:controller')
            ->addArgument('file_path', InputArgument::OPTIONAL, 'Path to the patch file.')
            ->addOption(
                'installed',
                null,
                InputOption::VALUE_NONE,
                'Check compatibility of installed patches'
            )->setDescription('Check install for controllers loaded before core tha can affect patch.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $root = $this->getApplication()->getMagentoRootFolder();
        $this->check = new Controller($root);
    }
}
