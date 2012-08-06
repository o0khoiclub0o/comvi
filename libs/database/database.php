<?php
/**
 * Database connector class
 *
 * @package		Comvi.Framework
 * @subpackage	Database
 */
abstract class CDatabase
{
	/**
	 * The database driver name
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * The query sql string
	 *
	 * @var string
	 **/
	protected $_sql = '';

	/**
	 * The prefix used on all database tables
	 *
	 * @var string
	 */
	protected $_table_prefix = '';

	/**
	 * The database link identifier.
	 *
	 * @var mixed
	 */
	protected $_connection = '';

	/**
	 * The last query cursor
	 *
	 * @var resource
	 */
	protected $_cursor = null;

	/**
	 * Debug option
	 *
	 * @var boolean
	 */
	protected $_debug = 0;

	/**
	 * The limit for the query
	 *
	 * @var int
	 */
	protected $_limit = 0;

	/**
	 * The for offset for the limit
	 *
	 * @var int
	 */
	protected $_offset = 0;

	/**
	 * The number of queries performed by the object instance
	 *
	 * @var int
	 */
	protected $_ticker = 0;

	/**
	 * A log of queries
	 *
	 * @var array
	 */
	protected $_log = null;

	/**
	 * The null/zero date string
	 *
	 * @var string
	 */
	protected $_nullDate = null;

	/**
	 * Quote for named objects
	 *
	 * @var string
	 */
	protected $_nameQuote = null;

	/**
	 * UTF-8 support
	 *
	 * @var boolean
	 */
	protected $_utf = 0;

	/**
	 * The fields that are to be quote
	 *
	 * @var array
	 */
	protected $_quoted = null;

	/**
	 *  Legacy compatibility
	 *
	 * @var bool
	 */
	protected $_hasQuoted = null;


	/**
	 * Database object constructor
	 *
	 * @param	array	List of options used to configure the connection
	 */
	public function __construct($options)
	{
		$prefix	= array_key_exists('prefix', $options)	? $options['prefix']	: 'cv_';


		// Determine utf-8 support.
		$this->_utf = $this->hasUTF();

		// Set charactersets (needed for MySQL 4.1.2+).
		if ($this->_utf){
			$this->setUTF();
		}

		$this->_table_prefix	= $prefix;
		$this->_ticker			= 0;
		$this->_log				= array();
		$this->_quoted			= array();
		$this->_hasQuoted		= false;
	}

	/**
	 * Database object destructor
	 *
	 * @return	boolean
	 */
	public function __destruct()
	{
		return true;
	}

	/**
	 * Test to see if the MySQLi connector is available
	 *
	 * @return boolean  True on success, false otherwise.
	 */
	abstract public function test();

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return	boolean
	 */
	abstract public function connected();

	/**
	 * Determines UTF support
	 *
	 * @return	boolean
	 */
	abstract public function hasUTF();

	/**
	 * Custom settings for UTF support
	 */
	abstract public function setUTF();

	/**
	 * Get A table
	 *
	 * @param	string	table name in plural
	 *
	 * @return	CModelList
	 */
    public function __call($name, $arguments)
    {		
		CLoader::import('ultility.inflector', 1);

		$classname	= ucfirst($name).'Model';
		$filename	= CInflector::underscore($name);
		$component	= explode('_', CInflector::singularize($filename));
		$component	= $component[0];

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($classname)) {
			$path = PATH_MODELS.$filename.'.php';

			if (!is_file($path)) {
				throw new Exception("LIB_MODEL_ERROR_FILE_NOT_FOUND|$path");
			}

			// Import the class file.
			require_once $path;

			if (!class_exists($classname)) {
				throw new Exception("LIB_MODEL_ERROR_CLASS_NOT_FOUND_IN_FILE|$classname");
			}
		}

		// Instantiate a new table class and return it.
		return new $classname($this);
    }

	/**
	 * Get A table
	 *
	 * @param	string	name of the table
	 *
	 * @return	CModelList
	 */
	public function table($name)
	{
		CLoader::import('ultility.inflector', 1);

		$classname	= CInflector::classify($name).'Model';
		$filename	= CInflector::pluralize($name);
		$component	= explode('_', $name);
		$component	= $component[0];

		// Only try to load the class if it doesn't already exist.
		if (!class_exists($classname)) {
			$path = PATH_COMPONENTS.$component.DS.'models'.DS.$filename.'.php';

			if (!is_file($path)) {
				throw new Exception("LIB_MODEL_ERROR_FILE_NOT_FOUND|$path");
			}

			// Import the class file.
			require_once $path;

			if (!class_exists($classname)) {
				throw new Exception("LIB_MODEL_ERROR_CLASS_NOT_FOUND_IN_FILE|$classname");
			}
		}

		// Instantiate a new table class and return it.
		return new $classname($this);
	}

	/**
	 * Adds a field or array of field names to the list that are to be quoted
	 *
	 * @param	mixed	Field name or array of names
	 */
	public function addQuoted($quoted)
	{
		if (is_string($quoted)) {
			$this->_quoted[] = $quoted;
		} else {
			$this->_quoted = array_merge($this->_quoted, (array)$quoted);
		}
		$this->_hasQuoted = true;
	}

	/**
	 * Splits a string of queries into an array of individual queries
	 *
	 * @param	string	The queries to split
	 * @return	array	queries
	 */
	public function splitSql($queries)
	{
		$start = 0;
		$open = false;
		$open_char = '';
		$end = strlen($queries);
		$query_split = array();

		for ($i = 0; $i < $end; $i++) {
			$current = substr($queries,$i,1);
			if (($current == '"' || $current == '\'')) {
				$n = 2;

				while(substr($queries,$i - $n + 1, 1) == '\\' && $n < $i) {
					$n ++;
				}

				if ($n%2==0) {
					if ($open) {
						if ($current == $open_char) {
							$open = false;
							$open_char = '';
						}
					} else {
						$open = true;
						$open_char = $current;
					}
				}
			}

			if (($current == ';' && !$open)|| $i == $end - 1) {
				$query_split[] = substr($queries, $start, ($i - $start + 1));
				$start = $i + 1;
			}
		}

		return $query_split;
	}



	/**
	 * Checks if field name needs to be quoted
	 *
	 * @param	string	The field name
	 * @return	bool
	 */
	public function isQuoted($fieldName)
	{
		if ($this->_hasQuoted) {
			return in_array($fieldName, $this->_quoted);
		} else {
			return true;
		}
	}

	/**
	 * Get a database escaped string
	 *
	 * @param	string	The string to be escaped
	 * @param	boolean	Optional parameter to provide extra escaping
	 * @return	string
	 */
	abstract public function getEscaped($text, $extra = false);

	/**
	 * Get a database error log
	 *
	 * @return	array
	 */
	public function getLog()
	{
		return $this->_log;
	}

	/**
	 * Get the total number of queries made
	 *
	 * @return array
	 */
	public function getTicker()
	{
		return $this->_ticker;
	}

	/**
	 * Quote an identifier name (field, table, etc).
	 *
	 * @param	string	$s	The identifier to quote.
	 *
	 * @return	string	The quoted identifier.
	 */
	public function nameQuote($s)
	{
		$q = $this->_nameQuote;

		if (strlen($q) == 1) {
			return $q.$s.$q;
		} else {
			return $q{0}.$s.$q{1};
		}
	}

	/**
	 * Sets the SQL query string for later execution.
	 *
	 * This function replaces a string identifier <code>#__</code> with the
	 * string held is the <var>_table_prefix</var> class variable.
	 *
	 * @param	string	The SQL query.
	 * @param	string	The offset to start selection.
	 * @param	string	The number of results to return.
	 * @param	string	The common table prefix (not available in Joomla 1.6).
	 *
	 * @return	object	This object to support chaining.
	 */
	public function setQuery($query, $offset = 0, $limit = 0)
	{
		$this->_sql		= $query;
		$this->_limit	= (int) $limit;
		$this->_offset	= (int) $offset;

		return $this;
	}

	/**
	 * This function replaces a string identifier <var>$prefix</var> with the
	 * string held is the <var>_table_prefix</var> class variable.
	 *
	 * @param	string	The SQL query
	 * @param	string	The common table prefix
	 */
	public function replacePrefix($sql, $prefix='#__')
	{
		$sql = trim($sql);

		$escaped = false;
		$quoteChar = '';

		$n = strlen($sql);

		$startPos = 0;
		$literal = '';
		while ($startPos < $n) {
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false) {
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);
			if (($k !== FALSE) && (($k < $j) || ($j === FALSE))) {
				$quoteChar	= '"';
				$j			= $k;
			} else {
				$quoteChar	= "'";
			}

			if ($j === false) {
				$j = $n;
			}

			$literal .= str_replace($prefix, $this->_table_prefix,substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n) {
				break;
			}

			// quote comes first, find end of quote
			while (TRUE) {
				$k = strpos($sql, $quoteChar, $j);
				$escaped = false;
				if ($k === false) {
					break;
				}
				$l = $k - 1;

				while ($l >= 0 && $sql{$l} == '\\') {
					$l--;
					$escaped = !$escaped;
				}

				if ($escaped) {
					$j	= $k+1;
					continue;
				}
				break;
			}

			if ($k === FALSE) {
				// error in the query - no end quote; ignore it
				break;
			}
			$literal .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k+1;
		}

		if ($startPos < $n) {
			$literal .= substr($sql, $startPos, $n - $startPos);
		}
		return $literal;
	}

	/**
	 * Get the current or query, or new CDatabaseQuery object.
	 *
	 * @param	boolean	False to return the last query set by setQuery, True to return a new CDatabaseQuery object.
	 * @return	string	The current value of the internal SQL variable
	 */
	public function getQuery($new = false)
	{
		if ($new) {
			CLoader::import('database.databasequery', 1);
			CLoader::import('database.databasequeryelement', 1);
			return new CDatabaseQuery;
		} else {
			return $this->_sql;
		}
	}

	/**
	 * Execute the query
	 *
	 * @return	mixed	A database resource if successful, FALSE if not.
	 */
	abstract public function query();

	/**
	 * Get the affected rows by the most recent query
	 *
	 * @return	int	The number of affected rows in the previous operation
	 */
	abstract public function getAffectedRows();

	/**
	 * Execute a batch query
	 *
	 * @return	mixed	A database resource if successful, FALSE if not.
	 */
	abstract public function queryBatch($abort_on_error=true, $p_transaction_safe = false);

	/**
	 * Diagnostic function
	 */
	abstract public function explain();

	/**
	 * Get the number of rows returned by the most recent query
	 *
	 * @param	object	Database resource
	 * @return	int		The number of rows
	 */
	abstract public function getNumRows($cur=null);

	/**
	 * This method loads the first field of the first row returned by the query.
	 *
	 * @return	mixed	The value returned in the query or null if the query failed.
	 */
	abstract public function loadResult();

	/**
	 * Load an array of single field results into an array
	 */
	abstract public function loadResultArray($numinarray = 0);

	/**
	 * Fetch a result row as an associative array
	 */
	abstract public function loadAssoc();

	/**
	 * Load a associactive list of database rows
	 *
	 * @param	string	The field name of a primary key
	 * @param	string	An optional column name. Instead of the whole row, only this column value will be in the return array.
	 * @return	array	If key is empty as sequential list of returned records.
	 */
	abstract public function loadAssocList($key = null, $column = null);

	/**
	 * This global function loads the first row of a query into an object
	 *
	 * @return	object
	 */
	abstract public function loadObject();

	/**
	 * Load a list of database objects
	 *
	 * @param	string	The field name of a primary key
	 * @return	array	If <var>key</var> is empty as sequential list of returned records.
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 */
	abstract public function loadObjectList($key='');

	/**
	 * Load the first row returned by the query
	 *
	 * @return	mixed	The first row of the query.
	 */
	abstract public function loadRow();

	/**
	 * Load a list of database rows (numeric column indexing)
	 *
	 * If <var>key</var> is not empty then the returned array is indexed by the value
	 * the database key.  Returns <var>null</var> if the query fails.
	 *
	 * @param	string	The field name of a primary key
	 * @return	array
	 */
	abstract public function loadRowList($key='');

	/**
	 * Load the next row returned by the query.
	 *
	 * @return	mixed	The result of the query as an array, false if there are no more rows, or null on an error.
	 */
	abstract public function loadNextRow();

	/**
	 * Load the next row returned by the query.
	 *
	 * @return	mixed	The result of the query as an object, false if there are no more rows, or null on an error.
	 */
	abstract public function loadNextObject();

	/**
	 * Inserts a row into a table based on an objects properties
	 * @param	string	The name of the table
	 * @param	object	An object whose properties match table fields
	 * @param	string	The name of the primary key. If provided the object property is updated.
	 */
	abstract public function insertObject($table, &$object, $keyName = NULL);

	/**
	 * Update an object in the database
	 *
	 * @param	string
	 * @param	object
	 * @param	string
	 * @param	boolean
	 */
	abstract public function updateObject($table, &$object, $keyName, $updateNulls=false);

	/**
	 * Get the ID generated from the previous INSERT operation
	 *
	 * @return mixed
	 */
	abstract public function insertid();

	/**
	 * Get the version of the database connector
	 */
	public function getVersion()
	{
		return 'Not available for this connector';
	}

	/**
	 * List tables in a database
	 *
	 * @return	array	A list of all the tables in the database
	 */
	abstract public function getTableList();

	/**
	 * Shows the CREATE TABLE statement that creates the given tables
	 *
	 * @param	array|string	A table name or a list of table names
	 * @return	array A list the create SQL for the tables
	 */
	abstract public function getTableCreate($tables);

	/**
	 * Retrieves information about the given tables
	 *
	 * @param	array|string	A table name or a list of table names
	 * @param	boolean			Only return field types, default true
	 * @return	array An array of fields by table
	 */
	abstract public function getTableFields($tables, $typeonly = true);

	/**
	 * Get a quoted database escaped string
	 *
	 * @param	string	A string
	 * @param	boolean	Default true to escape string, false to leave the string unchanged
	 * @return	string
	 */
	public function quote($text, $escaped = true)
	{
		return '\''.($escaped ? $this->getEscaped($text) : $text).'\'';
	}
}
?>