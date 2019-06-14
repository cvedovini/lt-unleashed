<?php

define('LIBRARYTHING_API', 'http://www.librarything.com/api_getdata.php');

/**
 * Define the 'LIBRARYTHING_API_REVIEW_MAX' constant in your wp-config.php file
 * to limit the number of characters shown in book reviews.
 */
if (!defined('LIBRARYTHING_API_REVIEW_MAX')) {
	define('LIBRARYTHING_API_REVIEW_MAX', 5000);
}

class LibraryThing_API {
	protected $userid = '';

	function __construct($userid) {
		$this->userid = $userid;
	}

	function get_profile_url() {
		return 'http://www.librarything.com/profile/' . $this->userid;
	}

	function get_books($max, $booksort, $tags, $cover_width, $cover_height) {
		$api_url = $this->get_api_url($max, $booksort, $tags, $cover_width, $cover_height);
		$cache_key = 'ltu_' . sha1($api_url);

		if (!isset($_REQUEST['ltu_nocache'])) {
			$books = get_transient($cache_key);
			if ($books && is_array($books)) return $books;
		}

		$response = wp_remote_get($api_url);

		if (!is_wp_error($response)) {
			$body = wp_remote_retrieve_body($response);
			$body = substr($body, 20, strlen($body) - 56);
			$body = json_decode($body);
			$books = $body->books;
			set_transient($cache_key, $books, LIBRARYTHING_CACHE_TIMEOUT);
			return $books;
		}

		return $response;
	}

	protected function get_api_url($max, $booksort, $tags, $cover_width, $cover_height) {
        $params = array(
        		'userid' => $this->userid,
        		'booksort' => $booksort,
        		'max' => $max,
        		'resultsets' => 'books,bookratings,bookreviews,booktags',
                'showReviews' => 1,
        		'reviewmax' => LIBRARYTHING_API_REVIEW_MAX,
        		'showTags' => 1,
                'callback' => 'noop',
        	);

       	if ($cover_width) $params['coverwidth'] = $cover_width;
       	if ($cover_height) $params['coverheight'] = $cover_height;
        if ($tags) $params['tagList'] = implode(',', $tags);
        if (defined('LIBRARYTHING_API_KEY')) $params['key'] = LIBRARYTHING_API_KEY;

        return LIBRARYTHING_API . '?' . http_build_query($params);
	}
}