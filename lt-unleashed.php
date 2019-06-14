<?php
/*
Plugin Name: LT Unleashed
Plugin URI: http://vdvn.me/pga
Description: Offers shortcodes and widgets to display any public LibraryThing books collection in WordPress
Author: Claude Vedovini
Author URI: http://vdvn.me/
Version: 1.1.1
Text Domain: lt-unleashed
Domain Path: /languages

# The code in this plugin is free software; you can redistribute the code aspects of
# the plugin and/or modify the code under the terms of the GNU Lesser General
# Public License as published by the Free Software Foundation; either
# version 3 of the License, or (at your option) any later version.

# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
# MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
# LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
# OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
# WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
#
# See the GNU lesser General Public License for more details.
*/

define('LIBRARYTHING_UNLEASHED_PLUGIN_BASENAME', plugin_basename(__FILE__));
if (!defined('LIBRARYTHING_CACHE_TIMEOUT')) {
	define('LIBRARYTHING_CACHE_TIMEOUT', 43200); // 12 hours
}

/** Initialize plugin **/
add_action('plugins_loaded', array('LibraryThing_Unleashed_Plugin', 'get_instance'));

class LibraryThing_Unleashed_Plugin {

	private static $instance;

	public static function get_instance() {
		if (!self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	function __construct() {
		// Make plugin available for translation
		// Translations can be filed in the /languages/ directory
		add_filter('load_textdomain_mofile', array(&$this, 'smarter_load_textdomain'), 10, 2);
		load_plugin_textdomain('lt-unleashed', false, dirname(plugin_basename(__FILE__)) . '/languages/' );

		register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
		add_action('init', array(&$this, 'init'));
		add_action('widgets_init', array(&$this, 'register_widget'));

		if ($this->get_option('single_book_page')) {
			add_action('init', array(&$this, 'setup_single_book_page'));
		}

		if (is_admin()) {
			require_once('includes/class-librarything-unleashed-admin.php');
			$this->admin = new LibraryThing_Unleashed_Admin($this);
		}
	}

	function smarter_load_textdomain($mofile, $domain) {
		if ($domain == 'lt-unleashed' && !is_readable($mofile)) {
			extract(pathinfo($mofile));
			$pos = strrpos($filename, '_');

			if ($pos !== false) {
				# cut off the locale part, leaving the language part only
				$filename = substr($filename, 0, $pos);
				$mofile = $dirname . '/' . $filename . '.' . $extension;
			}
		}

		return $mofile;
	}

	function init() {
		add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
		add_shortcode('books', array(&$this, 'books_shortcode'));
		add_shortcode('book', array(&$this, 'book_shortcode'));
		add_filter('librarything_cover_size', array(&$this, 'cover_size'), 10, 2);
	}

	function filter_options($options) {
		$defaults = array (
				'aws_access_key'	=> '',
				'aws_secret_key'	=> '',
				'amzn_locale'		=> 'com',
				'affiliate_tag'		=> '',
				'single_book_page'	=> 0,
				'book_base'		=> 'books',
				'hide_amzn_button'	=> false,
				'hide_signature'	=> false
		);

		return shortcode_atts($defaults, (array) $options);
	}

	function get_options() {
		return $this->filter_options(get_option('libarything-unleashed-options'));
	}

	function get_option($option_name) {
		$options = $this->get_options();
		return $options[$option_name];
	}

	function update_option($option_name, $option_value=null) {
		$options = get_option('libarything-unleashed-options', array());
		if (null === $option_value) {
			unset($options[$option_name]);
		} else {
			$options[$option_name] = $option_value;
		}
		return update_option('libarything-unleashed-options', $options);
	}

	function setup_single_book_page() {
		if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'deactivate' &&
				$_REQUEST['plugin'] == plugin_basename(__FILE__)) {
			// Don't do anything if we're deactivating this plugin
			return;
		}

		$single_book_page_id = $this->get_option('single_book_page');
		$prefix = 'index.php?page_id=' . $single_book_page_id;
		$base = $this->get_option('book_base');

		add_rewrite_rule("^$base/([^/]*)/?$", $prefix . '&work=$matches[1]', 'top');
		add_rewrite_rule("^$base/([^/]*)/([^/]*)/?$", $prefix . '&work=$matches[1]&isbn=$matches[2]', 'top');

		add_filter('query_vars', array(&$this, 'query_vars'));
		add_action('template_redirect', array(&$this, 'template_redirect'));

		$rules = get_option('rewrite_rules');
		if (!isset($rules["^$base/([^/]*)/?$"])) {
			flush_rewrite_rules();
		}

		add_filter('the_content', array(&$this, 'filter_content'), 10);
		add_filter('the_title', array(&$this, 'filter_title'), 10, 2);
		add_filter('single_post_title', array(&$this, 'filter_title'), 10, 2);
		$this->book = false;
	}

	function query_vars($vars) {
		$vars[] = 'work';
		$vars[] = 'isbn';
		return $vars;
	}

	function template_redirect() {
		$single_book_page_id = $this->get_option('single_book_page');

		if (get_query_var('page_id') == $single_book_page_id) {
			if (get_query_var('work')) {
				if (get_query_var('isbn')) {
					require_once ('includes/class-amazon-api.php');
					$amazon_api = new Amazon_API();
					$this->book = $amazon_api->lookup(get_query_var('isbn'));
				}

				// If no isbn or we couldn't find the book on Amzn then redirect to LibraryThing
				if (empty($this->book)) {
					$work_id = get_query_var('work');
					wp_redirect("http://www.librarything.com/work/book/$work_id/");
					exit;
				}
			}
		}
	}

	function register_widget() {
		require_once('includes/class-librarything-unleashed-widget.php');
		register_widget('LibraryThing_Unleashed_Widget');
	}

	function enqueue_scripts() {
		wp_enqueue_style('ltu', plugins_url('style.css', __FILE__), false, '1.0');
	}

	function filter_title($title, $post_or_id) {
		$id = (is_object($post_or_id)) ? $post_or_id->ID : $post_or_id;
		$single_book_page_id = $this->get_option('single_book_page');

		if ($id == $single_book_page_id && $this->book) {
			return $this->book->ItemAttributes->Title;
		}

		return $title;
	}

	function filter_content($content) {
		global $post;
		$single_book_page_id = $this->get_option('single_book_page');

		if ($post->ID == $single_book_page_id && $this->book) {
			return ltu_get_the_book($this->book);
		}

		return $content;
	}

	function cover_size($size, $template) {
		if ($template == 'covers') $size = array('width' => 90, 'height' => false);
		if ($template == 'list') $size = array('width' => 180, 'height' => false);
		return $size;
	}

	function books_shortcode($atts, $content=false) {
		$atts = shortcode_atts(array(
				'username' => '',
				'count' => 20,
				'sort_by' => 'entry_REV',
				'template' => 'covers',
				'tags' => false
		), $atts, 'books_shortcode');
		extract($atts);

		return ltu_get_the_books($username, $count, $sort_by, $template, $tags);
	}

	function book_shortcode($atts, $content=false) {
		$atts = shortcode_atts(array(
				'isbn' => '',
				'template' => 'book-single'
		), $atts, 'book_shortcode');
		extract($atts);

		return ltu_get_the_book($isbn, $template);
	}
}


function ltu_locate_template($template_name) {
	$template_path = locate_template("librarything/$template_name.php");

	if (empty($template_path) &&
			file_exists(WP_CONTENT_DIR . "/librarything/$template_name.php")) {
		$template_path = WP_CONTENT_DIR . "/librarything/$template_name.php";
	}

	if (empty($template_path) &&
			file_exists(plugin_dir_path(__FILE__) . "templates/$template_name.php")) {
		$template_path = plugin_dir_path(__FILE__) . "templates/$template_name.php";
	}

	return apply_filters('librarything_template', $template_path, $template_name);
}


function ltu_get_the_books($librarything_id, $count=20, $sort_by='entry_REV',
		$template='covers', $tags=false) {

	if ($tags && !is_array($tags)) {
		$tags = explode(',', $tags);
		$tags = array_map('trim', $tags);
	}

	if (empty($librarything_id)) {
		return '<p>' . __('You need to provide your LibraryThing username for your books to show here.', 'lt-unleashed') . '</p>';
	}

	$cover_size = apply_filters('librarything_cover_size', array('width' => false, 'height' => false), $template);

	require_once('includes/class-librarything-api.php');
	$librarything_api = new LibraryThing_API($librarything_id);
	$books = $librarything_api->get_books($count, $sort_by, $tags, $cover_size['width'], $cover_size['height']);

	if (empty($books)) {
		$message = __('The plugin could not retrieve any book from this collection, if you think this is wrong then <a href="%s">check that your LibraryThing account is public</a>. If your account is public and you used tag filtering then check your tags, they are case sensitive</a>.', 'lt-unleashed');
		$message = sprintf($message, $librarything_api->get_profile_url());
		return '<p>' . $message . '</p>';
	}

	$template_path = ltu_locate_template($template);
	if ($template_path) {
		ob_start();
		require($template_path);
		$output = ob_get_clean() . get_the_signature();
		return '<div class="librarything">' . $output . '</div>';
	}
}


function ltu_the_books($librarything_id, $count=20, $sort_by='entry_REV',
		$template='covers', $tags=false) {
	echo ltu_get_the_books($librarything_id, $count, $sort_by, $template, $tags);
}


function ltu_get_the_book($isbn_or_book, $template='book-single') {
	require_once ('includes/class-amazon-api.php');
	$amazon_api = new Amazon_API();

	if (is_object($isbn_or_book)) {
		$book = $isbn_or_book;
	} else {
		$book = $amazon_api->lookup($isbn_or_book);
	}

	if ($book) {
		$similarities = $amazon_api->similarityLookup($book->ASIN);
	}

	$template_path = ltu_locate_template($template);
	if ($template_path) {
		ob_start();
		require($template_path);
		$output = ob_get_clean() . get_the_signature();
		return '<div class="librarything">' . $output . '</div>';
	}
}


function ltu_the_book($isbn_or_book, $template='book-single') {
	echo ltu_get_the_book($isbn_or_book, $template);
}

function get_the_signature() {
	$plugin = LibraryThing_Unleashed_Plugin::get_instance();

	if (!$plugin->get_option('hide_signature')) {
		$output[] = '<div class="signature"><p>';
		$format = __('powered by the <a href="%s">LibraryThing Unleashed</a> plugin.', 'lt-unleashed');
		$output[] = sprintf($format, 'http://vdvn.me/p33s');
		$output[] = '</p></div>';
		return implode('', $output);
	}

	return '';
}

function ltu_get_book_url($work_id, $book, $default=false) {
	$plugin = LibraryThing_Unleashed_Plugin::get_instance();
	$base = $plugin->get_option('book_base');

	if ($plugin->get_option('aws_secret_key') &&
			$plugin->get_option('single_book_page')) {
		if (!empty($book->ISBN_cleaned)) {
			return home_url("$base/$work_id/{$book->ISBN_cleaned}/");
		}
		
		if (is_numeric($work_id)) {
			return home_url("$base/$work_id/");
		}
	} 
	
	if (is_numeric($work_id)) {
		return "http://www.librarything.com/work/book/$work_id/";
	}

	return $default;
}

function ltu_esc_url($url) {
	if (is_ssl()) {
		$url = str_replace('http://ecx.images-amazon.com/', 'https://images-na.ssl-images-amazon.com/', $url);
	}
	
	return esc_url($url);	
}


/**
 * Transforms a float into a star rating
 */
function ltu_star_rating($rating, $max=5) {
	$value = (float) $rating;
	$i = floor($value);
	if ($value == $i) {
		$strv = sprintf('%.0f', $value);
	} else {
		$strv = sprintf('%.1f', $value);
	}

	$arg = (int) $max;

	$value = ($value * 5) / $arg;
	$value = (int) (2 * $value);

	$output[] = sprintf('<span title="%s out of %s">', $strv, $arg);
	for ($i=2; $i < 11; $i += 2) {
		if ($i - $value <= 0) {
			$output[] = '<span class="starrating star_on"></span>';
		} elseif ($i - $value > 1) {
			$output[] = '<span class="starrating star_off"></span>';
		} else {
			$output[] = '<span class="starrating star_half"></span>';
		}
	}
	$output[] = '</span>';
	return implode('', $output);

}
