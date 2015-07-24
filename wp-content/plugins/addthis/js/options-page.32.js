/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2015 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

jQuery(document).ready(function($) {
    $( "#config-error" ).hide();
    $( "#share-error" ).hide();
    $( "#tabs" ).tabs();
    $( "#Card-above-post" ).tabs();
    $( "#Card-below-post" ).tabs();
    $( "#Card-side-sharing" ).tabs();

    var thickDims, tbWidth, tbHeight, img = '';

    thickDims = function() {
        var tbWindow = $('#TB_window'), H = $(window).height(), W = $(window).width(), w, h;

        w = (tbWidth && tbWidth < W - 90) ? tbWidth : W - 90;
        h = (tbHeight && tbHeight < H - 60) ? tbHeight : H - 60;
        if ( tbWindow.size() ) {
            tbWindow.width(w).height(h);
            $('#TB_iframeContent').width(w).height(h - 27);
            tbWindow.css({'margin-left': '-' + parseInt((w / 2),10) + 'px'});
            if ( typeof document.body.style.maxWidth != 'undefined' )
                tbWindow.css({'top':'30px','margin-top':'0'});
        }
    };

    $('#addthis_rating_thank_you').hide()
    switch($('#addthis_rate_us').val()) {
        case 'dislike':
            $('#addthis_like_us_answers').hide();
            $('#addthis_dislike').show();
            $('#addthis_like').hide();
            break;
        case 'like':
            $('#addthis_like_us_answers').hide();
            $('#addthis_dislike').hide();
            $('#addthis_like').show();
            break;
        case 'will not rate':
        case 'rated':
            $('#addthis_do_you_like_us').hide()
            break;
        default:
            $('#addthis_dislike').hide();
            $('#addthis_like').hide();
    }

    $('#addthis_dislike_confirm').click(function() {
        $('#addthis_like_us_answers').hide();
        $('#addthis_dislike').show();
        $('#addthis_rate_us').val('dislike')
    });
    $('#addthis_like_confirm').click(function() {
        $('#addthis_like_us_answers').hide();
        $('#addthis_like').show();
        $('#addthis_rate_us').val('like')
    });
    $('#addthis_not_rating').click(function() {
        $('#addthis_do_you_like_us').hide()
        $('#addthis_rate_us').val('will not rate')
    });
    $('#addthis_rating').click(function() {
        $('#addthis_rating_thank_you').show()
        $('#addthis_rate_us').val('rated')
    });

    $('a.thickbox-preview').click( function() {

        var previewLink = this;

        var values = {};
        $.each($('#addthis-settings').serializeArray(), function(i, field) {

            var thisName = field.name
            if (thisName.indexOf("addthis_settings[") != -1 )
            {
                thisName = thisName.replace("addthis_settings[", '');
                thisName = thisName.replace("]", '');
            }

            values[thisName] = field.value;
        });

        var stuff = $.param(values, true);

        var data = {
            action: 'at_save_transient',
            value : stuff
        };


        jQuery.post(ajaxurl, data, function(response) {

            // Fix for WP 2.9's version of lightbox
            if ( typeof tb_click != 'undefined' &&  $.isFunction(tb_click.call))
            {
               tb_click.call(previewLink);
            }
            var href = $(previewLink).attr('href');
            var link = '';


        if ( tbWidth = href.match(/&tbWidth=[0-9]+/) )
            tbWidth = parseInt(tbWidth[0].replace(/[^0-9]+/g, ''), 10);
        else
            tbWidth = $(window).width() - 90;

        if ( tbHeight = href.match(/&tbHeight=[0-9]+/) )
            tbHeight = parseInt(tbHeight[0].replace(/[^0-9]+/g, ''), 10);
        else
            tbHeight = $(window).height() - 60;

        $('#TB_title').css({'background-color':'#222','color':'#dfdfdf'});
        $('#TB_closeAjaxWindow').css({'float':'left'});
        $('#TB_ajaxWindowTitle').css({'float':'right'}).html(link);
        $('#TB_iframeContent').width('100%');
        thickDims();

        });
        return false;
    });

    var aboveCustom = $('#above_custom_button');
    var aboveCustomShow = function(){
        if ( aboveCustom.is(':checked'))
        {
            $('.above_option_custom').removeClass('hidden');
        }
        else
        {
            $('.above_option_custom').addClass('hidden');
        }
    };

    var belowCustom = $('#below_custom_button');
    var belowCustomShow = function(){
        if ( belowCustom.is(':checked'))
        {
            $('.below_option_custom').removeClass('hidden');
        }
        else
        {
            $('.below_option_custom').addClass('hidden');
        }
    };

    var aboveCustomString = $('#above_custom_string');
    var aboveCustomStringShow = function(){
        if ( aboveCustomString.is(':checked'))
        {
            $('.above_custom_string_input').removeClass('hidden');
        }
        else
        {
            $('.above_custom_string_input').addClass('hidden');
        }
    };

    var belowCustomString = $('#below_custom_string');
    var belowCustomStringShow = function(){
        if ( belowCustomString.is(':checked'))
        {
            $('.below_custom_string_input').removeClass('hidden');
        }
        else
        {
            $('.below_custom_string_input').addClass('hidden');
        }
    };

    aboveCustomShow();
    belowCustomShow();
    aboveCustomStringShow();
    belowCustomStringShow();

    $('input[name="addthis_settings[above]"]').change( function(){aboveCustomShow(); aboveCustomStringShow();} );
    $('input[name="addthis_settings[below]"]').change( function(){belowCustomStringShow();} );

    /**
     * Hide Theming and branding options when user selects version 3.0 or above
     */
    var ATVERSION_250 = 250;
    var AT_VERSION_300 = 300;
    var MANUAL_UPDATE = -1;
    var AUTO_UPDATE = 0;
    var REVERTED = 1;
    var atVersionUpdateStatus = $("#addthis_atversion_update_status").val();
    if (atVersionUpdateStatus == REVERTED) {
        $(".classicFeature").show();
    } else {
        $(".classicFeature").hide();
    }

    /**
     * Revert to older version after the user upgrades
     */
    $(".addthis-revert-atversion").click(function(){
       $("#addthis_atversion_update_status").val(REVERTED);
       $("#addthis_atversion_hidden").val(ATVERSION_250);
       $(this).closest("form").submit();
       return false;
    });
   /**
    * Update to a newer version
    */
   $(".addthis-update-atversion").click(function(){
       $("#addthis_atversion_update_status").val(MANUAL_UPDATE);
       $("#addthis_atversion_hidden").val(AT_VERSION_300);
       $(this).closest("form").submit();
       return false;
   });

   var addthis_credential_validation_status = $("#addthis_credential_validation_status");
   var addthis_validation_message = $("#addthis-credential-validation-message");
   var addthis_profile_validation_message = $("#addthis-profile-validation-message");
   //Validate the Addthis credentials
   window.skipValidationInternalError = false;
   function validate_addthis_credentials() {
        $.ajax(
            {"url" : addthis_option_params.wp_ajax_url,
             "type" : "post",
             "data" : {"action" : addthis_option_params.addthis_validate_action,
                      "addthis_profile" : $("#addthis_profile").val()
                  },
             "dataType" : "json",
             "beforeSend" : function() {
                 $(".addthis-admin-loader").show();
                 addthis_validation_message.html("").next().hide();
                 addthis_profile_validation_message.html("").next().hide();
             },
             "success": function(data) {
                 addthis_validation_message.show();
                 addthis_profile_validation_message.show();

                 if (data.credentialmessage == "error" || (data.profileerror == "false" && data.credentialerror == "false")) {
                     if (data.credentialmessage != "error") {
                         addthis_credential_validation_status.val(1);
                     } else {
                         window.skipValidationInternalError = true;
                     }
                     $("#addthis-settings").submit();
                 } else {
                     addthis_validation_message.html(data.credentialmessage);
                     addthis_profile_validation_message.html(data.profilemessage);
                     if (data.profilemessage != "") {
                         $('html, body').animate({"scrollTop":0}, 'slow');
                     }
                 }

             },
             "complete" :function(data) {
                 $(".addthis-admin-loader").hide();
             },
             "error" : function(jqXHR, textStatus, errorThrown) {
                 console.log(textStatus, errorThrown);
             }
         });
    }

    $("#addthis_profile").change(function(){
       addthis_credential_validation_status.val(0);
       if($.trim($("#addthis_profile").val()) == "") {
            addthis_profile_validation_message.next().hide();
       }
    });

    $('#addthis_config_json').focusout(function() {
        var error = 0;
        if ($('#addthis_config_json').val() != " ") {
            try {
                var addthis_config_json = jQuery.parseJSON($('#addthis_config_json').val());
            }
                catch (e) {
                    $('#config-error').show();
                    error = 1;
                }
        }
        if (error == 0) {
            $('#config-error').hide();
            return true;
        } else {
            $('#config-error').show();
            return false;
        }
    });

    $('#addthis_share_json').focusout(function() {
        var error = 0;
        if ($('#addthis_share_json').val() != " ") {
            try {
                var addthis_share_json = jQuery.parseJSON($('#addthis_share_json').val());
            }
            catch (e) {
                $('#share-error').show();
                error = 1;
            }
        }
        if (error == 0) {
            $('#share-error').hide();
            return true;
        } else {
            $('#share-error').show();
            return false;
        }
    });

    $('.addthis-submit-button').click(function() {
        $('#config-error').hide();
        $('#share-error').hide();
        var error = 0;
        if ($('#addthis-config-json').val() != " ") {
            try {
                var addthis_config_json = jQuery.parseJSON($('#addthis-config-json').val());
            }
            catch (e) {
                $('#config-error').show();
                error = 1;
            }
        }
        if ($('#addthis_share_json').val() != " ") {
            try {
                var addthis_share_json = jQuery.parseJSON($('#addthis_share_json').val());
            }
            catch (e) {
                $('#share-error').show();
                error = 1;
            }
        }
        if (error == 0) {
            return true;
        } else {
            return false;
        }
     });


  //preview box
    function rewriteServices(posn) {
        var services = $('#'+posn+'-chosen-list').val();
        var service = services.split(', ');
        var i;
        var newservice = '';
        for (i = 0; i < (service.length); ++i) {
            if(service[i] == 'linkedin') {
                newservice += 'linkedin_counter, ';
            }
            else if(service[i] == 'facebook') {
                newservice += 'facebook_like, ';
            }
            else if(service[i] == 'twitter') {
                newservice += 'tweet, ';
            }
            else if(service[i] == 'pinterest_share') {
                newservice += 'pinterest_pinit, ';
            }
            else if(service[i] == 'hyves') {
                newservice += 'hyves_respect, ';
            }
            else if(service[i] == 'google_plusone_share') {
                newservice += 'google_plusone, ';
            }
            else if(service[i] == 'counter' || service[i] == 'compact') {
                newservice += service[i]+', ';
            }
        }
        var newservices = newservice.slice(0,-2);
        return newservices;
    }

    function revertServices(posn) {
        var services = $('#'+posn+'-chosen-list').val();
        var service = services.split(', ');
        var i;
        var newservice = '';
        for (i = 0; i < (service.length); ++i) {
            if(service[i] == 'facebook_like') {
                newservice += 'facebook, ';
            }
            else if(service[i] == 'linkedin_counter') {
                newservice += 'linkedin, ';
            }
            else if(service[i] == 'hyves_respect') {
                newservice += 'hyves, ';
            }
            else if(service[i] == 'google_plusone') {
                newservice += 'google_plusone_share, ';
            }
            else if(service[i] == 'tweet') {
                newservice += 'twitter, ';
            }
            else if(service[i] == 'pinterest_pinit') {
                newservice += 'pinterest_share, ';
            }
            else {
                newservice += service[i]+', ';
            }
        }
        var newservices = newservice.slice(0,-2);
        return newservices;
    }

    if($('#large_toolbox_above').is(':checked')) {
        $('.above_button_set').show();
    } else if($('#fb_tw_p1_sc_above').is(':checked')) {
        $('.above_button_set').show();
    } else if($('#small_toolbox_above').is(':checked')) {
        $('.above_button_set').show();
    } else if($('#button_above').is(':checked')) {
        $('.above_button_set').hide();
    } else if($('#above_custom_string').is(':checked')) {
        $('.above_button_set').hide();
    }

    if($('#large_toolbox_below').is(':checked')) {
        $('.below_button_set').show();
    } else if($('#fb_tw_p1_sc_below').is(':checked')) {
        $('.below_button_set').show();
    } else if($('#small_toolbox_below').is(':checked')) {
        $('.below_button_set').show();
    } else if($('#button_below').is(':checked')) {
        $('.below_button_set').hide();
    } else if($('#below_custom_string').is(':checked')) {
        $('.below_button_set').hide();
    }

    $("#large_toolbox_above").click( function() {
        if($('#above-chosen-list').val() != '') {
            var newserv = revertServices('above');
            $('#above-chosen-list').val(newserv);
        }
        $('.above_button_set').show();
    });

    $("#large_toolbox_below").click( function() {
        if($('#below-chosen-list').val() != '') {
            var newserv = revertServices('below');
            $('#below-chosen-list').val(newserv);
        }
        $('.below_button_set').show();
    });

    $("#fb_tw_p1_sc_above").click( function() {
        if($('#above-chosen-list').val() != '') {
            var newserv = rewriteServices('above');
            $('#above-chosen-list').val(newserv);
        }
        $('.above_button_set').show();
    });

    $("#fb_tw_p1_sc_below").click( function() {
        if($('#below-chosen-list').val() != '') {
            var newserv = rewriteServices('below');
            $('#below-chosen-list').val(newserv);
        }
        $('.below_button_set').show();
    });

    $("#small_toolbox_above").click( function() {
        if($('#above-chosen-list').val() != '') {
            var newserv = revertServices('above');
            $('#above-chosen-list').val(newserv);
        }
        $('.above_button_set').show();
    });

    $("#small_toolbox_below").click( function() {
        if($('#below-chosen-list').val() != '') {
            var newserv = revertServices('below');
            $('#below-chosen-list').val(newserv);
        }
        $('.below_button_set').show();
    });

    $("#button_above").click( function() {
        $('.above_button_set').show();
    });

    $("#above_custom_string").click( function() {
        if($(this).is(':checked')){
            $('.above_button_set').hide();
        } else {
            $('.above_button_set').show();
        }
    });

    $("#button_below").click( function() {
        $('.below_button_set').show();
    });

    $("#below_custom_string").click( function() {
        if($(this).is(':checked')){
            $('.below_button_set').hide();
        } else {
            $('.below_button_set').show();
        }
    });

    $('.addthis-submit-button').click(function() {
        if($('#above-disable-smart-sharing').is(':checked')) {
            if($('#button_above').is(':checked')) {
                $('#above-chosen-list').val('');
            } else {
                var list = [];
                $('.above-smart-sharing-container .selected-services .ui-sortable').each(function(){
                    var service = '';
                    $(this).find('li').each(function(){
                        if($(this).hasClass('enabled')) {
                            list.push($(this).attr('data-service'));
                            if($(this).attr('data-service') == 'compact') {
                                list.push('counter');
                            }
                        }
                    });
                });
                var aboveservices = list.join(', ');
                $('#above-chosen-list').val(aboveservices);
            }
        }
        if($('#button_above').is(':checked')) {
            $('#above-chosen-list').val('');
        }

        if($('#below-disable-smart-sharing').is(':checked')) {
            if($('#button_below').is(':checked')) {
                $('#below-chosen-list').val('');
            } else {
                var list = [];
                $('.below-smart-sharing-container .selected-services .ui-sortable').each(function(){
                    var service = '';
                    $(this).find('li').each(function(){
                        if($(this).hasClass('enabled')) {
                            list.push($(this).attr('data-service'));
                            if($(this).attr('data-service') == 'compact') {
                                list.push('counter');
                            }
                        }
                    });
                });
                var belowservices = list.join(', ');
                $('#below-chosen-list').val(belowservices);
            }
        }
        if($('#button_below').is(':checked')) {
            $('#below-chosen-list').val('');
        }

        if($("#tabs .ui-state-active > a").html() == "Mode") {
            $( "#tabs" ).tabs("option", "active", 0);
        }

    });

    var dataContent = '';
    var dataTitle = '';
    var innerContent = '';
    var left = 0;
    var top = 0;
    var popoverHeight = 0;
    var parent;
    var me;
    $('.row-right a').mouseover(function(){
        me = $(this);
        parent = $(me).parent();

        dataContent = $(parent).attr('data-content');
        dataTitle = $(parent).attr('data-original-title');
        innerContent = "<div class='popover fade right in' style='display: block;'><div class='arrow'></div><h3 class='popover-title'>";
        innerContent =  innerContent + dataTitle;
        innerContent = innerContent + "</h3><div class='popover-content'>";
        innerContent = innerContent + dataContent;
        innerContent = innerContent + "</div></div>";
        $(parent).append(innerContent);

        popoverHeight = $(parent).find('.popover').height();
        left = $(me).position().left + 15;
        top = $(me).position().top - (popoverHeight/2) + 8;

        $(parent).find('.popover').css({
            'left': left+'px',
            'top': top+'px'
        });
    });
    $('.row-right a').mouseout(function(){
        $('.popover').remove();
    });

    //Keep the user in current tab
    $(function() {
        var index = 'key';
        var dataStore = window.sessionStorage;
        try {
            var oldIndex = dataStore.getItem(index);
        } catch(e) {
            var oldIndex = 0;
        }
        $('#tabs').tabs({
            active : oldIndex,
            activate : function( event, ui ){
                var newIndex = ui.newTab.parent().children().index(ui.newTab);
                dataStore.setItem( index, newIndex ) //  Set future value
            }
        });
    });

    //Setting checkbox checked
    $('.addthis-switch').click(function()
    {
        $(this).toggleClass("addthis-switchOn");
        var id = $(this).attr('id').replace('_switch','');
        if($('#'+id).attr('checked')){
            $('#'+id).prop('checked', false);
            $("."+id+'_overlay').css('pointer-events', 'none');
            $("."+id+'_overlay').css('opacity', '0.5');
        } else {
           $('#'+id).prop('checked', true);
           $("."+id+'_overlay').css('opacity', '1');
           $("."+id+'_overlay').css('pointer-events', 'auto');
        }
    });

    //Default overlay - disabled tools
    $('.addthis-toggle-cb').each(function() {
        var id = $(this).attr('id');
        if(!$('#'+id).attr('checked')){
            $("."+id+'_overlay').css('pointer-events', 'none');
            $("."+id+'_overlay').css('opacity', '0.5');
        }
    });

    //Show sidebar preview based on the user selected
    $('[id^=sbpreview_]').hide();
    var current_sbpreview_id = "sbpreview_"+$('#addthis_sidebar_theme').val();
    $('#'+current_sbpreview_id).show();
    $('#addthis_sidebar_theme').on('change', function() {
        var preview_id = "sbpreview_"+$('#addthis_sidebar_theme option:selected').val();
        $('[id^=sbpreview_]').hide();
        $('#'+preview_id).show();
    });

});

