<?php

namespace TiB\PatchPal\Check;

class CodePool extends AbstractCheck
{
    public function check($changeSet)
    {
        // @todo - what other 'type' can be codePool overridden?
        $overloadedFiles = array();
        foreach ($changeSet as $file) {
            if ( ! preg_match('~^app/code/([^/]+)/([^/]+)/([^/]+)/(Model|Helper|Block)/(.+\.php)$~i', $file, $fileInfo)) {
                continue;
            }

            $pools = array('lib', 'core', 'community', 'local');
            $start = array_search($fileInfo[1], $pools);
            $checkPools = array_reverse(array_slice($pools, $start + 1));

            foreach ($checkPools as $pool) {
                $path = implode(
                    DIRECTORY_SEPARATOR,
                    array(
                        'app', 'code', $pool, $fileInfo[2], $fileInfo[3], $fileInfo[4], $fileInfo[5]
                    )
                );
                $fullPath = $this->basePath . DIRECTORY_SEPARATOR . $path;
                if (file_exists($fullPath)) {
                    $overloadedFiles[] = array(
                        'original_file' => $fileInfo[0],
                        'overload_file' => $path,
                    );
                }
            }
        }

        return $overloadedFiles;
    }
}
