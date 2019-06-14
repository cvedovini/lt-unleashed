<div class="books books-cover">
<?php foreach ($books as $id => $book): ?>
    <div class="book">
        <div class="inner-book">
            <a href="<?php echo esc_url(ltu_get_book_url($id, $book)); ?>"
                title="<?php echo esc_attr($book->title); ?> - <?php echo esc_attr($book->author_fl); ?>"><img
                alt="<?php echo esc_attr($book->title); ?> - <?php echo esc_attr($book->author_fl); ?>"
                src="<?php echo ltu_esc_url($book->cover); ?>" width="90"/></a>
        </div>
    </div>
<?php endforeach; ?>
</div>
