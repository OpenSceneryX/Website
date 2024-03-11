<?php

/**
 * Description of OSXDecal
 */
class OSXDecal extends OSXLibraryItem {
    const FILENAME_ROOT = "decal";
    const FILENAME_EXT= "dcl";

    function __construct($path, $url) {
        parent::__construct($path, $url, 'decal');
    }

    function enqueueScript() {
        parent::enqueueScript();

        // Inject the slick slider code. Can't do this in the getHTML function below because that's too late
        $slickScript = '<script type="text/javascript">
            $(document).ready(function(){
                $(".slick-screenshots").slick({
                    autoplay: true,
                    autoplaySpeed: 2000,
                    swipeToSlide: true,
                    dots: true
                });
            });
            </script>';

        wp_enqueue_script('slick', plugin_dir_url(__FILE__) . 'slick/slick.min.js', array(), false, true);
        wp_add_inline_script('slick', $slickScript, 'after');
    }

    protected function parse() {
        parent::parse();
    }

    protected function getTypeSpecificHTML() {
        $result = "";

        return $result;
    }

    protected function getTypeExtension() {
        return "." . self::FILENAME_EXT;
    }
}
