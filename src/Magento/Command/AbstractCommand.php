<?php

namespace TiB\PatchPal\Magento\Command;

use N98\Magento\Command\AbstractMagentoCommand;

abstract class AbstractCommand extends AbstractMagentoCommand
{
    protected function harvestChangeset($filePath)
    {
        $patchData = file_get_contents($filePath);
        $pattern = '~diff --git ([^ ]+) ~ism';
        preg_match_all($pattern, $patchData, $matches);

        return $matches[1];
    }
}
