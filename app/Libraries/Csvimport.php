<?php

namespace App\Libraries;

/**
 * CodeIgniter CSV Import Class
 *
 * This library will help import a CSV file into
 * an associative array.
 *
 * This library treats the first row of a CSV file
 * as a column header row.
 *
 * @package         CodeIgniter
 * @subpackage      Libraries
 * @category        Libraries
 * @author          Brad Stinson
 */
class Csvimport
{
    private $handle = '';
    private string $filepath = '';
    private array $column_headers = [];
    private int $initial_line = 0;
    private string $delimiter = ',';
    private bool $detect_line_endings = false;

    /**
     * Function that parses a CSV file and returns results
     * as an array.
     *
     * @param string $filepath Location of the CSV file
     * @param array $column_headers Alternate values that will be used for array keys instead of first line of CSV
     * @param bool $detect_line_endings When true sets the php INI settings to allow script to detect line endings. Needed for CSV files created on Macs.
     * @param int $initial_line Sets the line of the file from which start parsing data.
     * @param string $delimiter The values delimiter (e.g. ";" or ",").
     * @return array
     */
    public function get_array(string $filepath = '', array $column_headers = [], bool $detect_line_endings = false, int $initial_line = 0, string $delimiter = ','): array
    {
        $result = [];

        // Raise memory limit (for big files)
        ini_set('memory_limit', '20M');

        // File path
        $this->filepath = $filepath ?: $this->filepath;

        // If file doesn't exist, return false
        if (!file_exists($this->filepath)) {
            return [];
        }

        // Auto detect row endings
        $this->detect_line_endings = $detect_line_endings ?: $this->detect_line_endings;

        if ($this->detect_line_endings) {
            ini_set("auto_detect_line_endings", true);
        }

        // Parse from this line on
        $this->initial_line = $initial_line ?: $this->initial_line;

        // Delimiter
        $this->delimiter = $delimiter ?: $this->delimiter;

        // Column headers
        $this->column_headers = $column_headers ?: $this->column_headers;

        // Open the CSV for reading
        $this->_get_handle();

        $row = 0;

        while (($data = fgetcsv($this->handle, 0, $this->delimiter)) !== false) {
            if ($data[0] != null) {
                if ($row < $this->initial_line) {
                    $row++;
                    continue;
                }

                // If first row, parse for column_headers
                if ($row == $this->initial_line) {
                    // If column_headers already provided, use them
                    if ($this->column_headers) {
                        foreach ($this->column_headers as $key => $value) {
                            $column_headers[$key] = trim((string)$value);
                        }
                    } else {
                        // Parse first row for column_headers to use
                        foreach ($data as $key => $value) {
                            $column_headers[$key] = trim((string)$value);
                        }
                    }
                } else {
                    $new_row = $row - $this->initial_line - 1; // needed so that the returned array starts at 0 instead of 1
                    foreach ($column_headers as $key => $value) {
                        // assumes there are as many columns as their are title columns
                        $result[$new_row][$value] = utf8_encode(trim((string)$data[$key]));
                    }
                }

                unset($data);

                $row++;
            }
        }

        $this->_close_csv();

        return $result;
    }

    /**
     * Sets the "detect_line_endings" flag
     *
     * @param bool $detect_line_endings The flag bit
     */
    private function _set_detect_line_endings(bool $detect_line_endings): void
    {
        $this->detect_line_endings = $detect_line_endings;
    }

    /**
     * Sets the "detect_line_endings" flag
     *
     * @param bool $detect_line_endings The flag bit
     * @return $this
     */
    public function detect_line_endings(bool $detect_line_endings): self
    {
        $this->_set_detect_line_endings($detect_line_endings);
        return $this;
    }

    /**
     * Sets the initial line from which start to parse the file
     *
     * @param int $initial_line Start parse from this line
     * @return $this
     */
    public function initial_line(int $initial_line): self
    {
        $this->_set_initial_line($initial_line);
        return $this;
    }

    /**
     * Sets the values delimiter
     *
     * @param string $delimiter The values delimiter (eg. "," or ";")
     * @return $this
     */
    public function delimiter(string $delimiter): self
    {
        $this->_set_delimiter($delimiter);
        return $this;
    }

    /**
     * Sets the filepath of a given CSV file
     *
     * @param string $filepath Location of the CSV file
     * @return $this
     */
    public function filepath(string $filepath): self
    {
        $this->_set_filepath($filepath);
        return $this;
    }

    /**
     * Sets the alternate column headers that will be used when creating the array
     *
     * @param array $column_headers Alternate column_headers that will be used instead of first line of CSV
     * @return $this
     */
    public function column_headers(array $column_headers): self
    {
        $this->_set_column_headers($column_headers);
        return $this;
    }

    /**
     * Opens the CSV file for parsing
     */
    private function _get_handle(): void
    {
        $this->handle = fopen($this->filepath, 'r');
    }

    /**
     * Closes the CSV file when complete
     */
    private function _close_csv(): void
    {
        fclose($this->handle);
    }

    /**
     * Sets the initial line from which start to parse the file
     *
     * @param int $initial_line Start parse from this line
     */
    private function _set_initial_line(int $initial_line): void
    {
        $this->initial_line = $initial_line;
    }

    /**
     * Sets the values delimiter
     *
     * @param string $delimiter The values delimiter (eg. "," or ";")
     */
    private function _set_delimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Sets the filepath of a given CSV file
     *
     * @param string $filepath Location of the CSV file
     */
    private function _set_filepath(string $filepath): void
    {
        $this->filepath = $filepath;
    }

    /**
     * Sets the alternate column headers that will be used when creating the array
     *
     * @param array $column_headers Alternate column_headers that will be used instead of first line of CSV
     */
    private function _set_column_headers(array $column_headers): void
    {
        if (!empty($column_headers)) {
            $this->column_headers = $column_headers;
        }
    }
}
