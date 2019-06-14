<?php if (empty($book)): ?>
<p><?php _e('Cannot find book', 'lt-unleashed'); ?></p>
<?php else: ?>
<table class="book-single">
	<tr>
		<td class="sidecol">
		    <div class="section cover">
		      <a title="<?php echo esc_attr($book->ItemAttrbutes->Title); ?>" href="<?php echo esc_url($book->DetailPageURL); ?>">
				<?php if (isset($book->LargeImage->URL)): ?>
					<img src="<?php echo ltu_esc_url($book->LargeImage->URL); ?>" />
				<?php else: ?>
					<img src="http://www.librarything.com/devkey/<?php echo LIBRARYTHING_API_KEY; ?>/large/isbn/<?php echo $book->ASIN; ?>" />
				<?php endif; ?>
				</a>
			</div>
			<ul class="section links">
		        <li class="link-librarything">
					<a href="http://www.librarything.com/isbn/<?php echo $book->ASIN; ?>"><?php _e('This book on LibraryThing', 'lt-unleashed'); ?></a></li>
		        <li class="link-amazon">
					<a href="<?php echo esc_url($book->DetailPageURL); ?>" rel="nofollow"><?php _e('This book on Amazon', 'lt-unleashed'); ?></a></li>
		        <li class="link-google">
					<a href="http://www.google.com/books?q=isbn:<?php echo $book->ASIN; ?>" rel="nofollow"><?php _e('This book on Google books', 'lt-unleashed'); ?></a></li>
				<li class="link-add">
					<a href="http://www.librarything.com/addbook/<?php echo $book->ASIN; ?>" rel="nofollow"><?php _e('Add this book to your collection', 'lt-unleashed'); ?></a></li>
			</ul>
		</td>
		<td class="maincol">
			<div class="section title">
				<h3><?php echo esc_html($book->ItemAttributes->Title); ?></h3>
				<?php if (isset($book->ItemAttributes->Author)): ?>
				<div class="authors"><?php _e('by', 'lt-unleashed')?> <?php
					if (is_array($book->ItemAttributes->Author)) {
						echo esc_html(implode(', ', $book->ItemAttributes->Author));
					} else {
						echo esc_html($book->ItemAttributes->Author);
					}
				?></div>
				<?php endif; ?>
			</div>

			<?php if (is_array($book->EditorialReviews->EditorialReview)): ?>
			<div class="section review">
				<?php echo $book->EditorialReviews->EditorialReview[0]->Content; ?>
			</div>
			<?php endif; ?>

			<?php if (!LibraryThing_Unleashed_Plugin::get_instance()->get_option('hide_amzn_button')): ?>
			<div class="buy-now-button">
				<a href="<?php echo esc_attr($book->DetailPageURL); ?>" rel="nofollow"><?php _e('Buy this book on Amazon', 'lt-unleashed'); ?></a>
			</div>
			<?php endif; ?>

			<?php if (!empty($similarities)): ?>
			<div class="section similar">
				<h4><?php _e('Other books you may like', 'lt-unleashed'); ?></h4>
				<ul>
		            <?php foreach ($similarities as $other):
		            	$b = new stdClass();
		            	$b->ISBN_cleaned = $other->ASIN;
		            	$url = ltu_get_book_url('-', $b, $other->DetailPageURL);
		            ?>
					<li><a title="<?php echo esc_attr($other->ItemAttributes->Title); ?>" href="<?php echo esc_url($url); ?>">
						<img src="<?php echo ltu_esc_url($other->SmallImage->URL); ?>"  alt="<?php echo esc_attr($other->ItemAttributes->Title); ?>"/>
					</a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>
		</td>
	</tr>
</table>
<?php endif; ?>
