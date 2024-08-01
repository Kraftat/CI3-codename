<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;
use Config\Database; // Correct namespace for database configuration

/**
 * Slug Library
 *
 * Responsible for creating "friendly URLs" in your CodeIgniter application.
 *
 * @package App\Libraries
 */
class Slug
{
    /**
     * The name of the table
     *
     * @var string
     */
    public $table = '';

    /**
     * The primary id field in the table
     *
     * @var string
     */
    public $id = 'id';

    /**
     * The URI Field in the table
     *
     * @var string
     */
    public $field = 'uri';

    /**
     * The title field in the table
     *
     * @var string
     */
    public $title = 'title';

    /**
     * The replacement (Either underscore or dash)
     *
     * @var string
     */
    public $replacement = 'dash';

    /**
     * Database connection
     *
     * @var BaseConnection
     */
    protected $db;

    /**
     * Setup all vars
     *
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->setConfig($config);
        $this->db = Database::connect(); // Correct method to obtain a database connection
        helper(['url', 'text', 'string']);
        log_message('debug', 'Slug Class Initialized');
    }

    /**
     * Manually Set Config
     *
     * Pass an array of config vars to override previous setup
     *
     * @param array $config
     * @return void
     */
    public function setConfig($config = [])
    {
        if (!empty($config)) {
            foreach ($config as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Create a URI string
     *
     * This wraps into the _checkUri method to take a character
     * string and convert into ASCII characters.
     *
     * @param mixed $data (string or array)
     * @param int|null $id
     * @return string|bool
     */
    public function createUri($data = '', $id = null)
    {
        if (empty($data)) {
            return false;
        }

        if (is_array($data)) {
            if (!empty($data[$this->field])) {
                return $this->_checkUri($this->createSlug($data[$this->field]), $id);
            } elseif (!empty($data[$this->title])) {
                return $this->_checkUri($this->createSlug($data[$this->title]), $id);
            }
        } elseif (is_string($data)) {
            return $this->_checkUri($this->createSlug($data), $id);
        }

        return false;
    }

    /**
     * Create Slug
     *
     * Returns a string with all spaces converted to underscores (by default), accented
     * characters converted to non-accented characters, and non word characters removed.
     *
     * @param string $string the string you want to slug
     * @return string
     */
    public function createSlug($string)
    {
        $string = strtolower(url_title(convert_accented_characters($string), $this->replacement));
        return reduce_multiples($string, $this->_getReplacement(), true);
    }

    /**
     * Check URI
     *
     * Checks other items for the same uri and if something else has it
     * change the name to "name-1".
     *
     * @param string $uri
     * @param int|null $id
     * @param int $count
     * @return string
     */
    private function _checkUri($uri, $id = null, $count = 0)
    {
        $newUri = ($count > 0) ? $uri . $this->_getReplacement() . $count : $uri;

        // Setup the query
        $builder = $this->db->table($this->table);
        $builder->select($this->field)->where($this->field, $newUri);

        if ($id) {
            $builder->where("{$this->id} !=", $id);
        }

        if ($builder->countAllResults() > 0) {
            return $this->_checkUri($uri, $id, ++$count);
        } else {
            return $newUri;
        }
    }

    /**
     * Get the replacement type
     *
     * Either a dash or underscore generated off the term.
     *
     * @return string
     */
    private function _getReplacement()
    {
        return ($this->replacement === 'dash') ? '-' : '_';
    }
}
