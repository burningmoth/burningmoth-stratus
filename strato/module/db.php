<?php
/**
 * Database module.
 */
namespace BurningMoth\Stratus;

// setup default hooks ...
$_ENV['STRATUS_CALLBACKS']['db'] = function( $tratus ){

	$tratus
	// default dsn-specific pdo options
	->addFilter('db_pdo_options', 'filter_db_pdo_options_per_dsn', -999);

};

// register trait to extend Stratus ...
$_ENV['STRATUS_TRAITS'][ __NAMESPACE__ . '\DB_Module' ] = array();
trait DB_Module {

	/**
	 * Keyed array of registered databases.
	 * @var array
	 */
	public $___db = [];

	/**
	 * Register a database.
	 * @param string $key
	 * @param string $dsn
	 * @param string $username
	 * @param string $password
	 * @param array $options
	 * @return Stratus
	 */
	public function db_register( $key, $dsn, $username = '', $password = '', array $options = [] ) {

		// parse db scheme from dsn ...
		$scheme = parse_url($dsn, \PHP_URL_SCHEME);

		/**
		 * Filter database info.
		 * @filter db_register
		 * @value array $db
		 * @param string $key
		 */
		$this->___db[ $key ] = $this->filter('db_register', compact('key', 'dsn', 'username', 'password', 'options', 'scheme'), $key);

		return $this;
	}

	/**
	 * Fetch database setting(s).
	 * @param string $db_key
	 * @param string $key (optional)
	 * @param mixed $alt (optional)
	 * @return array|mixed
	 */
	public function db_get( $key = null, $alt = null, $db_key = null ) {
		$db = $this->___db[ $db_key ?? $this->db_key ] ?? [];
		return (
			is_string($key)
			? $db[ $key ] ?? $alt
			: $db
		);
	}

	/**
	 * Update database setting.
	 * @param string $key
	 * @param mixed $value (nulls are deleted)
	 * @param string $db_key (optional)
	 * @return Stratus
	 */
	public function db_set( $key, $value, $db_key = null ) {
		$db = $this->db_get(null, null, $db_key);
		if ( is_null($value) ) unset($db[ $key ]);
		else $db[ $key ] = $value;
		$this->___db[ $db_key ?? $this->db_key ] = $db;
		return $this;
	}

	/**
	 * Switch or establish a database connection.
	 * @param string $key
	 * @return Stratus
	 */
	public function db_use( string $key = 'default' ) {

		// set current database registration key ...
		$this->db_key = $key;

		// retrieve existing database ...
		if ( $db = $this->db_get() );

		// set mysql database from defaults, pre-2.1 single connection variables ...
		elseif ( isset($this->db_name) ) $db = $this->db_register(
			$key,
			sprintf(
				'mysql:host=%s;port=%d;dbname=%s;charset=utf8',
				$this->get('db_host', '127.0.0.1'),
				$this->get('db_port', 3306),
				$this->get('db_name', '')
			),
			$this->get('db_user', ''),
			$this->get('db_pass', '')
		)->db_get();

		// set sqlite database from path ...
		elseif ( isset($this->db_path) ) $db = $this->db_register($key, 'sqlite:'.$this->db_path)->db_get();

		// nothing ! trigger error ...
		else trigger_error('No registered database found!', \E_USER_ERROR);

		// connection set ? just switch to it ...
		if ( $this->db_conn = $db['connection'] ?? null );

		// set connection ...
		else {

			/**
			 * Filter before making database connection.
			 * @filter db_connecting
			 * @value array $db
			 * @param string $key
			 */
			$db = $this->filter('db_connecting', $db, $key);

			try {

				// create connection ...
				$this->db_conn = $this->___db[ $key ]['connection'] = new \PDO(
					$db['dsn'] ?? '',
					$db['username'] ?? '',
					$db['password'] ?? '',
					/**
					 * Filter PDO options passed to connection.
					 * @filter db_pdo_options
					 * @value array $options
					 * @param array $db
					 * @param string $key
					 */
					$this->filter(
						'db_pdo_options',
						array_replace([

							// default to fetch object ...
							\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,

							// return columns in lower case ...
							\PDO::ATTR_CASE => \PDO::CASE_LOWER,

							// set error mode ...
							\PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING,

							// will muck up errorInfo if set ...
							\PDO::ATTR_EMULATE_PREPARES => false

						], (array) ( $db['options'] ?? [] ) ),
						$db,
						$key
					)
				);

			} catch ( \PDOException $e ) {
				$this->catchException($e);
			}

			/**
			 * Trigger after making database connection
			 * @action db_connected
			 * @param PDO $conn
			 * @param array $db
			 * @param string $key
			 */
			$this->action('db_connected', $this->___db_conn, $db, $key);

		}

		return $this;
	}

	/**
	 * DSN specific default options.
	 * @filter db_pdo_options
	 */
	public function filter_db_pdo_options_per_dsn( $options, $db = [] ) {

		// mysql specific options ...
		if (
			'mysql' === $db['scheme'] ?? ''
		) $options = array_replace([

			// set charset to utf8 in PHP 5.2 - 5.3+ this can be set in the dsn (above)
			// UTC default time_zone affects NOW(), etc.
			\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8', time_zone = '+00:00'"

		], $options);

		return $options;
	}

	/**
	 * Return database connection.
	 *	- relegated setting connection to db_use()
	 * @return PDO object
	 */
	public function db_conn() {
		return $this->db_conn ?? $this->db_use()->db_conn;
	}


	/**
	 * Close out a record set or the database connection.
	 * @param PDOStatement $result (optional)
	 * @return bool
	 */
	public function db_close( \PDOStatement $result = null ) {

		// statement ? close that ...
		if ( $result ) return $result->closeCursor();

		// otherwise, close connection ...
		unset($this->db_conn);
		$this->db_set('connection', null);
		return true;

	}


	/**
	 * Get/set Serialization handler.
	 * The serialization function called by db_quote() for non-scalar data (should return string value).
	 * @param callable $callback (optional)
	 *	- pass null or non-callable value to reset back to default
	 * @return callable
	 */
	public function db_serialization_handler() {

		// setter ...
		if ( func_num_args() ) {
			$callback = func_get_arg(0);
			if ( ! is_callable($callback, true) ) $callback = null;
			$this->db_set('serialization_handler', $callback);
		}

		// get currently set handler or default 'serialize' ...
		return $this->db_get('serialization_handler', '\serialize');

	}

	/**
	 * Quote value for database utilization.
	 * @param mixed $value
	 * @return string
	 */
	public function db_quote( $value ) {

		// number (not numeric string!) ? stringify w/o quotes ...
		if ( is_integer($value) || is_float($value) ) $value = (string) $value;

		// null ? return NULL keyword ...
		elseif ( is_null($value) ) $value = 'NULL';

		// bool ? return TRUE or FALSE keyword ...
		elseif ( is_bool($value) ) $value = ( $value ? 'TRUE' : 'FALSE' );

		// quote everything else ...
		else $value = $this->db_conn()->quote(
			is_scalar($value)
			? $value
			: call_user_func($this->db_serialization_handler(), $value)
		);

		return $value;

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
	 * Quote identifier (table name, column name, etc.)
	 * @param string $id
	 * @return string
	 */
	public function db_quote_id( string $value ) {
		return preg_replace('/([\w-]+)/', "`$1`", $value);
	}

	/**
	 * Prepares a query with escaped variables.
	 * @param string $sql
	 * @param array $vars
	 * @return string
	 */
	public function db_prepare( string $sql, array $vars = array() ) {

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

				// return quoted identifier ...
				case '`':
					$value = $tratus->db_quote_id($value);
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
	public function db_query( string $sql = null, array $vars = array() ) {

		// prepare query ...
		$sql = $this->db_prepare($sql, $vars);

		// record this last prepared query ...
		$this->db_last_query = $sql;

		// generate result from connection ...
		$result = $this->db_conn()->prepare($sql);

		// report any database connection error ...
		if (
			! $result
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
			! $result->rowCount()
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
	public function db_row_count( \PDOStatement $result = null ) {
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
	public function db_row( \PDOStatement $result, $type = \PDO::FETCH_OBJ ) {
		return $result->fetch($type);
	}


	/**
	 * Return array of rows from a database result.
	 * @param PDOStatement $result
	 * @param PDO::FETCH_* $type - can be PDO::FETCH_OBJ, PDO::FETCH_ASSOC, PDO::FETCH_NUM
	 * @return array
	 */
	public function db_rows( \PDOStatement $result, $type = \PDO::FETCH_OBJ ) {
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
	public function db_column( \PDOStatement $result, $column = 0, $index = null ) {
		$rows = $result->fetchAll( is_int($column) ? \PDO::FETCH_NUM : \PDO::FETCH_ASSOC );
		return \array_column($rows, $column, $index);
	}


	/**
	 * Return result of a database field.
	 * @param PDOStatement $result
	 * @param integer $column
	 * @param integer $row
	 * @return mixed|null
	 */
	public function db_field( \PDOStatement $result, int $column = 0, int $row = 0 ) {
		return (
			( $row = $result->fetch( \PDO::FETCH_NUM, \PDO::FETCH_ORI_ABS, $row ) )
			&& isset($row[ $column ])
			? $row[ $column ]
			: null
		);
	}


}

