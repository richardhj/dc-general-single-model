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

namespace Richardhj\DcGeneral\Contao\Model;

use Contao\Database;


/**
 * Class AbstractSingleModel
 *
 * @package Richardhj\DcGeneral\Contao\Model
 */
abstract class AbstractSingleModel
{

    /**
     * Table name
     *
     * @var string
     */
    protected static $table;


    /**
     * Data
     *
     * @var array
     */
    protected $data = [];


    /**
     * Modified keys
     *
     * @var array
     */
    protected $modifiedKeys = [];


    /**
     * @var static
     */
    private static $instance;


    /**
     * AbstractSingleModel constructor.
     * Fetch data
     */
    public function __construct()
    {
        $result = Database::getInstance()->query('SELECT * FROM ' . static::$table);

        if (null !== $result) {
            while ($result->next()) {
                $this->data[$result->field] = $result->value;
            }
        }
    }


    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }


    /**
     * Set an object property
     *
     * @param string $key   The property name
     * @param mixed  $value The property value
     *
     * @return self
     */
    public function setProperty($key, $value)
    {
        if ($value === $this->getProperty($key)) {
            return $this;
        }

        $this->data[$key] = $value;
        return $this->markModified($key);
    }


    /**
     * Return an object property
     *
     * @param string $key The property key
     *
     * @return mixed|null The property value or null
     */
    public function getProperty($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return null;
    }


    /**
     * Return the name of the related table
     *
     * @return string The table name
     */
    public static function getTable()
    {
        return static::$table;
    }


    /**
     * Mark a field as modified
     *
     * @param string $key The field key
     *
     * @return self
     */
    public function markModified($key)
    {
        if (!isset($this->modifiedKeys[$key])) {
            $this->modifiedKeys[$key] = $this->data[$key];
        }

        return $this;
    }


    /**
     * Save modified keys in database
     *
     * @return self
     */
    public function save()
    {
        $query       = 'INSERT INTO ' . static::$table . ' %s';
        $queryUpdate = 'UPDATE %s';

        foreach ($this->modifiedKeys as $field) {
            Database::getInstance()
                ->prepare(
                    $query .
                    ' ON DUPLICATE KEY ' .
                    str_replace(
                        'SET ',
                        '',
                        Database::getInstance()
                            ->prepare($queryUpdate)
                            ->set(['value' => $this->getProperty($field)])
                            ->query
                    )
                )
                ->set(['field' => $field, 'value' => $this->getProperty($field)])
                ->execute();
        }

        return $this;
    }
}
