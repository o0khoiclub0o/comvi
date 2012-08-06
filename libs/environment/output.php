<?php
/**
 * COutput Class.
 *
 * This class serves to provide the Comvi Framework with a common interface to access
 * response variables.  This includes header and body.
 *
 * @package		Comvi.Framework
 * @subpackage	Environment
 */
class COutput
{
	protected $headers = array();

	protected $body = array();

	protected $vars = array();

	protected $commpress = false;

	
	/**
	* Class constructor
	*
	* @access protected
	* @param	array	$options Associative array of options
	*/
	function __construct($options = array())
	{
		if (array_key_exists('commpress', $options)) {
			$this->commpress = $options['commpress'];
		}
	}

	/**
	 * Set a header.
	 *
	 * If $replace is true, replaces any headers already defined with that $name.
	 *
	 * @param	string	$name
	 * @param	string	$value
	 * @param	boolean	$replace
	 *
	 * @return	void
	 */
	public function setHeader($name, $value, $replace = false)
	{
		$name	= (string) $name;
		$value	= (string) $value;

		if ($replace) {
			foreach ($this->headers as $key => $header)
			{
				if ($name == $header['name']) {
					unset($this->headers[$key]);
				}
			}
		}

		$this->headers[] = array(
			'name'	=> $name,
			'value'	=> $value
		);
	}

	/**
	 * Send all headers.
	 *
	 * @return	void
	 */
	public function sendHeaders()
	{
		if (!headers_sent()) {
			foreach ($this->headers as $header)
			{
				if ('status' == strtolower($header['name'])) {
					// 'status' headers indicate an HTTP status, and need to be handled slightly differently
					header(ucfirst(strtolower($header['name'])) . ': ' . $header['value'], null, (int) $header['value']);
				}
				else {
					header($header['name'] . ': ' . $header['value']);
				}
			}
		}
	}

	/**
	 * Set body content.
	 *
	 * If body content already defined, this will replace it.
	 *
	 * @param	mixed	$content
	 *
	 * @return	void
	 */
	public function setBody($key, $content)
	{
		$this->body[$key] = $content;
	}

	/**
	 * Return the body content
	 *
	 * @param	string	name of component
	 *
	 * @return	string|Cview
	 */
	public function getBody($key)
	{
		return $this->body[$key];
	}

	/**
	 * Set body vars
	 *
	 * @param	string	name of component
	 *
	 * @return	mixed
	 */
	public function setVars($key, $value)
	{
		$this->vars[$key] = $value;
	}

	/**
	 * Return the body vars
	 *
	 * @param	string	name of component
	 *
	 * @return	mixed
	 */
	public function &getVars($key = null)
	{
		if ($key == null) {
			return $this->vars;
		}

		return $this->vars[$key];
	}

	/**
	 * Prepend content to the body content
	 *
	 * @param	mixed	$content
	 *
	 * @return	void
	 */
	/*public static function prependBody($content)
	{
		array_unshift($this->body, (string) $content);
	}*/

	/**
	 * Append content to the body content
	 *
	 * @param	string	$content
	 *
	 * @return	void
	 */
	/*public static function appendBody($content)
	{
		array_push($this->body, (string) $content);
	}*/

	/**
	 * Sends all headers prior to returning the string
	 *
	 * @param	boolean	$compress	If true, compress the data
	 *
	 * @return	string
	 */
	/*public static function toString($compress = false)
	{
		$data = self::getBody();

		// Don't compress something if the server is going todo it anyway. Waste of time.
		if ($compress && !ini_get('zlib.output_compression') && ini_get('output_handler')!='ob_gzhandler') {
			$data = self::compress($data);
		}

		self::sendHeaders();

		return $data;
	}*/

	/**
	 * Compress the data
	 *
	 * Checks the accept encoding of the browser and compresses the data before
	 * sending it to the client.
	 *
	 * @param	string	$data	data
	 *
	 * @return	string	compressed data
	 */
	protected static function compress($data)
	{
		$encoding = self::clientEncoding();

		if (!$encoding) {
			return $data;
		}

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
		}

		if (headers_sent()) {
			return $data;
		}

		if (connection_status() !== 0) {
			return $data;
		}


		$level = 4; // ideal level

		/*
		$size		= strlen($data);
		$crc		= crc32($data);

		$gzdata		= "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		$gzdata		.= gzcompress($data, $level);

		$gzdata	= substr($gzdata, 0, strlen($gzdata) - 4);
		$gzdata	.= pack("V",$crc) . pack("V", $size);
		*/

		$gzdata = gzencode($data, $level);

		self::setHeader('Content-Encoding', $encoding);
		self::setHeader('X-Content-Encoded-By', 'Comvi! 1.0');

		return $gzdata;
	}

	/**
	 * Check, whether client supports compressed data
	 *
	 * @return	boolean
	 */
	protected static function clientEncoding()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			return false;
		}

		$encoding = false;

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$encoding = 'gzip';
		}

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
			$encoding = 'x-gzip';
		}

		return $encoding;
	}
}
?>