<?php
/**
 * @package edu.uw.uaa
 */
/**
 * Interface for database connection objects
 * Database connection pass SQL statements to the DBMS and return results in standard PHP
 * constructs. Provides access to DBMS specific quoting strategies. Provides hooks to
 * handle logging and error handling.
 * @author hanisko
 */
namespace App\Edw;

interface EdwConnectionInterface
{
    /**
     * Fetch an array of associative arrays from a database query
     * The outer array is a list of rows returned. The inner arrays are associative
     * arrays with the column names as indicies. If no results match returns
     * an empty array.
     * @param string $sql an SQL select statement
     * @return array
     */
    public function fetchAssoc($sql);

    /**
     * Fetch the contents of a single column into a simple array
     * Regardless of query provided only the values in the first column are returned.
     * If no results match returns an empty array.
     * @param string $sql an SQL select statement
     * @return array
     */
    public function fetchColumn($sql);

    /**
     * Fetch a single scalar value from a database query.
     * Regardless of results of query only the first column of the first record will
     * be returned. If no results match returns null.
     * @param string $sql an SQL select statement
     * @return string
     */
    public function fetchOne($sql);

    /**
     * Fetch the contents of two columns into an associative array
     * The first column becomes the index and the second column the value.
     * If no results match returns an empty array.
     * @param string $sql an SQL select statement
     * @return array
     */
    public function fetchPairs($sql);

    /**
     * Fetch a single row from a database query and return it as an associative array
     * Regardless of results of query only the first record will be returned.
     * If no results match returns null.
     * @param string $sql an SQL select statement
     * @return array|null
     */
    public function fetchRow($sql);

    /**
     * Get ODBC connection resource provided by odbc_connect.
     * Opens this connection if needed.
     * @return resource
     * @throws \Exception
     */
    public function getConnection();

    /**
     * Run a SQL query and return the raw result
     * @param $sql
     * @return mixed
     */
    public function query($sql);

    /**
     * Convert value to boolean with true represented as 1 and false as 0
     * @param $value
     * @return string
     */
    public function quoteBoolean($value);

    /**
     * Quote a character value for a database query
     * Special characters are escaped and the string is wrapped in single quotes
     * @param $value
     * @return string
     */
    public function quoteChar($value);

    /**
     * Quote a column descriptor for a database query
     * @param $column
     * @return string
     */
    public function quoteColumn($column);

    /**
     * Quote a date/time value for a database query
     * Strings are converted to unix timestamps (integers are assumed to be unix timestamps)
     * and converted to database expected format and wrapped in quotes
     * @param $value
     * @return string
     */
    public function quoteDateTime($value);

    /**
     * Quote a numeric value with decimal places for a database query
     * @param $value
     * @return string
     */
    public function quoteFloat($value);

    /**
     * Quote a numeric value with no decimal places for a database query
     * @param $value
     * @return string
     */
    public function quoteInteger($value);

    /**
     * Quote a table descriptor for a database query
     * @param $table
     * @return string
     */
    public function quoteTable($table);

    /**
     * Provide an ODBC connection resource generated by odbc_connect.
     * Generally this method should not be used, the purpose of this class is to create
     * the connection on demand. It is available for use in mixed code base when the
     * ODBC connection resource has already been built so this class does not do
     * redundant work.
     * @param $connection
     */
    public function setConnection($connection);

    /**
     * Starts logging of queries run against database using this connection
     * @param $activate
     */
    public function storeQueryHistory($activate = true);

}
