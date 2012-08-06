<?php
CLoader::import('document.document');

/**
 * DocumentHTML class, provides an easy interface to parse and display an html document
 *
 * @package		Comvi.Framework
 * @subpackage	Document
 */

class CDocumentHTML extends CDocument
{
	/**
	 * Array of meta tags
	 *
	 * @var	array
	 */
	protected $meta_tags = array();

	/**
	 * Array of Header <link> tags
	 *
	 * @var		array
	 */
	protected $styles = array();

	/**
	 * Array of linked scripts
	 *
	 * @var		array
	 */
	protected $scripts = array();

	/**
	 * Array of scripts placed in the header
	 *
	 * @var  array
	 * @access	private
	 */
	protected $script_datas = array();

	public $baseurl;
	public $template;
	public $static_url;


	/**
	 * Class constructor
	 *
	 * @param	array	$options Associative array of options
	 */
	public function __construct($options = array())
	{
		// set document type
		$this->type = 'html';

		CLoader::import('environment.uri', 1);
		$this->baseurl	= CLoader::getRouter()->base();
		$this->template	= CLoader::getConfig()->get('template');

		if (array_key_exists('tab', $options)) {
			$this->tab = $options['tab'];
		}

		if (array_key_exists('static_url', $options)) {
			$this->static_url = $options['static_url'];
		}

		// set default mime type and document metadata (meta data syncs with mime type by default)
		$this->setMetaData('Content-Type', 'text/html');

		parent::__construct($options);
	}

	/**
	 * Sets or alters a meta tag.
	 *
	 * @param string	$name			Value of name or http-equiv tag
	 * @param string	$content		Value of the content tag
	 * @return void
	 * @access public
	 */
	function setMetaData($name, $content)
	{
		if (strtolower($name) == 'content-type') {
			// Syncing with HTTP-header
			$this->mime = $content;
		}

		$this->meta_tags[$name] = $content;
	}

	public function addScriptLink($src)
	{
		$this->scripts[] = $this->static_url.'/'.$src;
	}

	public function addStyleLink($src)
	{
		$this->styles[]	= $this->static_url.'/'.$src;
	}

	public function addScriptData($data)
	{
		$this->script_datas[] = $data;
	}
}
?>