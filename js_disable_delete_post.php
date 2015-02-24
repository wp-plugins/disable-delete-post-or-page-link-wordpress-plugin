<?php /**
    
    Plugin Name:   Disable Delete Post or Page
    Plugin URI:    http://reactivedevelopment.net/disable-delete-post-page
    Description:   Allows the administrator to remove the delete post link from a post or page.
    Version:       2.0
 
 *
 
    Author:        Jeremy Selph, Reactive Development LLC
    Author URI:    http://www.reactivedevelopment.net/

    License:       GNU General Public License, v3 (or newer)
    License URI:   http://www.gnu.org/licenses/gpl-3.0.html

 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 

    Note: go here http://reactivedevelopment.net/disable-delete-post-page for documentation 
          or for paid support go here http://www.reactivedevelopment.net/contact/

    *
    
    Activation Instructions
    
    1. Download the disable-delete-post-or-page-link-wordpress-plugin.zip file to your computer.
    2. Unzip the file.
    3. Upload the `disable-delete-post-or-page-link-wordpress-plugin` folder to your `/wp-content/plugins/` directory.
    4. Activate the plugin through the 'Plugins' menu in WordPress.

    *

    Change log
    
    01. updated public function reference                                   ver 0.2 | 01/04/2014

    *
**/

    /**
     
    when we activate the plugin do this

     *
     * @package Disable Delete Post or Page
     * @subpackage install_js_disable_delete_post
     * @since 1.0
    */

    function install_js_disable_delete_post() {         
        
        $currentJSDeletePostOption          = unserialize( get_option( 'jsDisableDeletePost', '' ) );
        if ( empty( $currentJSDeletePostOption ) ){

            add_option( 'jsDisableDeletePost', 'yes', '', 'yes' ); 

        }

    } register_activation_hook( __FILE__, 'install_js_disable_delete_post' );

    /**
     
    remove the delete link from the page/posts list

     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_from_post_list
     * @since 1.0
    */
        
    function js_remove_delete_link_from_post_list( $actions, $post ){
        
        // get this posts jsRemoveDeleteLink meta value
        $thisJSDeleteMetaValue              = get_post_meta( $post->ID, '_jsRemoveDeleteLink', true );
        if( $thisJSDeleteMetaValue == 'yes' || $post->ID == 4 ){ 

            unset( $actions[ 'trash' ] );

        } return $actions;
        
    } add_filter( 'post_row_actions', 'js_remove_delete_link_from_post_list', 10, 2 );
      add_filter( 'page_row_actions', 'js_remove_delete_link_from_post_list', 10, 2 );

    /**
     
    remove the delete link edit post/page page

     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_from_post_edit_page
     * @version 2.0
     * @since 1.0
    */
        
        function js_remove_delete_link_from_post_edit_page(){
        
        /*  when reasearching and looking at wp-admin/includes/meta-boxes.php. There is no way that I can see that will 
            allow us to remove the Move to Trash link in the publish box. So this is a temporarry fix untill we can find 
            a better way to acomplish this feature. */
            
            $currentJSPostID                = intval( $_GET[ 'post' ] );
            if ( $currentJSPostID > 0 ){
            
                // get this posts jsRemoveDeleteLink meta value
                $thisJSDeleteMetaValue      = get_post_meta( $currentJSPostID, '_jsRemoveDeleteLink', true );
                
                // if value == yes then remove link
                if( $thisJSDeleteMetaValue == 'yes' && ( get_post_type( $currentJSPostID ) == 'page'
                 || get_post_type( $currentJSPostID ) == 'post' ) ){

                    ?><style>#delete-action{ display:none; } #js-remove-delete-message{ position:absolute; bottom:11px; }</style>
                      <div id="js-remove-delete-message"><? _e( 'You cannot delete this' ); ?> <? 
                      echo get_post_type( $currentJSPostID ); ?> </div><?
                
                }

            }
            
        } add_action( 'post_submitbox_start', 'js_remove_delete_link_from_post_edit_page' );

    /**
     
    add check box to the screen options page

     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_add_checkBox_to_screen_settings
     * @version 2.0
     * @since 1.0
    */
    
    function js_remove_delete_link_add_checkBox_to_screen_settings( $current, $screen ){  
        
        /*  found this example in the dont-break-the-code-example */
        $currentJSPostID                    = intval( $_GET[ 'post' ] );
        if ( $currentJSPostID > 0 ){
            
            // if this post is a page or a post then add the check box
            if( in_array( $screen->id, array( 'post', 'page' ) ) && ( get_post_type( $currentJSPostID ) == 'page'
             || get_post_type( $currentJSPostID ) == 'post' ) && current_user_can( 'administrator' ) ){
                
                // get this posts jsRemoveDeleteLink meta value
                $thisJSDeleteMetaValue      = get_post_meta( $currentJSPostID, '_jsRemoveDeleteLink', true );
                
                // if value == yes then add checkbox to the screen settings tab             
                $addCheckBoxCode            = '<h5>' . __( 'Remove the ability to delete this' ) . get_post_type( $currentJSPostID ) . '</h5>';
                
                if ( $thisJSDeleteMetaValue == 'yes' ){ $checked = ' checked="checked" '; }
                $addCheckBoxCode           .= '<input type="checkbox" id="jsRemoveDeleteLink" name="jsRemoveDeleteLink"' . $checked . '/>'
                                           .  '<label for="jsRemoveDeleteLink"> '
                                               .  __( 'Remove Trash Link' )
                                           .  '</label> ';

                return $addCheckBoxCode;
                
            } else { return; } 
        
        }
        
    } add_filter( 'screen_settings', 'js_remove_delete_link_add_checkBox_to_screen_settings', 10, 2 );

    /**
     
    add jquery function to admin head to save the remove delete link meta for this post

     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_add_jquery_to_head
     * @version 2.0
     * @since 1.0
    */
    
    function js_remove_delete_link_add_jquery_to_head(){
    
        /* add jquery to the head in-order to save the checkbox option */
        $currentJSPostID                    = intval( $_GET[ 'post' ] );
        if ( $currentJSPostID > 0 && current_user_can( 'administrator' ) ){ ?>

            <script type="text/javascript" language="javascript">
                
                jQuery( document ).ready( function(){
                    
                    // when the checkbox is clicked save the meta option for this post
                    jQuery( "#jsRemoveDeleteLink" ).click( function() {                            
                        
                        var isJSDeleteisChecked = "no";
                        if ( jQuery( "#jsRemoveDeleteLink" ).attr( "checked" ) ){ isJSDeleteisChecked = "yes"; }                            
                        jQuery.post( ajaxurl, 

                            "action=jsRemoveDeleteLink_save&post=<?php echo $currentJSPostID; ?>&jsRemoveDeleteLink=" + isJSDeleteisChecked, 
                            
                            function(response) { // hide or show trash link 
                                
                                if ( response == "yes" ){ // hide delete link
                                    
                                    jQuery( "#delete-action" ).hide( function() {
                                        
                                        var addThisAboveDelete  = '<div id="js-remove-delete-message" style="position:absolute; bottom:11px;">';
                                            addThisAboveDelete += "<? _e( 'You cannot delete this' ); ?> <? echo get_post_type( $currentJSPostID ); ?> </div>";
                                        jQuery( addThisAboveDelete ).prependTo( "#major-publishing-actions" );

                                    });

                                } else if ( response == "no" ){ // show delete link
                                    
                                    jQuery( "#js-remove-delete-message" ).remove(); 
                                    jQuery( "#delete-action" ).show(); 

                                } 

                            }); 

                        });

                    });

                </script> <?
    
        }
        
    } add_action( 'admin_head', 'js_remove_delete_link_add_jquery_to_head' );

    /**
     
    add ajax call to wp in order to save the remove delete post link

     *
     * @package Disable Delete Post or Page
     * @subpackage js_remove_delete_link_add_ajax_call_to_wp
     * @version 2.0
     * @since 1.0
    */
    
    function js_remove_delete_link_add_ajax_call_to_wp(){       
        
        /*  found this example in the dont-break-the-code-example */
        $jsRemoveDeleteLink                 = $_POST[ 'jsRemoveDeleteLink' ];
        $currentJSPostID                    = intval( $_GET[ 'post' ] );

        if( !empty( $currentJSPostID ) && $jsRemoveDeleteLink !== NULL ) {
            
            update_post_meta( $currentJSPostID, '_jsRemoveDeleteLink', $jsRemoveDeleteLink );
            echo $jsRemoveDeleteLink;

        } else { echo $jsRemoveDeleteLink; } exit;
        
    } add_action( 'wp_ajax_jsRemoveDeleteLink_save', 'js_remove_delete_link_add_ajax_call_to_wp' );

    /**
     
    when we deactivate the plugin do this

     *
     * @package Disable Delete Post or Page
     * @subpackage remove_js_disable_delete_post
     * @since 1.0
    */ 
        
    function remove_js_disable_delete_post() { 

        delete_option( 'jsDisableDeletePost' ); 

    } register_deactivation_hook( __FILE__, 'remove_js_disable_delete_post' );

    /**
    
    End code
    
    */

?>