<?php
/**
 * @package app.treq
 */

/**
 * Helpers for parsing EDW values to standard values
 * @author ladonnee
 */

namespace App\Updaters;

use Carbon\Carbon;

class EdwParser
{

    public function date($value)
    {
        return substr($value, 0, 19);
    }

    /**
     * Parses a date stored as 8 character YYYYMMDD
     * @param $value
     * @return string
     */
    public function dateYmd($value)
    {
        if ($value == 0) {
            return null;
        }
        return Carbon::createFromFormat('Ymdhis', $value . '000000');
    }

    /**
     * Parses values from an EDW "Last, First" value
     * Returns an array with 'first' and 'last' indexes with names converted to proper case
     * @param $value
     * @return array|null
     */
    public function lastfirst($value)
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        }
        if (strpos($value, ',')) {
            $lastfirst = explode(',', $value);
        } else {
            $lastfirst = [$value, ''];
        }
        return [
            'first' => $this->name($lastfirst[1]),
            'last' => $this->name($lastfirst[0]),
        ];
    }

    /**
     * Converts name value to proper case
     * @param $value
     * @return array|null
     */
    public function name($value)
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        }
        $value = strtolower($value);
        $value = str_replace('-', '[-] ', $value);
        $value = str_replace("'", '[apos] ', $value);
        $value = ucwords($value);
        $value = str_replace('[-] ', '-', $value);
        $value = str_replace('[apos] ', "'", $value);
        return $value;
    }

    /**
     * Converts empty values to null or casts to integer
     * @param $value
     * @return null|integer
     */
    public function integer($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int)$value;
    }

    /**
     * Converts empty values to null or trims whitespace
     * @param $value
     * @return null|string
     */
    public function string($value)
    {
        $value = trim($value);
        if (empty($value)) {
            return null;
        }
        return $value;
    }

    /**
     * Tests whether an EDW space padded value is empty
     * @param $value
     * @return null|string
     */
    public function isEmpty($value)
    {
        $unpadded = trim($value);
        return empty($unpadded);
    }

}
