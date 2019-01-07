<?php
/**
 * Database module.
 */
namespace BurningMoth\Stratus;

// register trait to extend Stratus ...
$_ENV['STRATUS_TRAITS'][ __NAMESPACE__ . '\DB_Module' ] = array();
trait DB_Module {

	/**
	 * Open/return database connection.
	 * @return PDO object
	 */
	public function db_conn() {

		// not connected ? connect now ...
		if ( !( $conn =& $this->ref('db_conn', false) ) ) {

			// create connection object ...
			$conn = new \PDO(
				sprintf(
					'mysql:host=%s;dbname=%s;charset=utf8',
					$this->get('db_host', 'localhost'),
					$this->get('db_name', '')
				),
				$this->get('db_user', ''),
				$this->get('db_pass', ''),
				[
					// set charset to utf8 in PHP 5.2 - 5.3+ this can be set in the dsn (above)
					\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",

					// default to fetch object ...
					\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,

					// return columns in lower case ...
					\PDO::ATTR_CASE => \PDO::CASE_LOWER,

					// set error mode ...
					\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING,

					// will muck up errorInfo if set ...
					\PDO::ATTR_EMULATE_PREPARES => false
				]
			);

			// upgrade group_concat max length ...
			//$conn->query("SET GLOBAL group_concat_max_len = 1000000");

		}

		// return connection ...
		return $conn;

	}


	/**
	 * Close out a record set or the database connection.
	 * @param PDOStatement $result (optional)
	 * @return bool
	 */
	public function db_close( $result = null ) {
		if ( is_null($result) ) {
			unset($this->db_conn);
			return true;
		}
		return $result->closeCursor();
	}


	/**
	 * Quote value for database utilization.
	 * @param mixed $value
	 * @return string
	 */
	public function db_quote( $value ) {

		// null ? return NULL keyword ...
		if ( is_null($value) ) return 'NULL';

		// non-scalar ? serialize value first ...
		if ( !is_scalar($value) ) $value = serialize($value);

		// quoted value ...
		return $this->db_conn()->quote($value);

	}


	/**
	 * Escape value for database utilization.
	 * @param mixed $value
	 * @return string
	 */
	public function db_escape( $value ) {

		// return quoted value sans quotes ...
		return trim( $this->db_quote($value), "'");

	}


	/**
	 * Prepares a query with escaped variables.
	 * @param string $sql
	 * @param array $vars
	 * @return string
	 */
	public function db_prepare( $sql, array $vars = array() ) {

		// prepare variables ...
		if ( $vars ) array_walk($vars, function( &$value, $key, $tratus ){

			switch ( $key[0] ) {
				// force integer value ...
				case '#':
					$value = intval($value);
					break;

				// force floating point ...
				case '%':
					$value = floatval($value);
					break;

				// return quoted, escaped string ... :var is PDO style
				case ':':
					$value = $tratus->db_quote($value);
					break;

				// return escaped string, unquoted ...
				case '!':
					$value = $tratus->db_escape($value);
					break;

				// do nothing to process the value, send as-is
				case '?':
				default:
					break;
			}

		}, $this);

		return trim(strtr($sql, $vars));
	}


	/**
	 * Execute database query.
	 * @param string $sql
	 * @param array $vars
	 * @return PDOStatement
	 */
	public function db_query( $sql = null, array $vars = array() ) {

		// prepare query ...
		$sql = $this->db_prepare($sql, $vars);

		// record this last prepared query ...
		$this->db_last_query = $sql;

		// generate result from connection ...
		$result = $this->db_conn()->prepare($sql);

		// report any database connection error ...
		if (
			!$result
			&& ( $error = $this->db_conn()->errorInfo() )
			&& !empty($error[1])
			&& ( $error = $error[2] )
		) {
			trigger_error(sprintf('%s in query "%s"', $error, $sql), \E_USER_WARNING);
			return false;
		}

		// execute statement ...
		$result->execute();

		// report any statement error ...
		if (
			( $error = $result->errorInfo() )
			&& !empty($error[1])
			&& ( $error = $error[2] )
		) {
			trigger_error(sprintf('%s in query "%s"', $error, $sql), \E_USER_WARNING);
			return false;
		}

		// record affected rows ...
		$this->db_affected_rows = (

			// return row count for select statement ...
			!$result->rowCount()
			&& stripos($sql, 'select') === 0
			? $this->db_conn()->query("SELECT FOUND_ROWS()")->fetchColumn()

			// record affected rows at the connection level ( row count is at the statement level ) ...
			: $result->rowCount()

		);

		// return result ...
		return $result;

	}


	/**
	 * Return number of rows returned from a result or affected by the last DELETE or UPDATE statement.
	 * @param PDOStatement $result
	 * @return int
	 */
	public function db_row_count( $result = null ) {
		$count = ( is_null($result) ? $this->get('db_affected_rows', 0) : $result->rowCount() );
		if ( $count === false ) $count = 0;
		return $count;
	}


	/**
	 * Return last auto-incremented id from INSERT or REPLACE statement.
	 * @return mixed
	 */
	public function db_id() {
		return $this->db_conn()->lastInsertId();
	}


	/**
	 * Return a row from a database result.
	 * @param PDOStatement $result
	 * @param PDO::FETCH_* $type - can be PDO::FETCH_OBJ, PDO::FETCH_ASSOC, PDO::FETCH_NUM
	 * @param array|object
	 */
	public function db_row( $result, $type = \PDO::FETCH_OBJ ) {
		return $result->fetch($type);
	}


	/**
	 * Return array of rows from a database result.
	 * @param PDOStatement $result
	 * @param PDO::FETCH_* $type - can be PDO::FETCH_OBJ, PDO::FETCH_ASSOC, PDO::FETCH_NUM
	 * @return array
	 */
	public function db_rows( $result, $type = \PDO::FETCH_OBJ ) {
		return $result->fetchAll($type);
	}


	/**
	 * Return array of one column results, optionally indexed.
	 * @see array_column()
	 * @param PDOStatement $result
	 * @param integer|string $column
	 * @param integer|string $index (optional)
	 * @return array
	 */
	public function db_column( $result, $column = 0, $index = null ) {
		$rows = $result->fetchAll(\PDO::FETCH_ASSOC);
		return \array_column($rows, $column, $index);
	}


	/**
	 * Return result of a database field.
	 * @param PDOStatement $result
	 * @param integer $column
	 * @param integer $row
	 */
	public function db_field( $result, $column = 0, $row = 0 ) {
		if ( $row > 0 ) {
			$rows = $this->db_column($result, $column);
			return ( $row >= count($rows) ? end($rows) : $rows[ $row ] );
		}
		return $result->fetchColumn($column);
	}


}

