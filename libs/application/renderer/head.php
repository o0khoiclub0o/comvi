<?php
/**
 * CDocument head renderer
 *
 * @package		Comvi.Framework
 * @subpackage	Document
 */
class CRendererHead
{
	/**
	 * Renders the document head and returns the results as a string
	 *
	 * @return	string	The output of the script
	 */
	public function render()
	{
		$buffer = '';
		$document = CLoader::getDocument();
		
		// get line endings
		$lnEnd	= $document->get('lineEnd');
		$tab	= $document->get('tab');

		$buffer .= $tab.'<title>'.htmlspecialchars($document->get('title')).'</title>'.$lnEnd;

		$metaTags = $document->get('metaTags');
		// Generate META tags (needs to happen as early as possible in the head)
		foreach ($metaTags as $name => $content)
		{
			if (strtolower($name) === 'content-type') {
				$content.= '; charset=' . $document->get('charset');
				$buffer .= $tab.'<meta http-equiv="Content-Type" content="'.htmlspecialchars($content).'" />'.$lnEnd;
			}
			elseif ($name === 'content-language') {
				$buffer .= $tab.'<meta http-equiv="Content-Language" content="'.htmlspecialchars($content).'" />'.$lnEnd;
			}
			else {
				$buffer .= $tab.'<meta name="'.$name.'" content="'.htmlspecialchars($content).'" />'.$lnEnd;
			}
		}

		$base = $document->get('baseurl');
		// Generate base tag (need to happen first)
		if (!empty($base)) {
			$buffer .= $tab.'<base href="'.$base.'" />'.$lnEnd;
		}

		$styles = $document->get('styles');
		// Generate link declarations
		foreach ($styles as $style) {
			$buffer .= $tab.'<link rel="stylesheet" type="text/css" href="'.$style.'" />'.$lnEnd;
		}

		$scripts = $document->get('scripts');
		// Generate script file links
		foreach ($scripts AS $src) {
			$buffer .= $tab.'<script type="text/javascript" src="'.$src.'"></script>'.$lnEnd;
		}

		$scriptDatas = $document->get('scriptDatas');
		// Generate script declarations
		foreach ($scriptDatas as $content)
		{
			$buffer .= $tab.'<script type="text/javascript">'.$lnEnd;

			// This is for full XHTML support.
			//if ($document->_mime != 'text/html') {
			//	$buffer .= $tab.$tab.'<![CDATA['.$lnEnd;
			//}

			$buffer .= $content.$lnEnd;

			// See above note
			//if ($document->_mime != 'text/html') {
			//	$buffer .= $tab.$tab.']]>'.$lnEnd;
			//}
	
			$buffer .= $tab.'</script>'.$lnEnd;
		}

		//foreach($document->_custom as $custom) {
		//	$buffer .= $tab.$custom.$lnEnd;
		//}

		return $buffer;
	}
}
?>