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
    protected static $table;


    /**
     * Data
     * @var array
     */
    protected $data = [];


    /**
     * Modified keys
     * @var array
     */
    protected $modifiedKeys = [];


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
        $result = \Database::getInstance()->query('SELECT * FROM '.static::$table);

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
     *
     * @return self
     */
    public function setProperty($strKey, $varValue)
    {
        if ($this->getProperty($strKey) === $varValue) {
            return $this;
        }

        $this->data[$strKey] = $varValue;
        return $this->markModified($strKey);
    }


    /**
     * Return an object property
     *
     * @param string $strKey The property key
     *
     * @return mixed|null The property value or null
     */
    public function getProperty($strKey)
    {
        if (isset($this->data[$strKey])) {
            return $this->data[$strKey];
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
     * @param string $strKey The field key
     *
     * @return self
     */
    public function markModified($strKey)
    {
        if (!isset($this->modifiedKeys[$strKey])) {
            $this->modifiedKeys[$strKey] = $this->data[$strKey];
        }

        return $this;
    }


    /**
     * Save modified keys in database
     * @return self
     */
    public function save()
    {
        $query = 'INSERT INTO '.static::$table . ' %s';
        $queryUpdate = 'UPDATE %s';

        foreach ($this->modifiedKeys as $field) {
            \Database::getInstance()
                ->prepare(
                    $query.
                    ' ON DUPLICATE KEY '.
                    str_replace(
                        'SET ',
                        '',
                        \Database::getInstance()
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
