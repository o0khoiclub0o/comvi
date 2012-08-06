<?php
/**
 * JSON format handler for CRegistry.
 *
 * @package		Comvi.Framework
 * @subpackage	Registry
 */
class CRegistryFormatJSON extends CRegistryFormat
{
	/**
	 * Converts an object into a JSON formatted string.
	 *
	 * @param	object	Data source object.
	 * @param	array	Options used by the formatter.
	 * @return	string	JSON formatted string.
	 */
	public function objectToString($object, $options = array())
	{
		return json_encode($object);
	}

	/**
	 * Parse a JSON formatted string and convert it into an object.
	 *
	 * If the string is not in JSON format, this method will attempt to parse it as INI format.
	 *
	 * @param	string	JSON formatted string to convert.
	 * @param	array	Options used by the formatter.
	 * @return	object	Data object.
	 */
	public function stringToObject($data, $process_sections = false)
	{
		$data = trim($data);
		if ((substr($data, 0, 1) != '{') && (substr($data, -1, 1) != '}')) {
			$ini = CRegistryFormat::getInstance('INI');
			$obj = $ini->stringToObject($data, $process_sections);
		} else {
			$obj = json_decode($data);
		}
		return $obj;
	}
}
?>