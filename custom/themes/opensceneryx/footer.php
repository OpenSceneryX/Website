<?php
/**
 * Template for displaying the footer
 *
 * Contains the closing of the id=main div and all content after
 *
 * @package WordPress
 * @subpackage Twenty_Eleven
 * @since Twenty Eleven 1.0
 */
?>

	</div><!-- #main -->

	<div style="clear:both;">&nbsp;</div>
	<div>
	<?php if (ENVIRONMENT != 'dev' && strpos($wp->request, 'login') === false): ?>
		<!-- Matched Content -->
		<ins class="adsbygoogle"
			style="display:block"
			data-ad-client="ca-pub-5631233433203577"
			data-ad-slot="9063855707"
			data-matched-content-rows-num="3"
			data-matched-content-columns-num="3"
			data-matched-content-ui-type="image_card_stacked"
			data-ad-format="autorelaxed"></ins>
		<script>
			(adsbygoogle = window.adsbygoogle || []).push({});
		</script>
	<?php endif ?>
	</div>
	<div style="clear:both;">&nbsp;</div>

	<footer id="colophon" role="contentinfo">

			<?php
				/*
				 * A sidebar in the footer? Yep. You can customize
				 * your footer with three columns of widgets.
				 */
				if ( ! is_404() ) {
					get_sidebar( 'footer' );
				}
				?>

			<div id="site-generator">
				<div class="footer-logo">
					<form action='https://www.paypal.com/cgi-bin/webscr' method='post'><input type='hidden' name='cmd' value='_s-xclick'><input type='hidden' name='hosted_button_id' value='J3H6VKZD86BJN'><input type='image' src='https://www.paypal.com/en_GB/i/btn/btn_donate_SM.gif' name='submit' class='icon' alt='PayPal - The safer, easier way to pay online.'></form>
				</div>
				<div class="footer-text">OpenSceneryX is free <strong>and will always remain free</strong> for everyone to use.  However, if you do use it, please consider giving a donation to offset the direct costs such as hosting and domain names.</div>
				<div style='clear:both;'>&nbsp;</div>
				<div class="footer-logo">
					<a rel='license' class='nounderline' href='https://creativecommons.org/licenses/by-nc-nd/3.0/' onclick='window.open(this.href);return false;'><img alt='Creative Commons License' class='icon' src='/doc/cc_logo.png' /></a>
				</div>
				<div class="footer-text">The OpenSceneryX library is licensed under a <a rel='license' href='https://creativecommons.org/licenses/by-nc-nd/3.0/' onclick='window.open(this.href);return false;'>Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 License</a>. 'The Work' is defined as the library as a whole and by using the library you signify agreement to these terms. <strong>You must obtain the permission of the author(s) if you wish to distribute individual files from this library for any purpose</strong>, as this constitutes a derivative work, which is forbidden under the licence.</div>
				<div class="footer-social">
					<a rel='external' class='nounderline' href='https://twitter.com/opensceneryx' onclick='window.open(this.href);return false;'><img alt='Follow OpenSceneryX on Twitter' class='icon' src='/extras/twitter_follow.png'/></a>
					<a rel='external' class='nounderline' href='http://www.youtube.com/c/OpenScenery' onclick='window.open(this.href);return false;'><img alt="OpenSceneryX on YouTube" class='icon' src="/extras/yt_logo.png"/></a>
					<a rel='external' class='nounderline' href='https://www.facebook.com/OpenSceneryX/' onclick='window.open(this.href);return false;'><img alt='Follow OpenSceneryX on Facebook' class='icon' src='/extras/facebook_follow.svg'/></a>
				</div>
				<div style='clear:both;'>&nbsp;</div>
			</div>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
