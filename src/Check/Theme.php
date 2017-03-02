<?php

namespace TiB\PatchPal\Check;

class Theme extends AbstractCheck
{
    protected $_blackList = array(
        'frontend' => array(
            'base/default',
            'default/blank',
            'default/default',
            'default/iphone',
            'default/modern',
            'enterprise/default',
            'enterprise/iphone',
            'rwd/default',
            'rwd/enterprise',
        ),
        'adminhtml' => array(
            'default/default',
            'default/find',
        ),
        'install' => array(
            'default/default',
            'default/enterprise',

        ),
    );

    public function check($changeSet)
    {
        $collectedChanges = $this->collectChanges($changeSet);
        $collectedThemes  = $this->collectThemes();

        $results = array();
        foreach ($collectedThemes as $area => $themes) {
            foreach ($themes as $theme) {
                foreach ($collectedChanges[$area] as $changes) {
                    $path = \Mage::getBaseDir().'/app/design/'.$area . '/'. $theme . '/'. $changes['file'];
                    if (file_exists($path)) {
                        $bits = explode('/', $changes['file']);
                        $type = array_shift($bits);
                        $file = implode('/', $bits);
                        $results[] = array(
                            'area'         => $area,
                            'custom_theme' => $theme,
                            'core_theme'   => $changes['theme'],
                            'type'         => $type,
                            'file'         => $file,
                        );
                    }
                }
            }
        }

        return $results;
    }

    /**
     * @param array $changeSet
     * @return array
     */
    protected function collectChanges(array $changeSet)
    {
        $collectedChangeSet = array();
        foreach ($changeSet as $file) {
            if (!preg_match('~^app/design/([^/]+)/([^/]+)/([^/]+)/(.+)$~i', $file, $fileInfo)) {
                continue;
            }
            $coreArea = $fileInfo[1];
            $coreTheme = $fileInfo[2] . DIRECTORY_SEPARATOR . $fileInfo[3];
            $coreFile = $fileInfo[4];

            $collectedChangeSet[$coreArea][] = array(
                'area'  => $coreArea,
                'theme' => $coreTheme,
                'file'  => $coreFile,
            );
        }

        return $collectedChangeSet;
    }

    /**
     * @return array
     */
    protected function collectThemes()
    {
        $collectedThemes = array();
        $areaIterator = new \DirectoryIterator($this->basePath . '/app/design/');
        foreach ($areaIterator as $areaInfo) {
            if ( ! $areaInfo->isDir() || $areaInfo->isDot()) {
                continue;
            }

            $packageIterator = new \DirectoryIterator($areaIterator->getPathname());
            foreach ($packageIterator as $packageInfo) {
                if ( ! $packageInfo->isDir() || $packageInfo->isDot()) {
                    continue;
                }

                $themeIterator = new \DirectoryIterator($packageInfo->getPathname());
                foreach ($themeIterator as $themeInfo) {
                    if ( ! $themeInfo->isDir() || $themeInfo->isDot()) {
                        continue;
                    }

                    $themeName = $packageInfo->getFilename() . DIRECTORY_SEPARATOR . $themeInfo->getFilename();
                    if (in_array($themeName, $this->_blackList[$areaInfo->getFilename()])) {
                        continue;
                    }

                    $collectedThemes[$areaInfo->getFilename()][] = $themeName;
                }
            }
        }

        return $collectedThemes;
    }
}
