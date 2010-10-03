<?php

/**
 * IMAGES ONLY
 * 
 * This will strip out everything except images from {ITEM_CONTENT}.
 * 
 * If you don't want to do anything to the content, simply remove the function.
 */

// We MUST keep this classname.
class SimplePie_PostProcess
{
	// Function name MUST be the same as the template tag we're processing, all lowercase, and MUST accept a single string parameter.
	function item_content($s)
	{
		// Match all images in the content.
		// I would recommend the regex tool at http://osteele.com/tools/rework/ to do testing ahead of time.
		preg_match_all('/<img([^>]*)>/i', $s, $matches);

		// Clear out the variable.
		$s = '';

		// Loop through all of the *complete* matches (stored in $matches[0]).
		foreach ($matches[0] as $match)
		{
			// Add the images (only) back to $s.
			$s .= $match . '<br />';
		}

		// Return $s back out to the plugin.
		return $s;
	}
}

?>