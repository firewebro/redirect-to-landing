<?php
/*
Plugin Name: Redirect to landing
Description: WordPress Plugin for redirecting public website traffic to a landing page.
Author: Flaviu Ghitulescu
Version: 1
*/
$plugin_data = get_plugin_data( __FILE__ );
$plugin_name = $plugin_data['Name'];

add_action( 'admin_menu', 'fwb_redirect_to_landing_menu');
function fwb_redirect_to_landing_menu(){
    add_menu_page( 'Redirect to landing page', 'Redirect to landing', 'manage_options', 'fwb-redirect-to-landing', 'fwb_redirect_to_landing_init');
}

function fwb_redirect_to_landing_init() {
    if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    // display plugin page
    echo '
<div class="wrap">
	<h2>'.$plugin_name.'.</h2>';

    echo '</div>';
}