<?php

class LibraryThing_Unleashed_Admin {

	function __construct(&$plugin) {
		$this->plugin = $plugin;
		add_action('admin_menu', array(&$this, 'init'));
	}

	function init() {
		add_filter('plugin_action_links_' . LIBRARYTHING_UNLEASHED_PLUGIN_BASENAME, array(&$this, 'add_settings_link'));
		add_submenu_page('options-general.php', __('LibraryThing Unleashed Options', 'lt-unleashed'),
				__('LibraryThing', 'lt-unleashed'), 'manage_options',
				'libarything-unleashed-options', array(&$this, 'options_page'));

		register_setting('libarything-unleashed-options', 'libarything-unleashed-options', array(&$this, 'sanitize_options'));
		add_settings_section('default', '', '', 'libarything-unleashed-options');
		$this->add_settings_field('aws_access_key', __('AWS access key', 'lt-unleashed'));
		$this->add_settings_field('aws_secret_key', __('AWS secret key', 'lt-unleashed'));
		$this->add_settings_field('amzn_locale', __('Amazon location', 'lt-unleashed'));
		$this->add_settings_field('affiliate_tag', __('Amazon affiliate tag', 'lt-unleashed'));
		$this->add_settings_field('single_book_page', __('Book page', 'lt-unleashed'));
		$this->add_settings_field('book_base', __('Book base', 'lt-unleashed'));
		$this->add_settings_field('hide_amzn_button', __('Hide "Buy Now!" button', 'lt-unleashed'));
		$this->add_settings_field('hide_signature', __('Hide signature', 'lt-unleashed'));
	}

	function add_settings_link($links) {
		$url = admin_url('options-general.php?page=libarything-unleashed-options');
		$links[] = '<a href="' . esc_url($url) . '">' . __('Settings') . '</a>';
		return $links;
	}

	function add_settings_field($id, $title) {
		$full_id = "libarything-unleashed-options[$id]";
		add_settings_field($full_id, $title, array(&$this, "field_$id"), 'libarything-unleashed-options');
	}

	function options_page() { ?>
		<div class="wrap">
			<h1><?php _e('LibraryThing Unleashed Options', 'lt-unleashed'); ?></h1>
			<form method="POST" action="options.php"><?php
				settings_fields('libarything-unleashed-options');
				do_settings_sections('libarything-unleashed-options');
				submit_button(); ?>
			</form>
		</div><?php
	}

	function sanitize_options($options) {
		$options = $this->plugin->filter_options($options);

		// Must we create a new page?
		if ('create_new' == $options['single_book_page']) {
			$options['single_book_page'] = wp_insert_post(array(
						'post_title' => __('Single Book Page', 'lt-unleashed'),
						'post_status' => 'publish',
						'post_type' => 'page'
					));
		}

		// If the page or base changed we must flush the rewrite rules
		if ($options['single_book_page'] != $this->plugin->get_option('single_book_page') ||
				$options['book_base'] != $this->plugin->get_option('book_base')) {
			flush_rewrite_rules();
		}

		return $options;
	}

	function field_aws_access_key() { ?>
		<input type="text" name="libarything-unleashed-options[aws_access_key]" class="code" size="42"
			value="<?php echo $this->plugin->get_option('aws_access_key'); ?>" /><br>
		<em><?php _e('The access key for the Amazon Product Advertising API which is used to fetch data for single book pages.', 'lt-unleashed'); ?></em><?php
	}

	function field_aws_secret_key() { ?>
		<input type="password" name="libarything-unleashed-options[aws_secret_key]" class="code" size="42"
			value="<?php echo $this->plugin->get_option('aws_secret_key'); ?>" /><br>
		<em><?php _e('The secret key for the Amazon Product Advertising API.', 'lt-unleashed'); ?></em><?php
	}

	function field_single_book_page() { ?>
		<select name="libarything-unleashed-options[single_book_page]"><?php
			$page_id = $this->plugin->get_option('single_book_page');
			$pages = get_pages('post_status=publish');

			echo '<option value="0" ' . selected($page_id, 0, false) . '>' . __('No single book page', 'lt-unleashed') . '</option>';
			foreach ($pages as $page) {
			  	$option = '<option value="' . $page->ID . '" ' . selected($page_id, $page->ID, false) . '>';
				$option .= $page->post_title;
				$option .= '</option>';
				echo $option;
			}
			echo '<option value="create_new">' . __('Create a new page', 'lt-unleashed') . '</option>';
			?>
		</select><br>
		<em><?php _e('The plugin needs a page to support displaying individual books.', 'lt-unleashed'); ?>
			<?php _e('Choose a page or create a new one.', 'lt-unleashed'); ?>
			<?php _e('If you don\'t, all links to books will go to LibraryThing.', 'lt-unleashed'); ?></em><?php
	}

	function field_book_base() { ?>
		<input type="text" name="libarything-unleashed-options[book_base]"
			value="<?php echo $this->plugin->get_option('book_base'); ?>" /><br>
		<em><?php _e('The base of the URL for single book pages.', 'lt-unleashed'); ?></em><?php
	}

	function field_amzn_locale() { ?>
		<select name="libarything-unleashed-options[amzn_locale]"><?php
			$locale = $this->plugin->get_option('amzn_locale');
			$countryList = array('de', 'com', 'co.uk', 'ca', 'fr', 'co.jp', 'it', 'cn', 'es', 'in', 'com.br');

			foreach ($countryList as $country) {
				echo '<option value="' . $country . '" ' . selected($locale, $country, false) . '>Amazon.' . $country . '</option>';
			} ?>
		</select><br>
		<em><?php _e('The Amazon store from which the book data will be fetched and where your visitors will be sent if they click an Amazon link.', 'lt-unleashed'); ?></em><?php
	}

	function field_affiliate_tag() { ?>
		<input type="text" name="libarything-unleashed-options[affiliate_tag]" class="code"
			value="<?php echo $this->plugin->get_option('affiliate_tag'); ?>" /><br>
		<em><?php _e('Your Amazon affiliate tag.', 'lt-unleashed'); ?>
			<?php _e('If you don\'t specify one, the plugin will use its own and the affiliate commissions, if any, will go the author of the plugin.', 'lt-unleashed'); ?></em><?php
	}

	function field_hide_amzn_button() { ?>
		<label>
			<input type="checkbox" name="libarything-unleashed-options[hide_amzn_button]"
				value="1" <?php checked($this->plugin->get_option('hide_amzn_button')); ?> />&nbsp;<?php
				_e('Check this option to hide the Amazon "Buy Now!" button on the single book page.', 'lt-unleashed'); ?>
		</label><?php
	}

	function field_hide_signature() { ?>
		<label>
			<input type="checkbox" name="libarything-unleashed-options[hide_signature]"
				value="1" <?php checked($this->plugin->get_option('hide_signature')); ?> />&nbsp;<?php
				_e('Check this option to hide the plugin\'s signature.', 'lt-unleashed'); ?>
		</label><?php
	}
}
