<?php
/**
 * Single model DataProvider for DcGeneral
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package DcGeneral
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */

namespace DcGeneral\Contao;


/**
 * Class SqlHook
 * @package DcGeneral\Contao
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
        $bypassCache = \Config::get('bypassCache');
        \Config::set('bypassCache', true);

        // Only check the active modules (see #4541)
        foreach (\ModuleLoader::getActive() as $module) {
            $dir = 'system/modules/'.$module.'/dca';

            if (!is_dir(TL_ROOT.'/'.$dir)) {
                continue;
            }

            foreach (scan(TL_ROOT.'/'.$dir) as $file) {
                // Ignore non PHP files and files which have been included before
                if (substr($file, -4) != '.php' || in_array($file, $included)) {
                    continue;
                }

                $table = substr($file, 0, -4);

                // Load the data container
                if (!isset($GLOBALS['loadDataContainer'][$table])) {
                    \Controller::loadDataContainer($table);
                }

                if ('General' === $GLOBALS['TL_DCA'][$table]['config']['dataContainer']
                    && self::hasDataProviderClass('DcGeneral\Data\SingleModelDataProvider', $table)
                ) {
                    $definition[$table] = [
                        'TABLE_FIELDS'             => [
                            'name'  => '`name` varchar(128) NOT NULL default \'\'',
                            'value' => '`value` text NULL',
                        ],
                        'TABLE_CREATE_DEFINITIONS' => [
                            'PRIMARY' => 'PRIMARY KEY  (`name`)',
                        ],
                        'TABLE_OPTIONS'            => 'ENGINE=MyISAM DEFAULT CHARSET=utf8',
                    ];
                }

                $included[] = $file;
            }
        }

        // Restore the cache settings
        \Config::set('bypassCache', $bypassCache);

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
