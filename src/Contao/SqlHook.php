<?php

/**
 * This file is part of richardhj/dc-general-single-model.
 *
 * Copyright (c) 2016-2017 Richard Henkenjohann
 *
 * @package   richardhj/contao-textfield-multiple
 * @author    Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright 2016-2017 Richard Henkenjohann
 * @license   https://github.com/richardhj/dc-general-single-model/blob/master/LICENSE LGPL-3.0
 */

namespace Richardhj\DcGeneral\Contao;

use Contao\Config;
use Contao\Controller;
use Contao\ModuleLoader;


/**
 * Class SqlHook
 *
 * @package Richardhj\DcGeneral\Contao
 */
class SqlHook
{

    /**
     * Add the database table definition of "single model DCAs" to the Database\Installer's sql definition
     *
     * @param array $definition
     *
     * @return array
     */
    public function addSqlDefinition(array $definition)
    {
        $included = [];

        // Ignore the internal cache
        $bypassCache = Config::get('bypassCache');
        Config::set('bypassCache', true);

        // Only check the active modules (see #4541)
        foreach (ModuleLoader::getActive() as $module) {
            $dir = 'system/modules/' . $module . '/dca';

            if (!is_dir(TL_ROOT . '/' . $dir)) {
                continue;
            }

            foreach (scan(TL_ROOT . '/' . $dir) as $file) {
                // Ignore non PHP files and files which have been included before
                if ('.php' !== substr($file, -4) || in_array($file, $included)) {
                    continue;
                }

                $table = substr($file, 0, -4);

                // Load the data container
                if (!isset($GLOBALS['loadDataContainer'][$table])) {
                    Controller::loadDataContainer($table);
                }

                if ('General' === $GLOBALS['TL_DCA'][$table]['config']['dataContainer']
                    && self::hasDataProviderClass('DcGeneral\Data\SingleModelDataProvider', $table)
                ) {
                    $definition[$table] = [
                        'TABLE_FIELDS'             => [
                            'field' => '`field` varchar(128) NOT NULL default \'\'',
                            'value' => '`value` text NULL',
                        ],
                        'TABLE_CREATE_DEFINITIONS' => [
                            'PRIMARY' => 'PRIMARY KEY  (`field`)',
                        ],
                        'TABLE_OPTIONS'            => 'ENGINE=MyISAM DEFAULT CHARSET=utf8',
                    ];
                }

                $included[] = $file;
            }
        }

        // Restore the cache settings
        Config::set('bypassCache', $bypassCache);

        return $definition;
    }


    /**
     * @param string $dataProviderClassName The data provider's class name
     * @param string $table                 The dca table name
     *
     * @return bool
     */
    private static function hasDataProviderClass($dataProviderClassName, $table)
    {
        $dcaConfig = $GLOBALS['TL_DCA'][$table]['dca_config']['data_provider'];

        if (!is_array($dcaConfig)) {
            return false;
        }

        foreach ($dcaConfig as $dataProvider) {
            if ($dataProviderClassName === $dataProvider['class']) {
                return true;
            }
        }

        return false;
    }
}
