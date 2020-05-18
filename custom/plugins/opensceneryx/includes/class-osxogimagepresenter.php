<?php

use Yoast\WP\SEO\Presenters\Open_Graph\Image_Presenter;

require_once WP_CONTENT_DIR . "/plugins/wordpress-seo/src/presenters/abstract-presenter.php";
require_once WP_CONTENT_DIR . "/plugins/wordpress-seo/src/presenters/abstract-indexable-presenter.php";
require_once WP_CONTENT_DIR . "/plugins/wordpress-seo/src/presenters/open-graph/image-presenter.php";

/**
 * We extend the Yoast Image_Presenter class because although we could use it as-is, there is a bug in that class which
 * means it doesn't calculate the width and height and just outputs the width and height of the default image.
 *
 * If the bug is fixed in future, we can remove this class altogether.
 */
class OSXOGImagePresenter extends Image_Presenter {

    /**
	 * Override the filter function to include our image width and height.
	 *
	 * @param array $image The image.
	 *
	 * @return array The filtered image.
	 */
	protected function filter( $image ) {
		/**
		 * Filter: 'wpseo_opengraph_image' - Allow changing the Open Graph image.
		 *
		 * @api string - The URL of the Open Graph image.
		 *
		 * @param Indexable_Presentation $presentation The presentation of an indexable.
		 */
		$image_url = \trim( \apply_filters( 'wpseo_opengraph_image', $image['url'], $this->presentation ) );
		if ( ! empty( $image_url ) && \is_string( $image_url ) ) {
            $image['url'] = $image_url;
            $image['width'] = 500;
            $image['height'] = 500;
		}

		return $image;
	}
}
