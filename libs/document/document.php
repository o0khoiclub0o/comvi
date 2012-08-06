<?php
/**
 * Document class, provides an easy interface to parse and display a document
 *
 * @abstract
 * @package		Comvi.Framework
 * @subpackage	Document
 */
class CDocument
{
	public $title;
	public $base;
	public $type;
	public $mime;
	public $charset		= 'utf-8';
	public $language		= 'en';
	public $direction	= 'ltr';
	public $tab			= "\11";
	public $lineEnd		= "\12";
	public $generator	= 'Comvi! 1.0 - Cloud Framework';
	public $mdate;


	/**
	* Class constructor
	*
	* @access protected
	* @param	array	$options Associative array of options
	*/
	function __construct($options = array())
	{
		if (array_key_exists('base', $options)) {
			$this->base = $options['base'];
		}

		if (array_key_exists('charset', $options)) {
			$this->charset = $options['charset'];
		}

		if (array_key_exists('language', $options)) {
			$this->language = $options['language'];
		}

		if (array_key_exists('direction', $options)) {
			$this->direction = $options['direction'];
		}

		if (array_key_exists('tab', $options)) {
			$this->tab = $options['tab'];
		}

		if (array_key_exists('lineend', $options)) {
			switch ($options['lineend']) {
				case 'win':
					$this->lineEnd = "\15\12";
					break;
				case 'unix':
					$this->lineEnd = "\12";
					break;
				case 'mac':
					$this->lineEnd = "\15";
					break;
				default:
					$this->lineEnd = $options['lineend'];
			}
		}
	}
}
?>