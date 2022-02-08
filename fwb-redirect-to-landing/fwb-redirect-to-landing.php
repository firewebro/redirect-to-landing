<?php
/*
Plugin Name: Redirect to landing
Description: WordPress Plugin for redirecting public website traffic to a landing page.
Author: FireWeb
AuthorURI: https://fireweb.ro
Version: 1.0
*/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// redirect function, pluggable
if( !function_exists("fwb_redirect_to_landing") ){
	function fwb_redirect_to_landing(){
        $enableredirect_option = get_option('fwb_redirect_to_landing_enable', false);
        $redirecturl_option = get_option('fwb_redirect_to_landing_url', false);
        $redirecttype_option = get_option( 'fwb_redirect_type', 302 );
        $rolebypass_option = get_option('fwb_redirect_to_landing_rolebypass', false);
        // verify redirecturl against post_name to avoit a redirection loop, possible more bugs here
        if( !strpos($redirecturl_option, get_post_field('post_name', get_post())) ) {
            if( $enableredirect_option && strlen($redirecturl_option) > 10 ) {
                // admins are not redirected
                if( !fwb_redirect_to_landing_check_role( $rolebypass_option ) ){
                    // do the redirect 
                    header( "Location: ". $redirecturl_option, true, $redirecttype_option );
                    // exit to stop execution after header()
                    die();
                }
            }
        }
    }
}

function fwb_redirect_to_landing_check_role($roles, $user_id = null) {
    if ($user_id) {
        $user = get_userdata($user_id);
    } else {
        $user = wp_get_current_user();
    }
    if (empty($user)) {
        return false;
    }
    foreach ($user->roles as $role) {
        if (in_array($role, $roles)) {
            return true;
        }
    }
    return false;
}

// hook redirect
add_action( 'wp', 'fwb_redirect_to_landing' );

if ( is_admin() ) {
    if( ! function_exists('get_plugin_data') ){
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
}

add_action( 'admin_menu', 'fwb_redirect_to_landing_menu' );
function fwb_redirect_to_landing_menu(){
    add_menu_page( 'Redirect to landing page', 'Redirect to landing', 'manage_options', 'fwb-redirect-to-landing', 'fwb_redirect_to_landing_init' );
}

function fwb_redirect_to_landing_init() {
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    $plugin_data = get_plugin_data( __FILE__ );

    global $wp_roles;

    // print it to the screen
    echo '<pre>' . print_r( $_POST['rolebypass'], true ) . '</pre>';



    // display plugin page
    echo '
    <div class="wrap">
	<h1>'.$plugin_data['Name'].'</h1>
    <div class="description">'.$plugin_data['Description'].'</div>';

    // Save plugin data to options
    if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
        delete_option('fwb_redirect_to_landing_enable' );
        if($_POST['enableredirect'] == 1){
            add_option('fwb_redirect_to_landing_enable', 1, '', 'yes' );
        }
        if( isset($_POST['redirecturl']) ){
            delete_option('fwb_redirect_to_landing_url');
            add_option('fwb_redirect_to_landing_url', esc_url_raw($_POST['redirecturl']), '', 'yes' );
        }
        if( isset($_POST['rolebypass']) ){
            delete_option('fwb_redirect_to_landing_rolebypass');
            add_option('fwb_redirect_to_landing_rolebypass', $_POST['rolebypass'], '', 'no' );
        }
        //redirect type option
        if( isset($_POST['redirecttype']) ){
            delete_option('fwb_redirect_type');
            add_option('fwb_redirect_type', $_POST['redirecttype'], '', 'yes' );
        }
    }

    $enableredirect_option = get_option('fwb_redirect_to_landing_enable', false);
    $redirecturl_option = get_option('fwb_redirect_to_landing_url', false);
    $rolebypass_option = get_option('fwb_redirect_to_landing_rolebypass', false);
    $redirecttype_option = get_option( 'fwb_redirect_type', false );

     echo '<pre>' . print_r( $rolebypass_option, true ) . '</pre>';

    echo '<form method="post" action=""> 
    <table class="form-table">
    
    <tr>
    <th scope="row"><label for="enableredirect">Enable Redirect</label></th>
    <td><input name="enableredirect" type="checkbox" id="enableredirect" value="1"'.($enableredirect_option ? ' checked' : false).'/> Check to enable.</td>
    </tr>

    <tr>
    <th><label for="redirecttype"> Redirection Type</label></th>
    <td>
        <select name="redirecttype">
            <option value="302"' . selected($redirecttype_option, 302, false) . '>Temporary redirect (302)</option>
            <option value="301"' . selected($redirecttype_option, 301, false) . '>Permanent redirect (301)</option>
        </select>
    </td>
    </tr>

    <tr>
    <th><label for="rolebypass"> Role Bypass</label></th>
    <td>
        <select name="rolebypass[]" multiple>';
            foreach($wp_roles->roles as $role => $roledetails) {
                echo '<option value="'.$role.'"' . selected(in_array($role, $rolebypass_option), true, false) . '>'.$role.'</option>';
            }
    echo '    </select>
    </td>
    </tr>
    
    <tr>
    <th scope="row"><label for="redirecturl">Redirect Url</label></th>
    <td><input name="redirecturl" type="text" id="redirecturl" aria-describedby="tagline-description" value="'.($redirecturl_option ? $redirecturl_option : false).'" class="regular-text" />
    <p class="description" id="tagline-description">https://example.com/landing</p></td>
    </tr>
    </table>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"  /></p>
    </form>';

    echo '</div>';
}