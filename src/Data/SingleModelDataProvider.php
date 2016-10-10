<?php
/**
 * Single model DataProvider for DcGeneral
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package DcGeneral
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */

namespace DcGeneral\Data;


use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralException;


/**
 * Class SingleModelDataProvider
 * @package DcGeneral\Data
 */
class SingleModelDataProvider extends DefaultDataProvider
{

    /**
     * {@inheritDoc}
     */
    public function setBaseConfig(array $config)
    {
        // Create database table if not yet
        $table = $config['source'];
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$table}` (
  `name` varchar(128) NOT NULL default '',
  `value` text NULL,
  PRIMARY KEY  (`name`),
  UNIQUE (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
SQL;

        \Database::getInstance()->query($sql);

        parent::setBaseConfig($config);
    }


    /**
     * Exception throwing convenience method.
     *
     * Convenience method in this data provider that simply throws an Exception stating that the passed method name
     * should not be called on this data provider, as it is only intended to display an edit mask.
     *
     * @param string $strMethod The name of the method being called.
     *
     * @throws DcGeneralException Throws always an exception telling that the method (see param $strMethod) must not be
     *                            called.
     *
     * @return void
     */
    protected function youShouldNotCallMe($strMethod)
    {
        throw new DcGeneralException(
            sprintf(
                'Error, %s not available, as the data provider is intended for edit mode only.',
                $strMethod
            ),
            1
        );
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $item Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($item)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Fetch a single record by id.
     *
     * This data provider only supports retrieving by id so use $objConfig->setId() to populate the config with an Id.
     *
     * @param ConfigInterface $objConfig The configuration to use.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralException If config object does not contain an Id.
     */
    public function fetch(ConfigInterface $objConfig)
    {
        $query = sprintf(
            'SELECT %s FROM %s',
            $this->buildFieldQuery($objConfig),
            $this->strSource
        );

        $result = $this->objDatabase->query($query);

        $model = $this->getEmptyModel();

        if ($result->numRows) {
            while ($result->next()) {
                $model->setProperty($result->name, $result->value);
            }
        }

        return $model;
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $objConfig Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchAll(ConfigInterface $objConfig)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $objConfig Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCount(ConfigInterface $objConfig)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $strField Unused.
     *
     * @param mixed  $varNew   Unused.
     *
     * @param int    $intId    Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isUniqueValue($strField, $varNew, $intId = null)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $strField Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resetFallback($strField)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Save a model to the database.
     *
     * In general, this method fetches the solely property "rows" from the model and updates the local table against
     * these contents.
     *
     * The parent id (id of the model) will get checked and reflected also for new items.
     *
     * When rows with duplicate ids are encountered (like from MCW for example), the dupes are inserted as new rows.
     *
     * @param ModelInterface $objItem   The model to save.
     *
     * @param bool           $recursive Ignored as not relevant in this data provider.
     *
     * @return ModelInterface The passed Model.
     *
     * @throws DcGeneralException When the passed model does not contain a property named "rows", an Exception is
     *                            thrown.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save(ModelInterface $objItem, $recursive = false)
    {
        $query = 'INSERT INTO '.$this->strSource.' %s';
        $queryUpdate = 'UPDATE %s';

        foreach ($objItem->getPropertiesAsArray() as $name => $value) {
            if ('id' === $name) {
                continue;
            }

            $this->objDatabase
                ->prepare
                (
                    $query.
                    ' ON DUPLICATE KEY '.
                    str_replace
                    (
                        'SET ',
                        '',
                        $this->objDatabase
                            ->prepare($queryUpdate)
                            ->set(['value' => $value])
                            ->query
                    )
                )
                ->set(['name' => $name, 'value' => $value])
                ->execute();
        }

        if ($objItem instanceof DefaultModel) {
            // A pseudo id will prohibit an exception when calling ModelId::create() after saving
            $objItem->setID($this->strSource.'::1');
        }

        return $objItem;
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param CollectionInterface $objItems Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveEach(CollectionInterface $objItems)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Check if the property exists in the table.
     *
     * This data provider only returns true for the tstamp property.
     *
     * @param string $strField The name of the property to check.
     *
     * @return boolean
     */
    public function fieldExists($strField)
    {
        return $this->objDatabase->prepare('SELECT * FROM '.$this->strSource.' WHERE name=?')->execute(
            $strField
        )->numRows ? true : false;
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $mixID      Unused.
     *
     * @param mixed $mixVersion Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersion($mixID, $mixVersion)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Return null as versioning is not supported in this data provider.
     *
     * @param mixed   $mixID         Unused.
     *
     * @param boolean $blnOnlyActive Unused.
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersions($mixID, $blnOnlyActive = false)
    {
        // Sorry, versioning not supported.
        return null;
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ModelInterface $objModel    Unused.
     *
     * @param string         $strUsername Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveVersion(ModelInterface $objModel, $strUsername)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $mixID      Unused.
     *
     * @param mixed $mixVersion Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setVersionActive($mixID, $mixVersion)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $mixID Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getActiveVersion($mixID)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ModelInterface $objModel1 Unused.
     *
     * @param ModelInterface $objModel2 Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sameModels($objModel1, $objModel2)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $strSourceSQL Unused.
     *
     * @param string $strSaveSQL   Unused.
     *
     * @param string $strTable     Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function insertUndo($strSourceSQL, $strSaveSQL, $strTable)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

}