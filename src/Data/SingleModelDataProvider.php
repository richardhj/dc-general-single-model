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

namespace Richardhj\DcGeneral\Data;


use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProviderSqlUtils;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultModel;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralException;


/**
 * Class SingleModelDataProvider
 *
 * @package Richardhj\DcGeneral\Data
 */
class SingleModelDataProvider extends DefaultDataProvider
{

    /**
     * Exception throwing convenience method.
     *
     * Convenience method in this data provider that simply throws an Exception stating that the passed method name
     * should not be called on this data provider, as it is only intended to display an edit mask.
     *
     * @param string $method The name of the method being called.
     *
     * @throws DcGeneralException Throws always an exception telling that the method (see param $strMethod) must not be
     *                            called.
     *
     * @return void
     */
    protected function youShouldNotCallMe($method)
    {
        throw new DcGeneralException(
            sprintf(
                'Error, %s not available, as the data provider is intended for edit mode only.',
                $method
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
     * @param ConfigInterface $config The configuration to use.
     *
     * @return ModelInterface
     *
     * @throws DcGeneralException If config object does not contain an Id.
     */
    public function fetch(ConfigInterface $config)
    {
        $query  = sprintf(
            'SELECT %s FROM %s',
            DefaultDataProviderSqlUtils::buildFieldQuery($config, $this->idProperty),
            $this->strSource
        );
        $result = $this->objDatabase->query($query);
        $model  = $this->getEmptyModel();

        if ($result->numRows) {
            while ($result->next()) {
                $model->setProperty($result->field, $result->value);
            }
        }

        return $model;
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $config Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetchAll(ConfigInterface $config)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ConfigInterface $config Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getCount(ConfigInterface $config)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $field Unused.
     *
     * @param mixed  $new   Unused.
     *
     * @param int    $id    Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isUniqueValue($field, $new, $id = null)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $field Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resetFallback($field)
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
     * @param ModelInterface $item      The model to save.
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
    public function save(ModelInterface $item, $recursive = false)
    {
        $query       = 'INSERT INTO ' . $this->strSource . ' %s';
        $queryUpdate = 'UPDATE %s';

        foreach ($item->getPropertiesAsArray() as $field => $value) {
            if ('id' === $field) {
                continue;
            }

            $this->objDatabase
                ->prepare(
                    $query .
                    ' ON DUPLICATE KEY ' .
                    str_replace(
                        'SET ',
                        '',
                        $this->objDatabase
                            ->prepare($queryUpdate)
                            ->set(['value' => $value])
                            ->query
                    )
                )
                ->set(['field' => $field, 'value' => $value])
                ->execute();
        }

        if ($item instanceof DefaultModel) {
            // A pseudo id will prohibit an exception when calling ModelId::create() after saving
            $item->setID($this->strSource . '::1');
        }

        return $item;
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
     * @param string $field The name of the property to check.
     *
     * @return boolean
     */
    public function fieldExists($field)
    {
        return $this->objDatabase
            ->prepare('SELECT * FROM ' . $this->strSource . ' WHERE field=?')
            ->execute($field)
            ->numRows ? true : false;
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $id      Unused.
     *
     * @param mixed $version Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersion($id, $version)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Return null as versioning is not supported in this data provider.
     *
     * @param mixed   $id         Unused.
     *
     * @param boolean $onlyActive Unused.
     *
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersions($id, $onlyActive = false)
    {
        // Sorry, versioning not supported.
        return null;
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ModelInterface $model    Unused.
     *
     * @param string         $username Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveVersion(ModelInterface $model, $username)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $id      Unused.
     *
     * @param mixed $version Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setVersionActive($id, $version)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param mixed $id Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getActiveVersion($id)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param ModelInterface $model1 Unused.
     *
     * @param ModelInterface $model2 Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sameModels($model1, $model2)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }


    /**
     * Unsupported in this data provider, throws an Exception.
     *
     * @param string $sourceSql Unused.
     *
     * @param string $saveSql   Unused.
     *
     * @param string $table     Unused.
     *
     * @return void
     *
     * @throws DcGeneralException Always throws exception.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function insertUndo($sourceSql, $saveSql, $table)
    {
        $this->youShouldNotCallMe(__METHOD__);
    }

}