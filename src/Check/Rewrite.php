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
                $overloadedFiles[] = array(
                    'core_class'     => $coreClassName,
                    'overload_class' => $className,
                );
            }
        }

        return $overloadedFiles;
    }
}
