<?php
/**
 * Query Element Class.
 *
 * @package		Comvi.Framework
 * @subpackage	Database
 */
class CDatabaseQueryElement
{
	/**
	 * @var		string	The name of the element.
	 */
	protected $_name = null;

	/**
	 * @var		array	An array of elements.
	 */
	protected $_elements = null;

	/**
	 * @var		string	Glue piece.
	 */
	protected $_glue = null;

	/**
	 * Constructor.
	 *
	 * @param	string	$name		The name of the element.
	 * @param	mixed	$elements	String or array.
	 * @param	string	$glue		The glue for elements.
	 *
	 * @return	CDatabaseQueryElement
	 */
	public function __construct($name, $elements, $glue = ',')
	{
		$this->_elements	= array();
		$this->_name		= $name;
		$this->_glue		= $glue;

		$this->append($elements);
	}

	/**
	 * Magic function to convert the query element to a string.
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return PHP_EOL.$this->_name.' '.implode($this->_glue, $this->_elements);
	}

	/**
	 * Appends element parts to the internal list.
	 *
	 * @param	mixed	String or array.
	 *
	 * @return	void
	 */
	public function append($elements)
	{
		if (is_array($elements)) {
			$this->_elements = array_unique(array_merge($this->_elements, $elements));
		}
		else {
			$this->_elements = array_unique(array_merge($this->_elements, array($elements)));
		}
	}
}
?>