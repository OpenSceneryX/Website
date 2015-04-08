/* 
 * Javascript for Addthis for Wordpress plugin
 */

jQuery(document).ready(function($) {  
    
    jQuery('#addthis-pubid').focus();
    
    jQuery('#addthis-form').submit(function(){
       if (jQuery('#addthis-pubid').val() === '') {
           jQuery('#addthis-pubid').css('border', '1px solid red');
           jQuery('#addthis-pubid').attr('title', 'Please fill Profile Id');
           return false;
       } 
    });
    
    jQuery('#addthis-pubid').keyup(function(){
        if(jQuery(this).val().length > 0) {
            jQuery(this).css('border', 'none');
            jQuery(this).attr('title', '');
        } else {
            jQuery(this).css('border', '1px solid red');
            jQuery(this).attr('title', 'Please fill Profile Id');
        }
    });
    
    jQuery('#async_load').change(function(){
        
        var syncLoad = jQuery(this).is(':checked')?1:0;
        var data     = {
                            action: "at_async_loading",
                            async_loading: syncLoad
        };
        jQuery('.at-loader').css('visibility', 'visible');
        jQuery.post(ajaxurl, data, function(response){
            jQuery('.at-loader').css('visibility', 'hidden');
        });   
    });
});