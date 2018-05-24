<?php

namespace TiB\PatchPal\Check;

class Controller extends AbstractCheck
{
    public function check($changeSet)
    {
        $overloadFiles = $this->gatherModified($changeSet);
        $gatheredOverloads = $this->gatherOverloads();

        $overloads = array();
        foreach ($overloadFiles as $modifiedFile) {
            foreach ($gatheredOverloads as $overload) {
                if ($overload['before'] === $modifiedFile['module']) {
                    list($vendor, $module, $prefix) = explode('_', $overload['prefix']);
                    $controller = implode('/', array($prefix, $modifiedFile['controller']));
                    $overloadPath =  "app/code/community/{$vendor}/{$module}/controllers/{$controller}Controller.php";
                    if (file_exists(\Mage::getBaseDir() .'/'.$overloadPath)) {
                        $overloads[$modifiedFile['filepath']] = array(
                            'core_file' => $modifiedFile['filepath'],
                            'overload_file' => $overloadPath,
                        );
                    }
                }
            }
        }

        return $overloads;
    }

    private function gatherOverloads()
    {
        $modulesNode = \Mage::getConfig()->getNode('frontend/routers');
        $routersInfo = $modulesNode->asArray();
        $gatheredOverloads = array();
        foreach ($routersInfo as $info) {
            if (isset($info['args']['modules'])) {
                foreach ($info['args']['modules'] as $module => $additionalInfo) {
                    if (isset($additionalInfo['@']['before'])) {
                        if (preg_match('~^(Mage|Enterprise)_~', $additionalInfo['@']['before'])) {
                            $gatheredOverloads[] = array(
                                'before' => $additionalInfo['@']['before'],
                                'module' => $module,
                                'prefix' => $additionalInfo[0],
                            );
                        }
                    }
                }
            }
        }

        return $gatheredOverloads;
    }

    private function gatherModified($changeSet)
    {
        $overloadFiles = array();
        foreach ($changeSet as $file) {
            if (!preg_match('~^app/code/([^/]+)/([^/]+)/([^/]+)/controllers/(.+)Controller\.php$~i', $file, $fileInfo)) {
                continue;
            }

            $overloadFiles[] = array(
                'module' => "{$fileInfo[2]}_{$fileInfo[3]}",
                'controller' => $fileInfo[4],
                'filepath' => $file,
            );
        }

        return $overloadFiles;
    }
}
