<?php

namespace TiB\PatchPal\Check;

class Rewrite extends AbstractCheck
{
    public function check($changeSet)
    {
        $overloadedFiles = array();
        foreach ($changeSet as $file) {
            if (!preg_match('~^app/code/([^/]+)/([^/]+)/([^/]+)/(Model|Helper|Block)/(.+)\.php$~i', $file, $fileInfo)) {
                continue;
            }

            $vendor         = $fileInfo[2];
            $module         = $fileInfo[3];
            $type           = $fileInfo[4];
            $path           = $fileInfo[5];
            $moduleName     = $vendor . '_' . $module;
            $classFragments = explode('/', $path);
            $classShortName = implode('_', array_map('lcfirst', $classFragments));
            $className      = implode('_', $classFragments);
            $coreClassName  = $moduleName . '_' . $type . '_' . $className;
            $shortName = lcfirst($module);
            if ($vendor == 'Enterprise') {
                $shortName = 'enterprise_' . $shortName;
            }

            $className = \Mage::getConfig()->getGroupedClassName(strtolower($type), $shortName . '/' . $classShortName);
            if ($className !== $coreClassName) {
                // @todo - list all classes between classname and coreClassName with reflection?
                $overloadedFiles[$file] = array(
                    'core_class'     => $file,
                    'overload_class' => $this->getFilePath($className),
                );
            }
        }

        return $overloadedFiles;
    }

    protected function getFilePath($class)
    {
        $file = 'app/code/%s/' . str_replace('_', '/', $class) . '.php';
        foreach (['local', 'community'] as $codePool) {
            $poolFile = sprintf($file, $codePool);
            if (file_exists($this->basePath . '/' . $poolFile))  {
                return $poolFile;
            }
        }

        return $file;
    }
}
