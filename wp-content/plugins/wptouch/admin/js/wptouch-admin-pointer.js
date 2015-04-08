jQuery(document).ready( function($) {
    wptouch_open_pointer(0);
    function wptouch_open_pointer(i) {
        pointer = wptouchpointer.pointers[i];
        console.log( pointer );
        options = jQuery.extend( pointer.options, {
            close: function() {
                jQuery.post( ajaxurl, {
                    pointer: pointer.id,
                    action: 'dismiss-wp-pointer'
                });
            }
        });

        jQuery(pointer.target).pointer( options ).pointer('open');
    }
});