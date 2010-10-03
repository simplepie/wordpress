<?php
/*
Plugin Name: SimplePie Plugin for WordPress
Version: 2.2.1
Plugin URI: http://simplepie.org/wiki/plugins/wordpress/simplepie_plugin_for_wordpress
Description: A fast and easy way to add RSS and Atom feeds to your WordPress blog. Go to <a href="options-general.php?page=simplepie_wordpress_2">Settings&rarr;SimplePie for WP</a> to adjust default settings.
Author: Ryan Parman
Author URI: http://simplepie.org/
*/


/*********************************************************************************/


/**
 * Define version for the plugin.
 */
define('SIMPLEPIE_PLUGIN', '2.2.1');

/**
 * Expected minimum SimplePie build number.
 */
define('EXPECTED_SIMPLEPIE_VERSION', '1.1.1');

/**
 * Expected minimum SimplePie build number.
 */
define('EXPECTED_SIMPLEPIE_BUILD', 20080315205903);

/**
 * WordPress version.
 */
define('WP_VERSION', get_bloginfo('version'));

/**
 * Web-accessible wp-content directory.
 */
define('WP_CONTENT_WEB', get_bloginfo('wpurl') . '/wp-content');

/**
 * Web-accessible control panel page.
 */
define('WP_CPANEL', get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=simplepie_wordpress_2');

/**
 * Set absolute SimplePie plugin directory.
 */
define('SIMPLEPIE_PLUGINDIR', dirname(__FILE__));

/**
 * Get only the name of the plugin directory.
 */
define('SIMPLEPIE_PLUGINDIR_NAME', pathinfo(dirname(__FILE__), PATHINFO_BASENAME));

/**
 * Web-accessible URL for the plugin directory.
 */
define('SIMPLEPIE_PLUGINDIR_WEB', WP_CONTENT_WEB . '/plugins/' . SIMPLEPIE_PLUGINDIR_NAME);

/**
 * Default cache directory.
 */
define('SIMPLEPIE_CACHEDIR', dirname(dirname(SIMPLEPIE_PLUGINDIR)) . '/cache');


/*********************************************************************************/


/**
 * Wrapper class for static functions
 */
class SimplePie_WordPress
{
	/**
	 * Shortens path names based on the location of /wp-content/.
	 */
	function clean_wp_path($path)
	{
		if ($wp_path = stristr($path, 'wp-content'))
		{
			return '[WP Install]/' . $wp_path;
		}
		else
		{
			return $path;
		}
	}

	/**
	 * Re-implement str_split() in PHP 4.x
	 * Written by dacmeaux at gmail dot com, posted to http://us3.php.net/str_split
	 * Modified by Ryan Parman, http://simplepie.org
	 */
	function str_split($text)
	{
		// If str_split() exists in PHP, use it.
		if (function_exists('str_split'))
		{
			return str_split($text);
		}

		// Otherwise, emulate it.
		else
		{
			$array = array();
			$text_len = strlen($text);
			for ($i = 0; $i < $text_len; $i++)
			{
				$key = NULL;
				for ($j = 0; $j < 1; $j++)
				{
					$key .= $text[$i];  
				}
				array_push($array, $key);
			}
			return $array;
		}
	}

	/**
	 * version_compare() is being stupid, so let's work around it.
	 */
	function convert_to_version($s)
	{
		$s = strval($s);
		$s = SimplePie_WordPress::str_split($s);
		$s = implode('.', $s);
		return $s;
	}

	/**
	 * Get a list of files from a given directory.
	 */
	function get_files($dir, $extension)
	{
		$temp = array();

		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if (!is_dir($file))
					{
						// Determine location of the file
						$location = $dir . $file;

						// Determine label for the template file
						$label = explode($extension, $file);
						$label = str_replace('_', ' ', $label[0]);
						$label = str_replace('-', ' ', $label);
						$label = str_replace('.', ' ', $label);
						$label = ucwords($label);

						// Add them to the array.
						$temp[] = array('location' => $location, 'label' => $label);
					}
				}
				closedir($dh);
			}
		}

		return $temp;
	}

	/**
	 * Handles the post-processing of data.
	 */
	function post_process($swap, $s)
	{
		if (class_exists('SimplePie_PostProcess'))
		{
			$swap = strtolower($swap);
			$post = new SimplePie_PostProcess;

			if (method_exists($post, $swap))
			{
				$s = SimplePie_PostProcess::$swap($s);
			}
		}

		return $s;
	}

	/**
	 * Delete all related values in the database.
	 */
	function delete_values()
	{
		// General settings
		delete_option('simplepie_template');
		delete_option('simplepie_items');
		delete_option('simplepie_items_per_feed');
		delete_option('simplepie_date_format');
		delete_option('simplepie_enable_cache');
		delete_option('simplepie_set_cache_location');
		delete_option('simplepie_set_cache_duration');
		delete_option('simplepie_enable_order_by_date');
		delete_option('simplepie_set_timeout');

		// Text-shortening settings
		delete_option('simplepie_truncate_feed_title');
		delete_option('simplepie_truncate_feed_description');
		delete_option('simplepie_truncate_item_title');
		delete_option('simplepie_truncate_item_description');

		// Advanced settings
		delete_option('simplepie_processing');
		delete_option('simplepie_locale');
		delete_option('simplepie_local_date_format');
		delete_option('simplepie_strip_htmltags');
		delete_option('simplepie_strip_attributes');
		delete_option('simplepie_set_max_checked_feeds');
		delete_option('simplepie_plugin_installed');
	}
}


/**
 * Set if this we're asked to reset the settings.
 */
if (isset($_POST['reset_all_settings']))
{
	SimplePie_WordPress::delete_values();
}

/**
 * Set default options if they're not already set.
 */
if (!get_option('simplepie_template'))
{
	update_option('simplepie_template', SIMPLEPIE_PLUGINDIR . '/templates/default.tmpl', 'The template to use for displaying feeds.');
}

if (!get_option('simplepie_items'))
{
	update_option('simplepie_items', 0, 'The number of feed items to display by default.');
}

if (!get_option('simplepie_items_per_feed'))
{
	update_option('simplepie_items_per_feed', 0, 'The number of feed items to display per feed when using Multifeeds.');
}

if (!get_option('simplepie_date_format'))
{
	update_option('simplepie_date_format', 'l, j F Y, g:i a', 'The default format for English dates.');
}

if (!get_option('simplepie_enable_cache'))
{
	update_option('simplepie_enable_cache', 1, 'Whether the feeds should be cached or not.');
}

if (!get_option('simplepie_set_cache_location'))
{
	update_option('simplepie_set_cache_location', SIMPLEPIE_CACHEDIR, 'The file system location for the cache.');
}

if (!get_option('simplepie_set_cache_duration'))
{
	update_option('simplepie_set_cache_duration', 3600, 'The number of seconds that feed data should be cached for before asking the feed if it\'s been changed.');
}

if (!get_option('simplepie_enable_order_by_date'))
{
	update_option('simplepie_enable_order_by_date', 1, 'Feeds items aren\'t always in chronological order. This fixes that.');
}

if (!get_option('simplepie_set_timeout'))
{
	update_option('simplepie_set_timeout', 10, 'Number of seconds to wait for a remote feed to respond before giving up.');
}

if (!get_option('simplepie_truncate_feed_title'))
{
	update_option('simplepie_truncate_feed_title', 0, 'Number of characters to shorten the text to.');
}

if (!get_option('simplepie_truncate_feed_description'))
{
	update_option('simplepie_truncate_feed_description', 200, 'Number of characters to shorten the text to.');
}

if (!get_option('simplepie_truncate_item_title'))
{
	update_option('simplepie_truncate_item_title', 0, 'Number of characters to shorten the text to.');
}

if (!get_option('simplepie_truncate_item_description'))
{
	update_option('simplepie_truncate_item_description', 200, 'Number of characters to shorten the text to.');
}

if (!get_option('simplepie_processing'))
{
	update_option('simplepie_processing', SIMPLEPIE_PLUGINDIR . '/processing/none.php', 'The rules to use for post-processing feeds.');
}

if (!get_option('simplepie_locale'))
{
	update_option('simplepie_locale', 'auto', 'The locale for the website.');
}

if (!get_option('simplepie_local_date_format'))
{
	update_option('simplepie_local_date_format', '%A, %e %B %Y, %H:%M', 'The default format for localized dates.');
}

if (!get_option('simplepie_strip_htmltags'))
{
	update_option('simplepie_strip_htmltags', 'base blink body doctype embed font form frame frameset html iframe input marquee meta noscript object param script style', 'The HTML tags to be stripped by default.');
}

if (!get_option('simplepie_strip_attributes'))
{
	update_option('simplepie_strip_attributes', 'bgsound class expr id style onclick onerror onfinish onmouseover onmouseout onfocus onblur lowsrc dynsrc', 'The HTML attributes to be stripped by default.');
}

if (!get_option('simplepie_set_max_checked_feeds'))
{
	update_option('simplepie_set_max_checked_feeds', 10, 'The number of links in the document to check for feeds.');
}


/*********************************************************************************/


/**
 * Add menu item to Options menu.
 */
function simplepie_options()
{
	if (function_exists('add_options_page'))
	{
		add_options_page('SimplePie for WP', 'SimplePie for WP', 8, 'simplepie_wordpress_2', 'simplepie_options_page');
	}
}

/**
 * Trigger the adding of the menu option.
 */
add_action('admin_menu', 'simplepie_options');

/**
 * Draw normal options page.
 */
function simplepie_options_page()
{
	if (isset($_POST['reset_all_settings']) && !empty($_POST['reset_all_settings']))
	{
		// Display the reset message.
		echo '<div id="message" class="updated fade"><p><strong>All of your options for this plugin have been reset to their default values.</strong></p></div>';
	}
	else if (isset($_POST['submitted']) && !empty($_POST['submitted']))
	{
		/**
		 * Get form data
		 */

		// General settings
		$simplepie_template = (string) $_POST['simplepie_template'];
		$simplepie_items = (integer) $_POST['simplepie_items'];
		$simplepie_items_per_feed = (integer) $_POST['simplepie_items_per_feed'];
		$simplepie_date_format = (string) $_POST['simplepie_date_format'];
		$simplepie_enable_cache = (bool) $_POST['simplepie_enable_cache'];
		$simplepie_set_cache_location = (string) $_POST['simplepie_set_cache_location'];
		$simplepie_set_cache_duration = (integer) $_POST['simplepie_set_cache_duration'];
		$simplepie_enable_order_by_date = (bool) $_POST['simplepie_enable_order_by_date'];
		$simplepie_set_timeout = (integer) $_POST['simplepie_set_timeout'];

		// Text-shortening settings
		$simplepie_truncate_feed_title = (integer) $_POST['simplepie_truncate_feed_title'];
		$simplepie_truncate_feed_description = (integer) $_POST['simplepie_truncate_feed_description'];
		$simplepie_truncate_item_title = (integer) $_POST['simplepie_truncate_item_title'];
		$simplepie_truncate_item_description = (integer) $_POST['simplepie_truncate_item_description'];

		// Advanced settings
		$simplepie_processing = (string) $_POST['simplepie_processing'];
		$simplepie_locale = (string) $_POST['simplepie_locale'];
		$simplepie_local_date_format = (string) $_POST['simplepie_local_date_format'];
		$simplepie_strip_htmltags = (string) $_POST['simplepie_strip_htmltags'];
		$simplepie_strip_attributes = (string) $_POST['simplepie_strip_attributes'];
		$simplepie_set_max_checked_feeds = (integer) $_POST['simplepie_set_max_checked_feeds'];

		/**
		 * Update plugin options
		 */

		// General settings
		update_option("simplepie_template", $simplepie_template);
		update_option("simplepie_items", $simplepie_items);
		update_option("simplepie_items_per_feed", $simplepie_items_per_feed);
		update_option("simplepie_date_format", $simplepie_date_format);
		update_option("simplepie_enable_cache", $simplepie_enable_cache);
		update_option("simplepie_set_cache_location", $simplepie_set_cache_location);
		update_option("simplepie_set_cache_duration", $simplepie_set_cache_duration);
		update_option("simplepie_enable_order_by_date", $simplepie_enable_order_by_date);
		update_option("simplepie_set_timeout", $simplepie_set_timeout);

		// Text-shortening settings
		update_option("simplepie_truncate_feed_title", $simplepie_truncate_feed_title);
		update_option("simplepie_truncate_feed_description", $simplepie_truncate_feed_description);
		update_option("simplepie_truncate_item_title", $simplepie_truncate_item_title);
		update_option("simplepie_truncate_item_description", $simplepie_truncate_item_description);

		// Advanced settings
		update_option("simplepie_processing", $simplepie_processing);
		update_option("simplepie_locale", $simplepie_locale);
		update_option("simplepie_local_date_format", $simplepie_local_date_format);
		update_option("simplepie_strip_htmltags", $simplepie_strip_htmltags);
		update_option("simplepie_strip_attributes", $simplepie_strip_attributes);
		update_option("simplepie_set_max_checked_feeds", $simplepie_set_max_checked_feeds);

		// Display the updated message.
		echo '<div id="message" class="updated fade"><p><strong>Your options were saved successfully!</strong></p></div>';
	}

	if (get_option('simplepie_locale') && get_option('simplepie_locale') != 'auto')
	{
		setlocale(LC_TIME, get_option('simplepie_locale'));
	}
?>
	<style type="text/css">
	.submit input.warning {
		background-color:#c00;
		background-image:none;
		border-color:#f00 #900 #900 #f00;
		color:#fff;
	}

	.submit {
		text-align:right;
		background-color:#e9f5ff;
		margin:0;
		padding:15px;
	}

	.submit input {
		font-size:115%;
		padding:5px 10px;
		border:1px solid #999;
	}

	.warning {
		background-color:#c00;
		color:#fff;
		padding:1px 2px;
	}
	
	div.wrap h3 {
		font-size:20px;
		padding-top:10px;
		border-bottom:1px solid #000;
	}
	
	code {
		color:#000080;
		background-color:#eef;
		font-size:0.95em;
	}

	input.text {
		background-color:#e9f5ff;
		color:#333;
		border:1px solid #ccc;
	}

	input.text:focus {
		background-color:#ffc;
		color:#000;
		border:1px solid #f90;
	}

	input#simplepie_set_cache_location {
		width:90%;
	}

	td.break,
	td.break-noborder {
		padding:15px 0;
	}

	td.break {
		border-bottom:1px solid #999;
	}

	.footnote,
	.footnote a {
		font-size:12px;
		line-height:1.3em;
		color:#aaa;
	}

	.footnote em {
		background-color:transparent;
		font-style:italic;
	}

	.footnote code {
		background-color:transparent;
		font:11px/14px monospace;
		color:#fff;
		background-color:#ccc;
		padding:0 1px;
	}
	</style>

	<form method="post" action="" name="simplepie" onsubmit="document.forms['simplepie'].simplepie_set_cache_duration.value = parseFloat(document.forms['simplepie'].simplepie_set_cache_duration.value) * parseInt(document.forms['simplepie'].simplepie_cache_duration_units[document.forms['simplepie'].simplepie_cache_duration_units.selectedIndex].value);document.forms['simplepie'].simplepie_cache_duration_units.selectedIndex=0;">
	<?php wp_nonce_field('update-options') ?>

	<div class="wrap">
		<h2>SimplePie Plugin for WordPress</h2>

		<h3>Installation Status</h3>
		<p>This information will help with debugging in case something goes wrong.</p>

		<fieldset class="options">
			<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform">
				<tr>
					<th width="33%" scope="row" valign="top">Version of WordPress:</th>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/ok.png" /> <?php echo WP_VERSION; ?></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top">Version of <a href="http://wordpress.org/extend/plugins/simplepie-plugin-for-wordpress/">SimplePie Plugin for WordPress</a>:</th>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/ok.png" /> <?php echo SIMPLEPIE_PLUGIN; ?></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top">Version of <a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a>:</th>
					<?php if ($e = version_compare(SimplePie_WordPress::convert_to_version(SIMPLEPIE_BUILD), SimplePie_WordPress::convert_to_version(EXPECTED_SIMPLEPIE_BUILD)) > -1): ?>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/ok.png" /> <?php echo SIMPLEPIE_VERSION; ?> (<a href="options-general.php?page=simplepie_core">Details</a>)</td>
					<?php elseif (!defined('SIMPLEPIE_BUILD')): ?>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/error.png" /> <span class="warning">None</span> &mdash; Please download and install <a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a>.<p class="footnote">This plugin requires <a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a> (version <?php echo EXPECTED_SIMPLEPIE_VERSION; ?>) to be installed. Check out the <a href="http://codex.wordpress.org/Managing_Plugins#Installing_Plugins">"Installing Plugins"</a> documentation at the WordPress site for help.</p></td>
					<?php else: ?>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/error.png" /> <span class="warning"><?php echo SIMPLEPIE_VERSION; ?></span> &mdash; This version is out-of-date. Please update <a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a> to the latest version.<p class="footnote"><a href="http://wordpress.org/extend/plugins/simplepie-core/">SimplePie Core</a> (version <?php echo EXPECTED_SIMPLEPIE_VERSION; ?> or newer) is required. If you already have the latest version, you might have a situation where another plugin has bundled SimplePie and that plugin has loaded before SimplePie Core, causing strange things to happen. If so, try either disabling the other plugin or checking for an updated version that has been updated to utilize SimplePie Core for best compatibility.</p></td>
					<?php endif; ?>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top">Plugin install location:</th>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/ok.png" /> <code><?php echo SimplePie_WordPress::clean_wp_path(SIMPLEPIE_PLUGINDIR); ?>/</code>.</td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top">Cache directory writable?:</th>
					<?php if (!get_option('simplepie_enable_cache')): ?>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/error.png" /> <span class="warning">Cache disabled!</span>
						<p class="footnote">You have chosen to disable caching. Be aware that this will negatively impact performance.</p></td>
					<?php elseif (!is_dir(get_option('simplepie_set_cache_location'))): ?>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/error.png" /> <span class="warning">Cache directory does not exist!</span>
						<p class="footnote">Please either create a <a href="http://simplepie.org/wiki/faq/file_permissions">writable</a> cache directory at <code><?php echo get_option('simplepie_set_cache_location'); ?></code>, or change the preferred location below.</p></td>
					<?php elseif (!is_writable(get_option('simplepie_set_cache_location'))): ?>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/error.png" /> <span class="warning">Cache directory not writable!</span>
						<p class="footnote">Please make sure that the cache directory at <code><?php echo get_option('simplepie_set_cache_location'); ?></code> is <a href="http://simplepie.org/wiki/faq/file_permissions">writable by the server</a>, or change the preferred location below.</p></td>
					<?php else: ?>
					<td valign="top"><img src="<?php echo SIMPLEPIE_PLUGINDIR_WEB; ?>/images/ok.png" /> <code><?php echo SimplePie_WordPress::clean_wp_path(get_option('simplepie_set_cache_location')); ?></code> exists and is writable.</td>
					<?php endif; ?>
				</tr>

			</table>
		</fieldset>

		<h3>General Settings</h3>
		<p>Most people should feel comfortable tweaking and modifying these settings. These settings are the defaults for SimplePie, but you can override any individual feed instance on the fly by passing additional parameters to it.  See the <a href="http://simplepie.org/wiki/plugins/wordpress/simplepie_plugin_for_wordpress">documentation</a> to learn how to do this.</p>

		<fieldset class="options">
			<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform">
				<tr>
					<th width="33%" scope="row" valign="top"><h4>Layout template:</h4></th>
					<td class="break"><select name="simplepie_template">
						<?php
						$templates = SimplePie_WordPress::get_files(SIMPLEPIE_PLUGINDIR . '/templates/', '.tmpl');
						sort($templates);

						foreach($templates as $template)
						{
							echo '<option value="' . $template['location'] . '">' . $template['label'] . '</option>' . "\n";
						}
						?>
					</select>
						<p class="footnote">Add or edit templates in the following directory:<br /><code><?php echo SimplePie_WordPress::clean_wp_path(SIMPLEPIE_PLUGINDIR); ?>/templates/</code></p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top"><h4>Number of items to display:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_items" value="<?php echo get_option('simplepie_items'); ?>" size="3" maxlength="3" /> items
						<p class="footnote">By default, how many feed items (i.e. posts) to display. Use <code>0</code> for ALL.</p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top"><h4>Number of items to display per feed:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_items_per_feed" value="<?php echo get_option('simplepie_items_per_feed'); ?>" size="3" maxlength="3" /> items
						<p class="footnote">Limit the number of items (i.e. posts) to use per-feed when merging multiple feeds. Use <code>0</code> for ALL.</p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top"><h4>Default format for English dates:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_date_format" value="<?php echo get_option('simplepie_date_format'); ?>" /> (i.e. <?php echo date(get_option('simplepie_date_format')); ?>)
						<p class="footnote">Supports any format supported by PHP's <a href="http://php.net/date">date()</a> function. Only used with <code>{ITEM_DATE}</code> and <code>{ITEM_DATE_UTC}</code>.</p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top"><h4>Should we use caching:</h4></th>
					<td class="break"><select name="simplepie_enable_cache" onchange="simplepie_color_temp=document.body.style.color;if(document.forms['simplepie'].simplepie_enable_cache.value=='false'){document.forms['simplepie'].simplepie_set_cache_duration.disabled=true;document.forms['simplepie'].simplepie_cache_duration_units.disabled=true;document.getElementById('set_cache_duration_title').style.color='#cccccc';}else{document.forms['simplepie'].simplepie_set_cache_duration.disabled=false;document.forms['simplepie'].simplepie_cache_duration_units.disabled=false;document.getElementById('set_cache_duration_title').style.color=simplepie_color_temp;}">
							<option value="1">Yes (Recommended)</option>
							<option value="0">No</option>
						</select>
						<p class="footnote">Disabling cache will negatively impact performance (and anger feed creators), but will ensure that the very freshest version of the feed is displayed at all times.</p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top"><h4>Cache storage location:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_set_cache_location" id="simplepie_set_cache_location" value="<?php echo get_option('simplepie_set_cache_location'); ?>" />
						<p class="footnote">This should be a complete, writable file system location. Default value is auto-detected, but is not always correct for all WordPress installations. Adjust only if cache isn't working.</p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" id="set_cache_duration_title" valign="top"><h4>How long should we cache for?:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_set_cache_duration" value="<?php echo get_option('simplepie_set_cache_duration'); ?>" size="10" />
						<select name="simplepie_cache_duration_units">
							<option value="1">Seconds</option>
							<option value="60">Minutes</option>
							<option value="3600">Hours</option>
							<option value="87840">Days</option>
						</select>
							<p class="footnote">How long before we ask the feed if it's been updated? Recommend 1 hour (3600 seconds).</p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top"><h4>Re-order items by date:</h4></th>
					<td class="break"><select name="simplepie_enable_order_by_date">
							<option value="1">Yes (Recommended)</option>
							<option value="0">No</option>
						</select>
							<p class="footnote">Some feeds have items that are not chronologically ordered. This fixes that.</p></td>
				</tr>

				<tr>
					<th width="33%" scope="row" valign="top"><h4>Seconds to wait while fetching data:</h4></th>
					<td class="break-noborder"><input type="text" class="text" name="simplepie_set_timeout" value="<?php echo get_option('simplepie_set_timeout'); ?>" size="3" maxlength="3" /> seconds
						<p class="footnote">Some feeds are on slow servers, so increasing this time allows more time to fetch the feed.</p></td>
				</tr>

			</table>
			<p class="submit"><input type="submit" name="submitted" value="<?php _e('Update Options »') ?>" /></p>
		</fieldset>

		<h3>Text-Shortening Settings</h3>
		<p>Most people should feel comfortable tweaking and modifying these settings. These settings allow you to set default lengths for truncated text, but you can override any individual feed instance on the fly by passing additional parameters to it.  See the <a href="http://simplepie.org/wiki/plugins/wordpress/simplepie_plugin_for_wordpress">documentation</a> to learn how to do this.</p>
		<p>These settings only apply to the <code>TRUNCATE_*</code> template tags: <code>{TRUNCATE_FEED_DESCRIPTION}</code>, <code>{TRUNCATE_ITEM_PARENT_DESCRIPTION}</code>, <code>{TRUNCATE_FEED_TITLE}</code>, <code>{TRUNCATE_ITEM_PARENT_TITLE}</code>, <code>{TRUNCATE_ITEM_DESCRIPTION}</code>, and <code>{TRUNCATE_ITEM_TITLE}</code>.</p>

		<fieldset class="options">
			<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform">
				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Length for shortened feed titles:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_truncate_feed_title" value="<?php echo get_option('simplepie_truncate_feed_title'); ?>" size="5" maxlength="5" /> characters
						<p class="footnote">Strips HTML, linebreaks, and converts entities first. Set <code>0</code> to clean (but not shorten) the text.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Length for shortened feed descriptions:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_truncate_feed_description" value="<?php echo get_option('simplepie_truncate_feed_description'); ?>" size="5" maxlength="5" /> characters
						<p class="footnote">Strips HTML, linebreaks, and converts entities first. Set <code>0</code> to clean (but not shorten) the text.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Length for shortened item titles:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_truncate_item_title" value="<?php echo get_option('simplepie_truncate_item_title'); ?>" size="5" maxlength="5" /> characters
						<p class="footnote">Strips HTML, linebreaks, and converts entities first. Set <code>0</code> to clean (but not shorten) the text.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Length for shortened item descriptions:</h4></th>
					<td class="break-noborder"><input type="text" class="text" name="simplepie_truncate_item_description" value="<?php echo get_option('simplepie_truncate_item_description'); ?>" size="5" maxlength="5" /> characters
						<p class="footnote">Strips HTML, linebreaks, and converts entities first. Set <code>0</code> to clean (but not shorten) the text.</p></td>
				</tr>

			</table>
			<p class="submit"><input type="submit" name="submitted" value="<?php _e('Update Options »') ?>" /></p>
		</fieldset>

		<h3>Advanced Settings</h3>
		<p>These settings should only be modified if you know what you're doing.</p>

		<fieldset class="options">
			<table width="100%" cellspacing="2" cellpadding="5" class="optiontable editform">
				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Content Post-Processing Rules:</h4></th>
					<td class="break"><select name="simplepie_processing">
						<?php
						$processes = SimplePie_WordPress::get_files(SIMPLEPIE_PLUGINDIR . '/processing/', '.php');
						sort($processes);

						foreach($processes as $process)
						{
							echo '<option value="' . $process['location'] . '">' . $process['label'] . '</option>' . "\n";
						}
						?>
					</select>
						<p class="footnote">Add or edit processing rules in the following directory:<br /><code><?php echo SimplePie_WordPress::clean_wp_path(SIMPLEPIE_PLUGINDIR); ?>/processing/</code>.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Locale for the website:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_locale" value="<?php echo get_option('simplepie_locale'); ?>" />
						<p class="footnote">Switch the locale for the website to use. There are a variety of formats that can be used, depending on your server setup. See PHP's <a href="http://php.net/setlocale">setlocale()</a> function. Use <code>auto</code> to use the server's default.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Default format for localized dates:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_local_date_format" value="<?php echo get_option('simplepie_local_date_format'); ?>" /> (i.e. <?php echo strftime(get_option('simplepie_local_date_format')); ?>)
						<p class="footnote">Supports any format supported by PHP's <a href="http://php.net/strftime">strftime()</a> function. Only used with <code>{ITEM_LOCAL_DATE}</code> and <code>{ITEM_LOCAL_DATE_UTC}</code>.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>HTML tags to strip out of feeds:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_strip_htmltags" value="<?php echo get_option('simplepie_strip_htmltags'); ?>" size="50" /> (space separated)
						<p class="footnote">These tags will be stripped from the output. By default, removes potentially dangerous tags as well as some deprecated ones.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>HTML attributes to strip out of feeds:</h4></th>
					<td class="break"><input type="text" class="text" name="simplepie_strip_attributes" value="<?php echo get_option('simplepie_strip_attributes'); ?>" size="50" /> (space separated)
						<p class="footnote">These attributes will be stripped from the output. By default, removes potentially dangerous attributes as well as some deprecated ones.</p></td>
				</tr>

				<tr valign="top">
					<th width="33%" scope="row" valign="top"><h4>Number of links to check during auto-discovery:</h4></th>
					<td class="break-noborder"><input type="text" class="text" name="simplepie_set_max_checked_feeds" value="<?php echo get_option('simplepie_set_max_checked_feeds'); ?>" size="3" maxlength="3" /> links
						<p class="footnote">SimplePie's ultra-liberal feed locator satisfies points 1&ndash;6 of Mark Pilgrim's <a href="http://diveintomark.org/archives/2002/08/15/ultraliberal_rss_locator">feed usability rant</a>. This tells how many links to check.</p></td>
				</tr>

			</table>
			<p class="submit"><input type="submit" name="submitted" value="<?php _e('Update Options »') ?>" /></p>
		</fieldset>
	</div>
	</form>

	<br /><br /><br /><br /><br />
	
	<form method="post" action="" name="simplepie">
	<?php wp_nonce_field('update-options') ?>
	<div class="wrap">
		<h2>Reset ALL Settings</h2>
		<p>Wipe out ALL custom settings (except for changes or additions to templates and post-processing files), and reset everything back to their default values.</p>
		<fieldset class="options">
			<input type="hidden" name="reset_all_settings" value="true" />
			<p class="submit"><input type="submit" name="submitted" class="warning" value="Reset ALL settings for this plugin to their default values!" /></p>
		</fieldset>
	</div>
	</form>


	<script type="text/javascript" charset="utf-8">
	// Set the correct value for simplepie_template
	for (var x = 0; x < document.forms['simplepie'].simplepie_template.length; x++) {
		if (document.forms['simplepie'].simplepie_template[x].value == "<?php echo get_option('simplepie_template'); ?>") {
			document.forms['simplepie'].simplepie_template.selectedIndex = x;
			break;
		}
	}

	// Set the correct value for simplepie_enable_cache
	for (var x = 0; x < document.forms['simplepie'].simplepie_enable_cache.length; x++) {
		if (document.forms['simplepie'].simplepie_enable_cache[x].value == "<?php echo get_option('simplepie_enable_cache'); ?>") {
			document.forms['simplepie'].simplepie_enable_cache.selectedIndex = x;
			break;
		}
	}

	// Set the correct value for simplepie_enable_order_by_date
	for (var x = 0; x < document.forms['simplepie'].simplepie_enable_order_by_date.length; x++) {
		if (document.forms['simplepie'].simplepie_enable_order_by_date[x].value == "<?php echo get_option('simplepie_enable_order_by_date'); ?>") {
			document.forms['simplepie'].simplepie_enable_order_by_date.selectedIndex = x;
			break;
		}
	}

	// Set the correct value for simplepie_processing
	for (var x = 0; x < document.forms['simplepie'].simplepie_processing.length; x++) {
		if (document.forms['simplepie'].simplepie_processing[x].value == "<?php echo get_option('simplepie_processing'); ?>") {
			document.forms['simplepie'].simplepie_processing.selectedIndex = x;
			break;
		}
	}

	</script>

<?php
}

/**
 * Function that truncates text.
 */
function SimplePie_Truncate($s, $length = 0)
{
	// Strip out HTML tags.
    $s = strip_tags($s);

	// Strip out superfluous whitespace and line breaks.
    $s = preg_replace('/(\s+)/', ' ', $s);

	// Avoid PHP 4.x bug. Only do this if we're on PHP5. http://bugs.php.net/25670
	if (SIMPLEPIE_PHP5)
	{
		// Convert all HTML entities to their character counterparts.
		$s = html_entity_decode($s, ENT_QUOTES, 'UTF-8');
	}

	// Shorten the string to the number of characters requested, and strip wrapping whitespace.
	if ($length > 0 && strlen($s) > $length)
	{
		$s = trim(substr($s, 0, $length)) . '. [&hellip;]';
	}

	// Return the value.
    return $s;
}

/**
 * Take the namespaced function and make it global so that it can be called by usort().
 */
function sort_items($a, $b)
{
	return SimplePie::sort_items($a, $b);
}

/**
 * The actual function that can be called on webpages.
 */
function SimplePieWP($feed_url, $options = null)
{
	// Quit if the SimplePie class isn't loaded.
	if (!class_exists('SimplePie'))
	{
		die('<p style="font-size:16px; line-height:1.5em; background-color:#c00; color:#fff; padding:10px; border:3px solid #f00; text-align:left;"><img src="' . SIMPLEPIE_PLUGINDIR_WEB . '/images/error.png" /> There is a problem with the SimplePie Plugin for WordPress. Check your <a href="' . WP_CPANEL . '" style="color:#ff0; text-decoration:underline;">Installation Status</a> for more information.</p>');
	}

	if (isset($locale) && !empty($locale) && $locale != 'auto')
	{
		setlocale(LC_TIME, $locale);
	}

	// Default general settings
	$template = get_option('simplepie_template');
	$items = get_option('simplepie_items');
	$items_per_feed = get_option('simplepie_items_per_feed');
	$date_format = get_option('simplepie_date_format');
	$enable_cache = get_option('simplepie_enable_cache');
	$set_cache_location = get_option('simplepie_set_cache_location');
	$set_cache_duration = get_option('simplepie_set_cache_duration');
	$enable_order_by_date = get_option('simplepie_enable_order_by_date');
	$set_timeout = get_option('simplepie_set_timeout');

	// Default text-shortening settings
	$truncate_feed_title = get_option('simplepie_truncate_feed_title');
	$truncate_feed_description = get_option('simplepie_truncate_feed_description');
	$truncate_item_title = get_option('simplepie_truncate_item_title');
	$truncate_item_description = get_option('simplepie_truncate_item_description');

	// Default advanced settings
	$processing = get_option('simplepie_processing');
	$locale = get_option('simplepie_locale');
	$local_date_format = get_option('simplepie_local_date_format');
	$strip_htmltags = get_option('simplepie_strip_htmltags');
	$strip_attributes = get_option('simplepie_strip_attributes');
	$set_max_checked_feeds = get_option('simplepie_set_max_checked_feeds');

	// Overridden settings
	if ($options)
	{
		// Fix the template location if one was passed in.
		if (isset($options['template']) && !empty($options['template']))
		{
			$options['template'] = SIMPLEPIE_PLUGINDIR . '/templates/' . strtolower(str_replace(' ', '_', $options['template'])) . '.tmpl';
		}

		// Fix the processing location if one was passed in.
		if (isset($options['processing']) && !empty($options['processing']))
		{
			$options['processing'] = SIMPLEPIE_PLUGINDIR . '/processing/' . strtolower(str_replace(' ', '_', $options['processing'])) . '.php';
		}

		extract($options);
	}

	// Load post-processing file.
	if ($processing && $processing != '')
	{
		include_once($processing);
	}

	// If template doesn't exist, die.
	if (!file_exists($template) || !is_readable($template))
	{
		die('<p style="font-size:16px; line-height:1.5em; background-color:#c00; color:#fff; padding:10px; border:3px solid #f00; text-align:left;"><img src="' . SIMPLEPIE_PLUGINDIR_WEB . '/images/error.png" /> The SimplePie template file is not readable by WordPress. Check the <a href="' . WP_CPANEL . '" style="color:#ff0; text-decoration:underline;">WordPress Control Panel</a> for more information.</p>');
	}

	// Initialize SimplePie
	$feed = new SimplePie();
	$feed->set_feed_url($feed_url);
	$feed->enable_cache($enable_cache);
	$feed->set_item_limit($items_per_feed);
	$feed->set_cache_location($set_cache_location);
	$feed->set_cache_duration($set_cache_duration);
	$feed->enable_order_by_date($enable_order_by_date);
	$feed->set_timeout($set_timeout);
	$feed->strip_htmltags(explode(' ', $strip_htmltags));
	$feed->strip_attributes(explode(' ', $strip_attributes));
	$feed->set_max_checked_feeds($set_max_checked_feeds);
	$feed->init();

	// Load up the selected template file
	$handle = fopen($template, 'r');
	$tmpl = fread($handle, filesize($template));
	fclose($handle);

	/**************************************************************************************************************/
	// ERRORS
	// I'm absolutely sure that there is a better way to do this.

	// Define what we're looking for
	$error_start_tag = '{IF_ERROR_BEGIN}';
	$error_end_tag = '{IF_ERROR_END}';
	$error_start_length = strlen($error_start_tag);
	$error_end_length = strlen($error_end_tag);

	// Find what we're looking for
	$error_start_pos = strpos($tmpl, $error_start_tag);
	$error_end_pos = strpos($tmpl, $error_end_tag);
	$error_length_pos = $error_end_pos - $error_start_pos;

	// Grab what we're looking for
	$error_string = substr($tmpl, $error_start_pos + $error_start_length, $error_length_pos - $error_start_length);
	$replacable_string = $error_start_tag . $error_string . $error_end_tag;

	if ($error_message = $feed->error())
	{
		$tmpl = str_replace($replacable_string, $error_string, $tmpl);
		$tmpl = str_replace('{ERROR_MESSAGE}', SimplePie_WordPress::post_process('ERROR_MESSAGE', $error_message), $tmpl);
	}
	elseif ($feed->get_item_quantity() == 0)
	{
		$tmpl = str_replace($replacable_string, $error_string, $tmpl);
		$tmpl = str_replace('{ERROR_MESSAGE}', SimplePie_WordPress::post_process('ERROR_MESSAGE', 'There are no items in this feed.'), $tmpl);
	}
	else
	{
		$tmpl = str_replace($replacable_string, '', $tmpl);
	}

	/**************************************************************************************************************/
	// FEED

	// FEED_AUTHOR_EMAIL
	if ($author = $feed->get_author())
	{
		if ($email = $author->get_email())
		{
			$tmpl = str_replace('{FEED_AUTHOR_EMAIL}', SimplePie_WordPress::post_process('FEED_AUTHOR_EMAIL', $email), $tmpl);
		}
		else
		{
			$tmpl = str_replace('{FEED_AUTHOR_EMAIL}', '', $tmpl);
		}
	}
	else
	{
		$tmpl = str_replace('{FEED_AUTHOR_EMAIL}', '', $tmpl);
	}

	// FEED_AUTHOR_LINK
	if ($author = $feed->get_author())
	{
		if ($link = $author->get_link())
		{
			$tmpl = str_replace('{FEED_AUTHOR_LINK}', SimplePie_WordPress::post_process('FEED_AUTHOR_LINK', $link), $tmpl);
		}
		else
		{
			$tmpl = str_replace('{FEED_AUTHOR_LINK}', '', $tmpl);
		}
	}
	else
	{
		$tmpl = str_replace('{FEED_AUTHOR_LINK}', '', $tmpl);
	}

	// FEED_AUTHOR_NAME
	if ($author = $feed->get_author())
	{
		if ($name = $author->get_name())
		{
			$tmpl = str_replace('{FEED_AUTHOR_NAME}', SimplePie_WordPress::post_process('FEED_AUTHOR_NAME', $name), $tmpl);
		}
		else
		{
			$tmpl = str_replace('{FEED_AUTHOR_NAME}', '', $tmpl);
		}
	}
	else
	{
		$tmpl = str_replace('{FEED_AUTHOR_NAME}', '', $tmpl);
	}

	// FEED_CONTRIBUTOR_EMAIL
	if ($contributor = $feed->get_contributor())
	{
		if ($email = $contributor->get_email())
		{
			$tmpl = str_replace('{FEED_CONTRIBUTOR_EMAIL}', SimplePie_WordPress::post_process('FEED_CONTRIBUTOR_EMAIL', $email), $tmpl);
		}
		else
		{
			$tmpl = str_replace('{FEED_CONTRIBUTOR_EMAIL}', '', $tmpl);
		}
	}
	else
	{
		$tmpl = str_replace('{FEED_CONTRIBUTOR_EMAIL}', '', $tmpl);
	}

	// FEED_CONTRIBUTOR_LINK
	if ($contributor = $feed->get_contributor())
	{
		if ($link = $contributor->get_link())
		{
			$tmpl = str_replace('{FEED_CONTRIBUTOR_LINK}', SimplePie_WordPress::post_process('FEED_CONTRIBUTOR_LINK', $link), $tmpl);
		}
		else
		{
			$tmpl = str_replace('{FEED_CONTRIBUTOR_LINK}', '', $tmpl);
		}
	}
	else
	{
		$tmpl = str_replace('{FEED_CONTRIBUTOR_LINK}', '', $tmpl);
	}

	// FEED_CONTRIBUTOR_NAME
	if ($contributor = $feed->get_contributor())
	{
		if ($name = $contributor->get_name())
		{
			$tmpl = str_replace('{FEED_CONTRIBUTOR_NAME}', SimplePie_WordPress::post_process('FEED_CONTRIBUTOR_NAME', $name), $tmpl);
		}
		else
		{
			$tmpl = str_replace('{FEED_CONTRIBUTOR_NAME}', '', $tmpl);
		}
	}
	else
	{
		$tmpl = str_replace('{FEED_CONTRIBUTOR_NAME}', '', $tmpl);
	}

	// FEED_COPYRIGHT
	if ($copyright = $feed->get_copyright())
	{
		$tmpl = str_replace('{FEED_COPYRIGHT}', SimplePie_WordPress::post_process('FEED_COPYRIGHT', $copyright), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_COPYRIGHT}', '', $tmpl);
	}

	// FEED_DESCRIPTION
	if ($description = $feed->get_description())
	{
		$tmpl = str_replace('{FEED_DESCRIPTION}', SimplePie_WordPress::post_process('FEED_DESCRIPTION', $description), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_DESCRIPTION}', '', $tmpl);
	}

	// FEED_ENCODING
	if ($encoding = $feed->get_encoding())
	{
		$tmpl = str_replace('{FEED_ENCODING}', SimplePie_WordPress::post_process('FEED_ENCODING', $encoding), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_ENCODING}', '', $tmpl);
	}

	// FEED_FAVICON
	if ($favicon = $feed->get_favicon())
	{
		$tmpl = str_replace('{FEED_FAVICON}', SimplePie_WordPress::post_process('FEED_FAVICON', $favicon), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_FAVICON}', '', $tmpl);
	}

	// FEED_IMAGE_HEIGHT
	if ($image_height = $feed->get_image_height())
	{
		$tmpl = str_replace('{FEED_IMAGE_HEIGHT}', SimplePie_WordPress::post_process('FEED_IMAGE_HEIGHT', $image_height), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_IMAGE_HEIGHT}', '', $tmpl);
	}

	// FEED_IMAGE_LINK
	if ($image_link = $feed->get_image_link())
	{
		$tmpl = str_replace('{FEED_IMAGE_LINK}', SimplePie_WordPress::post_process('FEED_IMAGE_LINK', $image_link), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_IMAGE_LINK}', '', $tmpl);
	}

	// FEED_IMAGE_TITLE
	if ($image_title = $feed->get_image_title())
	{
		$tmpl = str_replace('{FEED_IMAGE_TITLE}', SimplePie_WordPress::post_process('FEED_IMAGE_TITLE', $image_title), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_IMAGE_TITLE}', '', $tmpl);
	}

	// FEED_IMAGE_URL
	if ($image_url = $feed->get_image_url())
	{
		$tmpl = str_replace('{FEED_IMAGE_URL}', SimplePie_WordPress::post_process('FEED_IMAGE_URL', $image_url), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_IMAGE_URL}', '', $tmpl);
	}

	// FEED_IMAGE_WIDTH
	if ($image_width = $feed->get_image_width())
	{
		$tmpl = str_replace('{FEED_IMAGE_WIDTH}', SimplePie_WordPress::post_process('FEED_IMAGE_WIDTH', $image_width), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_IMAGE_WIDTH}', '', $tmpl);
	}

	// FEED_LANGUAGE
	if ($language = $feed->get_language())
	{
		$tmpl = str_replace('{FEED_LANGUAGE}', SimplePie_WordPress::post_process('FEED_LANGUAGE', $language), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_LANGUAGE}', '', $tmpl);
	}

	// FEED_LATITUDE
	if ($latitude = $feed->get_latitude())
	{
		$tmpl = str_replace('{FEED_LATITUDE}', SimplePie_WordPress::post_process('FEED_LATITUDE', $latitude), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_LATITUDE}', '', $tmpl);
	}

	// FEED_LONGITUDE
	if ($longitude = $feed->get_longitude())
	{
		$tmpl = str_replace('{FEED_LONGITUDE}', SimplePie_WordPress::post_process('FEED_LONGITUDE', $longitude), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_LONGITUDE}', '', $tmpl);
	}

	// FEED_PERMALINK
	if ($permalink = $feed->get_permalink())
	{
		$tmpl = str_replace('{FEED_PERMALINK}', SimplePie_WordPress::post_process('FEED_PERMALINK', $permalink), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_PERMALINK}', '', $tmpl);
	}

	// FEED_TITLE
	if ($title = $feed->get_title())
	{
		$tmpl = str_replace('{FEED_TITLE}', SimplePie_WordPress::post_process('FEED_TITLE', $title), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{FEED_TITLE}', '', $tmpl);
	}

	// SUBSCRIBE_URL
	if ($subscribe_url = $feed->subscribe_url())
	{
		$tmpl = str_replace('{SUBSCRIBE_URL}', SimplePie_WordPress::post_process('SUBSCRIBE_URL', $subscribe_url), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{SUBSCRIBE_URL}', '', $tmpl);
	}

	// TRUNCATE_FEED_DESCRIPTION
	if ($description = $feed->get_description())
	{
		$tmpl = str_replace('{TRUNCATE_FEED_DESCRIPTION}', SimplePie_Truncate(SimplePie_WordPress::post_process('TRUNCATE_FEED_DESCRIPTION', $description), $truncate_feed_description), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{TRUNCATE_FEED_DESCRIPTION}', '', $tmpl);
	}

	// TRUNCATE_FEED_TITLE
	if ($title = $feed->get_title())
	{
		$tmpl = str_replace('{TRUNCATE_FEED_TITLE}', SimplePie_Truncate(SimplePie_WordPress::post_process('TRUNCATE_FEED_TITLE', $title), $truncate_feed_title), $tmpl);
	}
	else
	{
		$tmpl = str_replace('{TRUNCATE_FEED_TITLE}', '', $tmpl);
	}

	/**************************************************************************************************************/
	// ITEMS

	// Separate out the pre-item template
	$tmpl = explode('{ITEM_LOOP_BEGIN}', $tmpl);
	$pre_tmpl = $tmpl[0];

	// Separate out the item template
	$tmpl = explode('{ITEM_LOOP_END}', $tmpl[1]);
	$item_tmpl = $tmpl[0];

	// Separate out the post-item template
	$post_tmpl = $tmpl[1];

	// Clear out the variable
	unset($tmpl);

	// Start putting the output string together.
	$tmpl = $pre_tmpl;

	// Loop through all of the items that we're supposed to.
	foreach ($feed->get_items(0, $items) as $item)
	{
		// Get a reference to the parent $feed object.
		$parent = $item->get_feed();

		// Get a working copy of the item template.  We don't want to edit the original.
		$working_item = $item_tmpl;

		// ITEM_CONTRIBUTOR_EMAIL
		if ($contributor = $item->get_contributor())
		{
			if ($email = $contributor->get_email())
			{
				$working_item = str_replace('{ITEM_CONTRIBUTOR_EMAIL}', SimplePie_WordPress::post_process('ITEM_CONTRIBUTOR_EMAIL', $email), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_CONTRIBUTOR_EMAIL}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_CONTRIBUTOR_EMAIL}', '', $working_item);
		}

		// ITEM_CONTRIBUTOR_LINK
		if ($contributor = $item->get_contributor())
		{
			if ($link = $contributor->get_link())
			{
				$working_item = str_replace('{ITEM_CONTRIBUTOR_LINK}', SimplePie_WordPress::post_process('ITEM_CONTRIBUTOR_LINK', $link), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_CONTRIBUTOR_LINK}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_CONTRIBUTOR_LINK}', '', $working_item);
		}

		// ITEM_CONTRIBUTOR_NAME
		if ($contributor = $item->get_contributor())
		{
			if ($name = $contributor->get_name())
			{
				$working_item = str_replace('{ITEM_CONTRIBUTOR_NAME}', SimplePie_WordPress::post_process('ITEM_CONTRIBUTOR_NAME', $name), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_CONTRIBUTOR_NAME}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_CONTRIBUTOR_NAME}', '', $working_item);
		}

		// ITEM_COPYRIGHT
		if ($copyright = $item->get_copyright())
		{
			$working_item = str_replace('{ITEM_COPYRIGHT}', SimplePie_WordPress::post_process('ITEM_COPYRIGHT', $copyright), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_COPYRIGHT}', '', $working_item);
		}

		// ITEM_PARENT_AUTHOR_EMAIL
		if ($author = $parent->get_author())
		{
			if ($email = $author->get_email())
			{
				$working_item = str_replace('{ITEM_PARENT_AUTHOR_EMAIL}', SimplePie_WordPress::post_process('ITEM_PARENT_AUTHOR_EMAIL', $email), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_PARENT_AUTHOR_EMAIL}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_AUTHOR_EMAIL}', '', $working_item);
		}

		// ITEM_PARENT_AUTHOR_LINK
		if ($author = $parent->get_author())
		{
			if ($link = $author->get_link())
			{
				$working_item = str_replace('{ITEM_PARENT_AUTHOR_LINK}', SimplePie_WordPress::post_process('ITEM_PARENT_AUTHOR_LINK', $link), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_PARENT_AUTHOR_LINK}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_AUTHOR_LINK}', '', $working_item);
		}

		// ITEM_PARENT_AUTHOR_NAME
		if ($author = $parent->get_author())
		{
			if ($name = $author->get_name())
			{
				$working_item = str_replace('{ITEM_PARENT_AUTHOR_NAME}', SimplePie_WordPress::post_process('ITEM_PARENT_AUTHOR_NAME', $name), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_PARENT_AUTHOR_NAME}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_AUTHOR_NAME}', '', $working_item);
		}

		// ITEM_PARENT_CONTRIBUTOR_EMAIL
		if ($contributor = $parent->get_contributor())
		{
			if ($email = $contributor->get_email())
			{
				$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_EMAIL}', SimplePie_WordPress::post_process('ITEM_PARENT_CONTRIBUTOR_EMAIL', $email), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_EMAIL}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_EMAIL}', '', $working_item);
		}

		// ITEM_PARENT_CONTRIBUTOR_LINK
		if ($contributor = $parent->get_contributor())
		{
			if ($link = $contributor->get_link())
			{
				$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_LINK}', SimplePie_WordPress::post_process('ITEM_PARENT_CONTRIBUTOR_LINK', $link), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_LINK}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_LINK}', '', $working_item);
		}

		// ITEM_PARENT_CONTRIBUTOR_NAME
		if ($contributor = $parent->get_contributor())
		{
			if ($name = $contributor->get_name())
			{
				$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_NAME}', SimplePie_WordPress::post_process('ITEM_PARENT_CONTRIBUTOR_NAME', $name), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_NAME}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_CONTRIBUTOR_NAME}', '', $working_item);
		}

		// ITEM_AUTHOR_EMAIL
		if ($author = $item->get_author())
		{
			if ($email = $author->get_email())
			{
				$working_item = str_replace('{ITEM_AUTHOR_EMAIL}', SimplePie_WordPress::post_process('ITEM_AUTHOR_EMAIL', $email), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_AUTHOR_EMAIL}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_AUTHOR_EMAIL}', '', $working_item);
		}

		// ITEM_AUTHOR_LINK
		if ($author = $item->get_author())
		{
			if ($link = $author->get_link())
			{
				$working_item = str_replace('{ITEM_AUTHOR_LINK}', SimplePie_WordPress::post_process('ITEM_AUTHOR_LINK', $link), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_AUTHOR_LINK}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_AUTHOR_LINK}', '', $working_item);
		}

		// ITEM_AUTHOR_NAME
		if ($author = $item->get_author())
		{
			if ($name = $author->get_name())
			{
				$working_item = str_replace('{ITEM_AUTHOR_NAME}', SimplePie_WordPress::post_process('ITEM_AUTHOR_NAME', $name), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_AUTHOR_NAME}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_AUTHOR_NAME}', '', $working_item);
		}

		// ITEM_CATEGORY
		if ($category = $item->get_category())
		{
			if ($label = $category->get_label())
			{
				$working_item = str_replace('{ITEM_CATEGORY}', SimplePie_WordPress::post_process('ITEM_CATEGORY', $label), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_CATEGORY}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_CATEGORY}', '', $working_item);
		}

		// ITEM_CONTENT
		if ($content = $item->get_content())
		{
			$working_item = str_replace('{ITEM_CONTENT}', SimplePie_WordPress::post_process('ITEM_CONTENT', $content), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_CONTENT}', '', $working_item);
		}

		// ITEM_DATE
		if ($date = $item->get_date($date_format))
		{
			$working_item = str_replace('{ITEM_DATE}', SimplePie_WordPress::post_process('ITEM_DATE', $date), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_DATE}', '', $working_item);
		}

		// ITEM_DATE_UTC
		if ($date = $item->get_date('U'))
		{
			$date = gmdate($date_format, $date);
			$working_item = str_replace('{ITEM_DATE_UTC}', SimplePie_WordPress::post_process('ITEM_DATE_UTC', $date), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_DATE_UTC}', '', $working_item);
		}

		// ITEM_DESCRIPTION
		if ($description = $item->get_description())
		{
			$working_item = str_replace('{ITEM_DESCRIPTION}', SimplePie_WordPress::post_process('ITEM_DESCRIPTION', $description), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_DESCRIPTION}', '', $working_item);
		}

		// ITEM_ENCLOSURE_EMBED
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->native_embed())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_EMBED}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_EMBED', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_EMBED}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_EMBED}', '', $working_item);
		}

		// ITEM_ENCLOSURE_EXTENSION
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_extension())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_EXTENSION}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_EXTENSION', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_EXTENSION}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_EXTENSION}', '', $working_item);
		}

		// ITEM_ENCLOSURE_HANDLER
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_handler())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_HANDLER}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_HANDLER', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_HANDLER}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_HANDLER}', '', $working_item);
		}

		// ITEM_ENCLOSURE_LENGTH
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_length())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_LENGTH}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_LENGTH', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_LENGTH}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_LENGTH}', '', $working_item);
		}

		// ITEM_ENCLOSURE_LINK
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_link())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_LINK}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_LINK', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_LINK}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_LINK}', '', $working_item);
		}

		// ITEM_ENCLOSURE_REAL_TYPE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_real_type())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_REAL_TYPE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_REAL_TYPE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_REAL_TYPE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_REAL_TYPE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_SIZE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_size())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_SIZE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_SIZE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_SIZE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_SIZE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_TYPE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_type())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_TYPE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_TYPE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_TYPE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_TYPE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_BITRATE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_bitrate())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_BITRATE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_BITRATE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_BITRATE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_BITRATE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_CHANNELS
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_channels())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_CHANNELS}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_CHANNELS', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_CHANNELS}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_CHANNELS}', '', $working_item);
		}

		// ITEM_ENCLOSURE_DESCRIPTION
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_description())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_DESCRIPTION}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_DESCRIPTION', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_DESCRIPTION}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_DESCRIPTION}', '', $working_item);
		}

		// ITEM_ENCLOSURE_DURATION
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_duration())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_DURATION}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_DURATION', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_DURATION}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_DURATION}', '', $working_item);
		}

		// ITEM_ENCLOSURE_EXPRESSION
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_expression())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_EXPRESSION}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_EXPRESSION', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_EXPRESSION}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_EXPRESSION}', '', $working_item);
		}

		// ITEM_ENCLOSURE_FRAMERATE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_framerate())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_FRAMERATE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_FRAMERATE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_FRAMERATE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_FRAMERATE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_HASH
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_hash())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_HASH}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_HASH', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_HASH}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_HASH}', '', $working_item);
		}

		// ITEM_ENCLOSURE_HEIGHT
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_height())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_HEIGHT}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_HEIGHT', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_HEIGHT}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_HEIGHT}', '', $working_item);
		}

		// ITEM_ENCLOSURE_LANGUAGE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_language())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_LANGUAGE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_LANGUAGE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_LANGUAGE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_LANGUAGE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_MEDIUM
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_medium())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_MEDIUM}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_MEDIUM', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_MEDIUM}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_MEDIUM}', '', $working_item);
		}

		// ITEM_ENCLOSURE_PLAYER
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_player())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_PLAYER}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_PLAYER', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_PLAYER}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_PLAYER}', '', $working_item);
		}

		// ITEM_ENCLOSURE_SAMPLINGRATE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_sampling_rate())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_SAMPLINGRATE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_SAMPLINGRATE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_SAMPLINGRATE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_SAMPLINGRATE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_THUMBNAIL
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_thumbnail())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_THUMBNAIL}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_THUMBNAIL', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_THUMBNAIL}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_THUMBNAIL}', '', $working_item);
		}

		// ITEM_ENCLOSURE_TITLE
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_title())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_TITLE}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_TITLE', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_TITLE}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_TITLE}', '', $working_item);
		}

		// ITEM_ENCLOSURE_WIDTH
		if ($enclosure = $item->get_enclosure())
		{
			if ($encltemp = $enclosure->get_width())
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_WIDTH}', SimplePie_WordPress::post_process('ITEM_ENCLOSURE_WIDTH', $encltemp), $working_item);
			}
			else
			{
				$working_item = str_replace('{ITEM_ENCLOSURE_WIDTH}', '', $working_item);
			}
		}
		else
		{
			$working_item = str_replace('{ITEM_ENCLOSURE_WIDTH}', '', $working_item);
		}

		// ITEM_ID
		if ($id = $item->get_id())
		{
			$working_item = str_replace('{ITEM_ID}', SimplePie_WordPress::post_process('ITEM_ID', $id), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_ID}', '', $working_item);
		}

		// ITEM_ID
		if ($latitude = $item->get_latitude())
		{
			$working_item = str_replace('{ITEM_LATITUDE}', SimplePie_WordPress::post_process('ITEM_LATITUDE', $latitude), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_LATITUDE}', '', $working_item);
		}

		// ITEM_LOCAL_DATE
		if ($local_date = $item->get_local_date($local_date_format))
		{
			$working_item = str_replace('{ITEM_LOCAL_DATE}', SimplePie_WordPress::post_process('ITEM_LOCAL_DATE', $local_date), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_LOCAL_DATE}', '', $working_item);
		}

		// ITEM_LOCAL_DATE_UTC
		if ($local_date = $item->get_date('U'))
		{
			$local_date = gmdate('U', $local_date);
			$local_date = strftime($local_date_format, $local_date);
			$working_item = str_replace('{ITEM_LOCAL_DATE_UTC}', SimplePie_WordPress::post_process('ITEM_LOCAL_DATE_UTC', $local_date), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_LOCAL_DATE_UTC}', '', $working_item);
		}

		// ITEM_LONGITUDE
		if ($longitude = $item->get_longitude())
		{
			$working_item = str_replace('{ITEM_LONGITUDE}', SimplePie_WordPress::post_process('ITEM_LONGITUDE', $longitude), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_LONGITUDE}', '', $working_item);
		}

		// ITEM_PERMALINK
		if ($permalink = $item->get_permalink())
		{
			$working_item = str_replace('{ITEM_PERMALINK}', SimplePie_WordPress::post_process('ITEM_PERMALINK', $permalink), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PERMALINK}', '', $working_item);
		}

		// ITEM_TITLE
		if ($title = $item->get_title())
		{
			$working_item = str_replace('{ITEM_TITLE}', SimplePie_WordPress::post_process('ITEM_TITLE', $title), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_TITLE}', '', $working_item);
		}

		// ITEM_PARENT_COPYRIGHT
		if ($copyright = $parent->get_copyright())
		{
			$working_item = str_replace('{ITEM_PARENT_COPYRIGHT}', SimplePie_WordPress::post_process('ITEM_PARENT_COPYRIGHT', $copyright), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_COPYRIGHT}', '', $working_item);
		}

		// ITEM_PARENT_DESCRIPTION
		if ($description = $parent->get_description())
		{
			$working_item = str_replace('{ITEM_PARENT_DESCRIPTION}', SimplePie_WordPress::post_process('ITEM_PARENT_DESCRIPTION', $description), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_DESCRIPTION}', '', $working_item);
		}

		// ITEM_PARENT_ENCODING
		if ($encoding = $parent->get_encoding())
		{
			$working_item = str_replace('{ITEM_PARENT_ENCODING}', SimplePie_WordPress::post_process('ITEM_PARENT_ENCODING', $encoding), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_ENCODING}', '', $working_item);
		}

		// ITEM_PARENT_FAVICON
		if ($favicon = $parent->get_favicon())
		{
			$working_item = str_replace('{ITEM_PARENT_FAVICON}', SimplePie_WordPress::post_process('ITEM_PARENT_FAVICON', $favicon), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_FAVICON}', '', $working_item);
		}

		// ITEM_PARENT_IMAGE_HEIGHT
		if ($image_height = $parent->get_image_height())
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_HEIGHT}', SimplePie_WordPress::post_process('ITEM_PARENT_IMAGE_HEIGHT', $image_height), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_HEIGHT}', '', $working_item);
		}

		// ITEM_PARENT_IMAGE_LINK
		if ($image_link = $parent->get_image_link())
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_LINK}', SimplePie_WordPress::post_process('ITEM_PARENT_IMAGE_LINK', $image_link), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_LINK}', '', $working_item);
		}

		// ITEM_PARENT_IMAGE_TITLE
		if ($image_title = $parent->get_image_title())
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_TITLE}', SimplePie_WordPress::post_process('ITEM_PARENT_IMAGE_TITLE', $image_title), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_TITLE}', '', $working_item);
		}

		// ITEM_PARENT_IMAGE_URL
		if ($image_url = $parent->get_image_url())
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_URL}', SimplePie_WordPress::post_process('ITEM_PARENT_IMAGE_URL', $image_url), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_URL}', '', $working_item);
		}

		// ITEM_PARENT_IMAGE_WIDTH
		if ($image_width = $parent->get_image_width())
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_WIDTH}', SimplePie_WordPress::post_process('ITEM_PARENT_IMAGE_WIDTH', $image_width), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_IMAGE_WIDTH}', '', $working_item);
		}

		// ITEM_PARENT_LANGUAGE
		if ($language = $parent->get_language())
		{
			$working_item = str_replace('{ITEM_PARENT_LANGUAGE}', SimplePie_WordPress::post_process('ITEM_PARENT_LANGUAGE', $language), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_LANGUAGE}', '', $working_item);
		}

		// ITEM_PARENT_LATITUDE
		if ($latitude = $parent->get_latitude())
		{
			$working_item = str_replace('{ITEM_PARENT_LATITUDE}', SimplePie_WordPress::post_process('ITEM_PARENT_LATITUDE', $latitude), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_LATITUDE}', '', $working_item);
		}

		// ITEM_PARENT_LONGITUDE
		if ($longitude = $parent->get_longitude())
		{
			$working_item = str_replace('{ITEM_PARENT_LONGITUDE}', SimplePie_WordPress::post_process('ITEM_PARENT_LONGITUDE', $longitude), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_LONGITUDE}', '', $working_item);
		}

		// ITEM_PARENT_PERMALINK
		if ($permalink = $parent->get_permalink())
		{
			$working_item = str_replace('{ITEM_PARENT_PERMALINK}', SimplePie_WordPress::post_process('ITEM_PARENT_PERMALINK', $permalink), $working_item);
		}
		else
		{

			$working_item = str_replace('{ITEM_PARENT_PERMALINK}', '', $working_item);
		}

		// ITEM_PARENT_TITLE
		if ($title = $parent->get_title())
		{
			$working_item = str_replace('{ITEM_PARENT_TITLE}', SimplePie_WordPress::post_process('ITEM_PARENT_TITLE', $title), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_TITLE}', '', $working_item);
		}

		// ITEM_PARENT_SUBSCRIBE_URL
		if ($subscribe_url = $parent->subscribe_url())
		{
			$working_item = str_replace('{ITEM_PARENT_SUBSCRIBE_URL}', SimplePie_WordPress::post_process('ITEM_PARENT_SUBSCRIBE_URL', $subscribe_url), $working_item);
		}
		else
		{
			$working_item = str_replace('{ITEM_PARENT_SUBSCRIBE_URL}', '', $working_item);
		}

		// TRUNCATE_ITEM_DESCRIPTION
		if ($description = $item->get_description())
		{
			$working_item = str_replace('{TRUNCATE_ITEM_DESCRIPTION}', SimplePie_Truncate(SimplePie_WordPress::post_process('TRUNCATE_ITEM_DESCRIPTION', $description), $truncate_item_description), $working_item);
		}
		else
		{
			$working_item = str_replace('{TRUNCATE_ITEM_DESCRIPTION}', '', $working_item);
		}

		// TRUNCATE_ITEM_TITLE
		if ($title = $item->get_title())
		{
			$working_item = str_replace('{TRUNCATE_ITEM_TITLE}', SimplePie_Truncate(SimplePie_WordPress::post_process('TRUNCATE_ITEM_TITLE', $title), $truncate_item_title), $working_item);
		}
		else
		{
			$working_item = str_replace('{TRUNCATE_ITEM_TITLE}', '', $working_item);
		}

		// TRUNCATE_ITEM_PARENT_DESCRIPTION
		if ($description = $parent->get_description())
		{
			$working_item = str_replace('{TRUNCATE_ITEM_PARENT_DESCRIPTION}', SimplePie_Truncate(SimplePie_WordPress::post_process('TRUNCATE_ITEM_PARENT_DESCRIPTION', $description), $truncate_feed_description), $working_item);
		}
		else
		{
			$working_item = str_replace('{TRUNCATE_ITEM_PARENT_DESCRIPTION}', '', $working_item);
		}

		// TRUNCATE_ITEM_PARENT_TITLE
		if ($title = $parent->get_title())
		{
			$working_item = str_replace('{TRUNCATE_ITEM_PARENT_TITLE}', SimplePie_Truncate(SimplePie_WordPress::post_process('TRUNCATE_ITEM_PARENT_TITLE', $title), $truncate_feed_title), $working_item);
		}
		else
		{
			$working_item = str_replace('{TRUNCATE_ITEM_PARENT_TITLE}', '', $working_item);
		}

		$tmpl .= $working_item;
	}

	/**************************************************************************************************************/
	// LAST STUFF

	// Start by removing all line breaks and tabs.
	$tmpl = preg_replace('/(\n|\r|\t)/i', "", $tmpl);

	// PLUGIN_DIR
	$tmpl = str_replace('{PLUGIN_DIR}', SIMPLEPIE_PLUGINDIR_WEB, $tmpl);

	$tmpl .= $post_tmpl;

	// Kill the object to prevent memory leaks.
	$feed->__destruct();
	unset($feed);
	unset($encltemp);
	unset($working_item);

	// Return the data back to the page.
	return $tmpl;
}
?>