<?php

namespace App\Edw;

/**
 * ODBC database connection to the UW Enterprise Data Warehouse
 */
class EdwConnection implements EdwConnectionInterface
{
    const DB_QUOTE = "'";
    protected $config;
    protected $connection;
    protected $logger;
    protected $logLocal = [];
    protected $queryHistory;
    protected $storeQueryHistory = false;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Fetch an array of associative arrays from a database query. The outer
     * array is a list of rows returned. The inner arrays are associative
     * arrays with the column names as indicies. If no results match returns
     * an empty array.
     * @param string $sql an SQL select statement
     * @return array
     */
    public function fetchAssoc($sql)
    {
        $result_id = $this->doQuery($sql);
        $out = array();
        while (($row = odbc_fetch_array($result_id)) !== false) {
            $out[] = $row;
        }
        odbc_free_result($result_id);
        return $out;
    }

    public function getColumns($sql)
    {

    }

    /**
     * Fetch the contents of two columns into an associative array where
     * the first column becomes the index and the second column the value.
     * If no results match returns an empty array.
     * @param string $sql an SQL select statement
     * @return array
     */
    public function fetchPairs($sql)
    {
        $result_id = $this->doQuery($sql);
        $out = array();
        for ($i = 1; odbc_fetch_row($result_id, $i); ++$i) {
            $out[odbc_result($result_id, 1)] = odbc_result($result_id, 2);
        }
        odbc_free_result($result_id);
        return $out;
    }

    /**
     * Fetch the contents of a single column into a simple numerically
     * indexed array. Regardless of query provided only the values in the first
     * column are returned. If no results match returns an empty array.
     * @param string $sql an SQL select statement
     * @return array
     */
    public function fetchColumn($sql)
    {
        $result_id = $this->doQuery($sql);
        $out = array();
        for ($i = 1; odbc_fetch_row($result_id, $i); ++$i) {
            $out[] = odbc_result($result_id, 1);
        }
        odbc_free_result($result_id);
        return $out;
    }

    /**
     * Fetch a single scalar value from a database query. Regardless of
     * results of query only the first column of the first record will
     * be returned. If no results match returns null.
     * @param string $sql an SQL select statement
     * @return string
     */
    public function fetchOne($sql)
    {
        $result_id = $this->doQuery($sql);

        if (odbc_fetch_row($result_id, 1)) {
            $out = odbc_result($result_id, 1);
        } else {
            // if didn't match any rows send null back
            $out = null;
        }
        odbc_free_result($result_id);
        return $out;
    }

    /**
     * Fetch a single row from a database query and return it as an associative
     * array. Regardless of results of query only the first record will be
     * returned. If no results match returns null.
     * @param string $sql an SQL select statement
     * @return array|null
     */
    public function fetchRow($sql)
    {
        $result_id = $this->doQuery($sql);
        if (odbc_fetch_row($result_id, 1)) {
            $out = odbc_fetch_array($result_id, 1);
        } else {
            // if didn't match any rows send null back
            $out = null;
        }
        odbc_free_result($result_id);
        return $out;
    }

    /**
     * Get ODBC connection resource provided by odbc_connect.
     * Opens this connection if needed.
     * @return resource
     * @throws \Exception
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $this->connection = odbc_connect($this->config['dsn'], $this->config['username'], $this->config['password']);
            if ($this->connection === false) {
                throw new EdwException(odbc_errormsg(), odbc_error(), null, 'odbc_connect');
            }
        }
        return $this->connection;
    }

    public function query($sql)
    {
        return $this->doQuery($sql);
    }

    public function quoteBoolean($value)
    {
        if (is_null($value)) return 'NULL';
        return ($value) ? 1 : 0;
    }

    public function quoteChar($value)
    {
        if (is_null($value)) return 'NULL';
        return str_replace("'", "''", $value);
    }

    public function quoteColumn($column)
    {
        return $this->squareQuotes($column);
    }

    public function quoteDateTime($value)
    {
        if (is_null($value)) return 'NULL';
        if (is_numeric($value)) {
            $value = (int) $value;
        } else {
            $value = strtotime($value);
        }
        return self::DB_QUOTE . date('Y-m-d H:i:s', $value) . self::DB_QUOTE;
    }

    public function quoteFloat($value)
    {
        if (is_null($value)) return 'NULL';
        return (float) $value;
    }

    public function quoteInteger($value)
    {
        if (is_null($value)) return 'NULL';
        return (int) $value;
    }

    public function quoteTable($table)
    {
        return $this->squareQuotes($table);
    }

    /**
     * Provide an ODBC connection resource generated by odbc_connect.
     * Generally this method should not be used, the purpose of this class is to create
     * the connection on demand. It is available for use in mixed code base when the
     * ODBC connection resource has already been built so this class does not do
     * redundant work.
     * @param $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function storeQueryHistory($activate = true)
    {
        $this->storeQueryHistory = (boolean) $activate;
        if ($this->storeQueryHistory) {
            $this->queryHistory = array();
        }
    }

    protected function doQuery($sql)
    {
        if ($this->storeQueryHistory) {
            $this->queryHistory[] = $sql;
        }
        $conn = $this->getConnection();
        $result_id = odbc_exec($conn, $sql);
        if ($result_id === false) {
            throw new EdwException(odbc_errormsg($conn), odbc_error($conn), null, $sql);
        }
        return $result_id;
    }

    /**
     * Wraps a SQL Server entity in square brackets
     * If the entity is multipart divided by dots quotes each part individually
     * @param string $name of a table, view, or column
     * @return string
     */
    protected function squareQuotes($name)
    {
        $parts = explode('.',$name);
        $num_parts = count($parts);
        for ($i = 0; $i < $num_parts; ++$i) {
            $parts[$i] = '['.$parts[$i].']';
        }
        return implode('.', $parts);
    }
}
