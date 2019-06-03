<?php

namespace TiB\PatchPal\Magento\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends AbstractMagentoCommand
{
    protected $check;

    protected function getResolvedFiles($patch)
    {
        return $this->getApplication()
            ->getConfig('patchPal','resolvedCompat', $patch);
    }

    protected function harvestChangeset($filePath)
    {
        $patchData = file_get_contents($filePath);
        $pattern = '~diff --git ([^ ]+) ~ism';
        preg_match_all($pattern, $patchData, $matches);

        return $matches[1];
    }

    protected function harvestAppliedChangesets()
    {
        $filename    = implode(DS, [\Mage::getBaseDir(), 'app', 'etc', 'applied.patches.list']);
        $lines       = array_filter(array_map('trim', file($filename)));
        $setPattern  = '~^[^\|]+ \| ([^\|]+) \| [^\|]+ \| [^\|]+ \| [^\|]+ \| .+$~';
        $filePattern = '~^patching file (.*)$~';
        $grouped     = [];
        $set         = '';
        foreach ($lines as $line) {
            if (preg_match($setPattern, $line, $matches)) {
                $set = $matches[1];
            } elseif (preg_match($filePattern, $line, $matches)) {
                $grouped[$matches[1]][] = $set;
            }
        }

        return $grouped;
    }

    protected function validateChangesetSource(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('installed')) {
            return true;
        }

        $file = $input->getArgument('file_path');
        if (empty($file) || !is_file($file)) {
            $output->writeln(
                sprintf('<error>File [%s] not readable.</error>', $file)
            );
            return false;
        }

        return true;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return 1;
        }

        if (!$this->validateChangesetSource($input, $output)) {
            return 1;
        }

        if ($input->getOption('installed')) {
            $headings = array('Original Path', 'Overload Path', 'Patch', 'Resolved?');
            $collisions = $this->checkInstalledPatches();
        } else {
            $fileName = $input->getArgument('file_path');
            $headings = array('Original Path', 'Overload Path');
            $collisions = $this->checkPatchFile($fileName);
        }

        if (count($collisions)) {
            $t = $this->getHelper('table');
            $t->setHeaders($headings)->renderByFormat($output, $collisions);
        }

        $output->writeln('<info>Done: <comment>'.sprintf($this::MSG_COMPLETE, count($collisions)).'</comment></info>');
        $output->writeln('');
    }

    protected function checkPatchFile($fileName)
    {
        $touchedFiles = $this->harvestChangeset($fileName);

        return $this->check->check($touchedFiles);
    }

    protected function checkInstalledPatches()
    {
        $changesets   = $this->harvestAppliedChangesets();
        $touchedFiles = array_unique(array_keys($changesets));
        $collisions   = $this->check->check($touchedFiles);

        $sorted = [];
        foreach ($changesets as $file => $patches) {
            foreach ($patches as $patch) {
                if (!isset($collisions[$file])) {
                    continue;
                }
                $sorted[] = $collisions[$file] + [
                    'patch' => $patch,
                        'resolved' => in_array($file, $this->getResolvedFiles($patch)) ?
                            'Yes' :
                            'No'
                    ];
            }
        }

        return $sorted;
    }
}
