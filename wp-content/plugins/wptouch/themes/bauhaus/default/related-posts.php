<?php if ( function_exists( 'wptouch_related_posts' ) ) { ?>
	<?php if ( wptouch_has_related_posts() ) { ?>
		<div class="related-posts">
			<h3><?php _e( 'Related', 'wptouch-pro' ); ?></h3>
			<ul class="related">
				<?php $related_posts = wptouch_related_posts(); ?>
				<?php foreach( $related_posts as $related_post ) { ?>
				<li<?php if ( isset( $related_post->thumbnail ) ) echo ' class="has-thumb"'; ?>>
					<?php
						if ( isset( $related_post->thumbnail ) && $related_post->thumbnail != '' ) {
							echo $related_post->thumbnail;
						} else {
					?>
							<div class="date-circle">
								<span class="month"><?php wptouch_the_time( 'M' ); ?></span>
								<span class="day"><?php wptouch_the_time( 'j' ); ?></span>
							</div>
					<?php
						}
					?>
					<strong><a href="<?php echo $related_post->link; ?>"><?php echo $related_post->title; ?></a></strong>
					<p><?php echo $related_post->excerpt; ?></p>
				</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
<?php } ?>