<div class="books books-list">
<?php foreach ($books as $id => $book): ?>
    <div class="book">
        <div class="cover"><a href="<?php echo esc_url(ltu_get_book_url($id, $book)); ?>"
            title="<?php echo esc_attr($book->title); ?> - <?php echo esc_attr($book->author_fl); ?>"><img
            alt="<?php echo esc_attr($book->title); ?> - <?php echo esc_attr($book->author_fl); ?>"
            src="<?php echo ltu_esc_url($book->cover); ?>" width="180"/></a>
        </div>
        <div class="book-inner">
            <div class="title">
                <a href="<?php echo esc_url(ltu_get_book_url($id, $book)); ?>"><?php echo esc_html($book->title); ?></a>
                <?php if ($book->rating): ?><span class="rating"><?php echo ltu_star_rating($book->rating); ?></span><?php endif; ?>
                <div class="authors"><?php _e('by', 'lt-unleashed'); ?> <?php echo esc_html($book->author_fl); ?></div>
            </div>
            <?php if ($book->hasreview): ?><div class="review"><?php echo esc_html($book->bookreview); ?></div><?php endif; ?>
            <?php if ($book->tags): ?><div class="tags"><?php _e('Filed under:', 'lt-unleashed'); ?> <?php echo esc_html(implode(', ', $book->tags)); ?></div><?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>