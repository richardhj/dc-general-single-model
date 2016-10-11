<?php
/**
 * Single model DataProvider for DcGeneral
 *
 * Copyright (c) 2016 Richard Henkenjohann
 *
 * @package DcGeneral
 * @author  Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 */

namespace DcGeneral\Contao\Model;


/**
 * Class AbstractSingleModel
 * @package DcGeneral\Contao\Model
 */
abstract class AbstractSingleModel
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable;


    /**
     * Data
     * @var array
     */
    protected $arrData = [];


    /**
     * Modified keys
     * @var array
     */
    protected $arrModified = [];


    /**
     * @var static
     */
    private static $objInstance;


    /**
     * AbstractSingleModel constructor.
     * Fetch data
     */
    public function __construct()
    {
        $result = \Database::getInstance()->query('SELECT * FROM '.static::$strTable);

        if (null !== $result) {
            while ($result->next()) {
                $this->arrData[$result->field] = $result->value;
            }
        }
    }


    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === static::$objInstance) {
            static::$objInstance = new static();
        }

        return static::$objInstance;
    }


    /**
     * Set an object property
     *
     * @param string $strKey   The property name
     * @param mixed  $varValue The property value
     */
    public function __set($strKey, $varValue)
    {
        if ($this->$strKey === $varValue) {
            return;
        }

        $this->markModified($strKey);
        $this->arrData[$strKey] = $varValue;
    }


    /**
     * Check whether a property is set
     *
     * @param string $strKey The property key
     *
     * @return boolean True if the property is set
     */
    public function __isset($strKey)
    {
        return isset($this->arrData[$strKey]);
    }


    /**
     * Return an object property
     *
     * @param string $strKey The property key
     *
     * @return mixed|null The property value or null
     */
    public function __get($strKey)
    {
        if (isset($this->arrData[$strKey])) {
            return $this->arrData[$strKey];
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
        return static::$strTable;
    }


    /**
     * Mark a field as modified
     *
     * @param string $strKey The field key
     */
    public function markModified($strKey)
    {
        if (!isset($this->arrModified[$strKey])) {
            $this->arrModified[$strKey] = $this->arrData[$strKey];
        }
    }


    /**
     * Save modified keys in database
     * @return self
     */
    public function save()
    {
        $query = 'INSERT INTO '.static::$strTable.' %s';
        $queryUpdate = 'UPDATE %s';

        foreach ($this->arrModified as $field) {

            \Database::getInstance()
                ->prepare
                (
                    $query.
                    ' ON DUPLICATE KEY '.
                    str_replace
                    (
                        'SET ',
                        '',
                        \Database::getInstance()
                            ->prepare($queryUpdate)
                            ->set(['value' => $this->$field])
                            ->query
                    )
                )
                ->set(['field' => $field, 'value' => $this->$field])
                ->execute();
        }

        return $this;
    }
}
