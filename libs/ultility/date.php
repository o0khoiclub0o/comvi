<?php
/**
 * CDate is a class that stores a date and provides logic to manipulate
 * and render that date in a variety of formats.
 *
 * @package		Comvi.Framework
 * @subpackage	Common
 */
class CDate/* extends DateTime */
{
	const DAY_ABBR = "\x021\x03";
	const DAY_NAME = "\x022\x03";
	const MONTH_ABBR = "\x023\x03";
	const MONTH_NAME = "\x024\x03";

	/**
	 * The format string to be applied when using the __toString() magic method.
	 *
	 * @var		string
	 */
	protected $format = 'd-m-Y H:i:s';

	/**
	 * The timezone for usage in rending dates as strings.
	 *
	 * @var		string
	 */
	protected $timezone = 'Europe/London';

	/**
	 * The timestamp of this CDate object.
	 *
	 * @var		int
	 */
	protected $timestamp;

	/**
	 * The micro timestamp of this CDate object.
	 *
	 * @var		float
	 */
	protected $microtimestamp;

	/**
	 * An array of offsets and time zone strings representing the available
	 */
	protected static $offsets = array(
		'-12'	=> 'Etc/GMT-12',
		'-11'	=> 'Pacific/Midway',
		'-10'	=> 'Pacific/Honolulu',
		'-9.5'	=> 'Pacific/Marquesas',
		'-9'	=> 'US/Alaska',
		'-8'	=> 'US/Pacific',
		'-7'	=> 'US/Mountain',
		'-6'	=> 'US/Central',
		'-5'	=> 'US/Eastern',
		'-4.5'	=> 'America/Caracas',
		'-4'	=> 'America/Barbados',
		'-3.5'	=> 'Canada/Newfoundland',
		'-3'	=> 'America/Buenos_Aires',
		'-2'	=> 'Atlantic/South_Georgia',
		'-1'	=> 'Atlantic/Azores',
		'0'		=> 'Europe/London',
		'1'		=> 'Europe/Amsterdam',
		'2'		=> 'Europe/Istanbul',
		'3'		=> 'Asia/Riyadh',
		'3.5'	=> 'Asia/Tehran',
		'4'		=> 'Asia/Muscat',
		'4.5'	=> 'Asia/Kabul',
		'5'		=> 'Asia/Karachi',
		'5.5'	=> 'Asia/Calcutta',
		'5.75'	=> 'Asia/Katmandu',
		'6'		=> 'Asia/Dhaka',
		'6.5'	=> 'Indian/Cocos',
		'7'		=> 'Asia/Bangkok',
		'8'		=> 'Australia/Perth',
		'8.75'	=> 'Australia/West',
		'9'		=> 'Asia/Tokyo',
		'9.5'	=> 'Australia/Adelaide',
		'10'	=> 'Australia/Brisbane',
		'10.5'	=> 'Australia/Lord_Howe',
		'11'	=> 'Pacific/Kosrae',
		'11.5'	=> 'Pacific/Norfolk',
		'12'	=> 'Pacific/Auckland',
		'12.75'	=> 'Pacific/Chatham',
		'13'	=> 'Pacific/Tongatapu',
		'14'	=> 'Pacific/Kiritimati'
	);


	/**
	 * Constructor.
	 *
	 * @param	string	String in a format accepted by strtotime(), defaults to "now".
	 * @param	mixed	Time zone to be used for the date, can be int/ float/ string
	 */
	public function __construct($time = 'now', $tz = null)
	{
		if (isset($tz)) {
			if (is_numeric($tz)) {
				$this->timezone = self::$offsets[(string) (float) $tz];
			}
			elseif (is_string($tz)) {
				$this->timezone = $tz;
			}
		}

		date_default_timezone_set($this->timezone);
		if ($time != 'micro') {
			$this->timestamp = strtotime($time);
		}
		else {
			list($usec, $sec) = explode(' ', microtime());
			$this->timestamp = (int) $sec;
			$this->microtimestamp = (float) $usec + (float) $sec;
		}
	}

	/**
	 * Magic method to access properties of the date given by class to the format method.
	 *
	 * @param	string	The name of the property.
	 * @return	mixed	A value if the property name is valid, null otherwise.
	 */
	/*public function __get($name)
	{
		$value = null;

		switch ($name) {
			case 'daysinmonth':
				$value = $this->format('t', true);
				break;

			case 'dayofweek':
				$value = $this->format('N', true);
				break;

			case 'dayofyear':
				$value = $this->format('z', true);
				break;

			case 'day':
				$value = $this->format('d', true);
				break;

			case 'hour':
				$value = $this->format('H', true);
				break;

			case 'isleapyear':
				$value = (boolean) $this->format('L', true);
				break;

			case 'hour':
				$value = $this->format('H', true);
				break;

			case 'minute':
				$value = $this->format('i', true);
				break;

			case 'month':
				$value = $this->format('m', true);
				break;

			case 'ordinal':
				$value = $this->format('S', true);
				break;

			case 'second':
				$value = $this->format('s', true);
				break;

			case 'week':
				$value = $this->format('W', true);
				break;

			case 'year':
				$value = $this->format('Y', true);
				break;

			default:
				$trace = debug_backtrace();
				trigger_error(
					'Undefined property via __get(): ' . $name .
					' in ' . $trace[0]['file'] .
					' on line ' . $trace[0]['line'],
					E_USER_NOTICE
				);
		}

		return $value;
	}*/

	/**
	 * Gets the date as a formatted string.
	 *
	 * @param	string	The date format specification string (see {@link PHP_MANUAL#date})
	 * @param	boolean	True to return the date string in the local time zone, false to return it in GMT.
	 * @return	string	The date string in the specified format format.
	 */
	public function format($format)
	{
		// Do string replacements for date format options that can be translated.
		$format = preg_replace('/(^|[^\\\])D/', "\\1".self::DAY_ABBR, $format);
		$format = preg_replace('/(^|[^\\\])l/', "\\1".self::DAY_NAME, $format);
		$format = preg_replace('/(^|[^\\\])M/', "\\1".self::MONTH_ABBR, $format);
		$format = preg_replace('/(^|[^\\\])F/', "\\1".self::MONTH_NAME, $format);

		// Format the date.
		$return = date($format, $this->timestamp);

		// Manually modify the month and day strings in the formated time.
		if (strpos($return, self::DAY_ABBR) !== false) {
			$return = str_replace(self::DAY_ABBR, $this->convertDayToString(parent::format('w'), true), $return);
		}
		if (strpos($return, self::DAY_NAME) !== false) {
			$return = str_replace(self::DAY_NAME, $this->convertDayToString(parent::format('w')), $return);
		}
		if (strpos($return, self::MONTH_ABBR) !== false) {
			$return = str_replace(self::MONTH_ABBR, $this->convertMonthToString(parent::format('n'), true), $return);
		}
		if (strpos($return, self::MONTH_NAME) !== false) {
			$return = str_replace(self::MONTH_NAME, $this->convertMonthToString(parent::format('n')), $return);
		}

		return $return;
	}

	/**
	 * Translates day of week number to a string.
	 *
	 * @param	integer	The numeric day of the week.
	 * @param	boolean	Return the abreviated day string?
	 * @return	string	The day of the week.
	 */
	protected static function convertDayToString($day, $abbr = false)
	{
		switch ($day) {
			case 0: return $abbr ? CText::_('SUN') : CText::_('SUNDAY');
			case 1: return $abbr ? CText::_('MON') : CText::_('MONDAY');
			case 2: return $abbr ? CText::_('TUE') : CText::_('TUESDAY');
			case 3: return $abbr ? CText::_('WED') : CText::_('WEDNESDAY');
			case 4: return $abbr ? CText::_('THU') : CText::_('THURSDAY');
			case 5: return $abbr ? CText::_('FRI') : CText::_('FRIDAY');
			case 6: return $abbr ? CText::_('SAT') : CText::_('SATURDAY');
		}
	}

	/**
	 * Translates month number to a string.
	 *
	 * @param	integer	The numeric month of the year.
	 * @param	boolean	Return the abreviated month string?
	 * @return	string	The month of the year.
	 */
	protected static function convertMonthToString($month, $abbr = false)
	{
		switch ($month) {
			case 1:  return $abbr ? CText::_('JANUARY_SHORT')	: CText::_('JANUARY');
			case 2:  return $abbr ? CText::_('FEBRUARY_SHORT')	: CText::_('FEBRUARY');
			case 3:  return $abbr ? CText::_('MARCH_SHORT')		: CText::_('MARCH');
			case 4:  return $abbr ? CText::_('APRIL_SHORT')		: CText::_('APRIL');
			case 5:  return $abbr ? CText::_('MAY_SHORT')		: CText::_('MAY');
			case 6:  return $abbr ? CText::_('JUNE_SHORT')		: CText::_('JUNE');
			case 7:  return $abbr ? CText::_('JULY_SHORT')		: CText::_('JULY');
			case 8:  return $abbr ? CText::_('AUGUST_SHORT')	: CText::_('AUGUST');
			case 9:  return $abbr ? CText::_('SEPTEMBER_SHORT')	: CText::_('SEPTEMBER');
			case 10: return $abbr ? CText::_('OCTOBER_SHORT')	: CText::_('OCTOBER');
			case 11: return $abbr ? CText::_('NOVEMBER_SHORT')	: CText::_('NOVEMBER');
			case 12: return $abbr ? CText::_('DECEMBER_SHORT')	: CText::_('DECEMBER');
		}
	}

	/**
	 * Magic method to render the date object in the format specified in the public
	 * static member CDate::$format.
	 *
	 * @return	string	The date as a formatted string.
	 */
	public function __toString()
	{
		return $this->format($this->format);
	}

	/**
	 * Gets the date as UNIX timestamp
	 *
	 * @return	integer	The date as a UNIX timestamp.
	 */
	public function toUnix()
	{
		return $this->timestamp;
	}

	/**
	 * Get Unix timestamp with microseconds
	 *
	 * @return	float	The Unix timestamp with microseconds
	 */
	public function getMicrotime()
	{
		return $this->microtimestamp;
	}

	/**
	 * Gets the date as an ISO 8601 string.  IETF RFC 3339 defines the ISO 8601 format
	 * and it can be found at the IETF Web site.
	 *
	 * @link http://www.ietf.org/rfc/rfc3339.txt
	 *
	 * @param	boolean	True to return the date string in the local time zone, false to return it in GMT.
	 * @return	string	The date string in ISO 8601 format.
	 */
	/*public function toISO8601()
	{
		return $this->format(DateTime::RFC3339);
	}*/

	/**
	 * Gets the date as an MySQL datetime string.
	 *
	 * @link http://dev.mysql.com/doc/refman/5.0/en/datetime.html
	 *
	 * @param	boolean	True to return the date string in the local time zone, false to return it in GMT.
	 * @return	string	The date string in MySQL datetime format.
	 */
	/*public function toMySQL()
	{
		return $this->format('Y-m-d H:i:s');
	}*/

	/**
	 * Gets the date as an RFC 822 string.  IETF RFC 2822 supercedes RFC 822 and its definition
	 * can be found at the IETF Web site.
	 *
	 * @link http://www.ietf.org/rfc/rfc2822.txt
	 *
	 * @param	boolean	True to return the date string in the local time zone, false to return it in GMT.
	 * @return	string	The date string in RFC 822 format.
	 */
	/*public function toRFC822()
	{
		return $this->format(DateTime::RFC2822);
	}*/
}
