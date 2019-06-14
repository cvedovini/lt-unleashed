<?php
class LibraryThing_Unleashed_Widget extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'librarything-unleashed', 'description' => __('A widget displaying the books from a LibraryThing collection', 'lt-unleashed'));
		$control_ops = array('id_base' => 'librarything-unleashed');
		parent::__construct('librarything-unleashed', __('LibraryThing Books', 'lt-unleashed'), $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args);
		extract($instance);
		echo $before_widget;

		if (!empty($title))
			echo $before_title . apply_filters('widget_title', $title, $instance, $this->id_base) . $after_title;

		ltu_the_books($librarything_id, $count, $sort_by, $template, $tags);
		echo $after_widget;
	}

	public function update($new_instance, $old_instance) {
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['librarything_id'] = trim($new_instance['librarything_id']);
		$instance['count'] = (int) $new_instance['count'];
		$instance['sort_by'] = $new_instance['sort_by'];
		$instance['template'] = $new_instance['template'];
		$tags = explode(',', $new_instance['tags']);
		$instance['tags'] = array_map('trim', $tags);
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args((array) $instance, array(
				'title' => '',
				'librarything_id' => '',
				'count' => 20,
				'sort_by' => 'entry_REV',
				'template' => 'covers',
				'tags' => array()
		));
		extract($instance);
		?>
<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php
		_e('Title:'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
		name="<?php echo $this->get_field_name('title'); ?>" type="text"
		value="<?php echo $title; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('librarything_id'); ?>"><?php
		_e('LibraryThing username:', 'lt-unleashed'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('librarything_id'); ?>"
		name="<?php echo $this->get_field_name('librarything_id'); ?>" type="text"
		value="<?php echo $librarything_id; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('count'); ?>"><?php
		_e('Maximum number of books to display:', 'lt-unleashed'); ?></label>
	<input id="<?php echo $this->get_field_id('count'); ?>" size="4"
		name="<?php echo $this->get_field_name('count'); ?>" type="text"
		value="<?php echo $count; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id('sort_by'); ?>"><?php
		_e('Sort by:', 'lt-unleashed'); ?></label>
	<select class="widefat" id="<?php echo $this->get_field_id('sort_by'); ?>"
		name="<?php echo $this->get_field_name('sort_by'); ?>">
		<option value="entry_REV" <?php selected($sort_by, 'entry_REV'); ?>><?php
			_e('Date of entry', 'lt-unleashed'); ?></option>
		<option value="random" <?php selected($sort_by, 'random'); ?>><?php
			_e('At random', 'lt-unleashed'); ?></option>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id('template'); ?>"><?php
		_e('Template:', 'lt-unleashed'); ?></label>
	<select class="widefat" id="<?php echo $this->get_field_id('template'); ?>"
		name="<?php echo $this->get_field_name('template'); ?>">
		<option value="covers" <?php selected($template, 'covers'); ?>><?php
			_e('Covers', 'lt-unleashed'); ?></option>
		<option value="list" <?php selected($template, 'list'); ?>><?php
			_e('List', 'lt-unleashed'); ?></option>
	</select><br>
	<em><?php _e('How to display the books', 'lt-unleashed'); ?></em>
</p>
<p>
	<label for="<?php echo $this->get_field_id('tags'); ?>"><?php
		_e('Tags:'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('tags'); ?>"
		name="<?php echo $this->get_field_name('tags'); ?>" type="text"
		value="<?php echo implode(',', $tags); ?>" /><br>
	<em><?php _e('Comma separated list of tags. This is an additive list, "bone, comics" will return books with either tag, not just books that have both tags. Leave empty to consider all the books', 'lt-unleashed'); ?></em>
</p>
<?php
	}
}
