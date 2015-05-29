<?php
/**
 * Plugin Name: RomanCart Ecommerce
 * Plugin URI: http://www.romancart.com/
 * Description: Creates a Store page on your Wordpress website embedded with RomanCart Storefront.
 * Version: 1.0.0
 * Author: RomanCart Development
 * Author URI: http://www.romancart.com
 * License: ROC_LICENSE
 */

/*  Copyright 2015  RomanCart Development  (email : support@romancart.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );


// admin menu, page
add_action('admin_menu', 'ROC_admin');
function ROC_admin() {
	add_options_page('RomanCart Ecommerce', 'RomanCart Ecommerce', 'manage_options', 'plugin', 'ROC_adminpage');
}

function ROC_adminpage() {
	if (!current_user_can('manage_options'))
	{
	wp_die( __('You do not have sufficient permissions to access this page.') );
	}

	$ROC_optName = 'ROC_storeId';
	$ROC_hiddenField = 'ROC_submitHidden';
	$ROC_fieldName = 'ROC_storeId';

	$ROC_optVal = get_option( $ROC_optName );

	if( isset($_POST[ $ROC_hiddenField ]) && $_POST[ $ROC_hiddenField ] == 'Y' ) {
		$ROC_optVal = $_POST[ $ROC_fieldName ];
		update_option( $ROC_optName, $ROC_optVal );
?>
		<div class="updated"><p><strong><?php _e('settings saved.', 'ROC_SettingsMenu' ); ?></strong></p></div>
<?php
	}
	echo '<div class="wrap">';
	echo "<h2>" . __( 'RomanCart Ecommerce Settings', 'ROC_SettingsMenu' ) . "</h2>";
?>
	<form name="ROC_SettingsForm" method="post" action="">
	<input type="hidden" name="<?php echo $ROC_hiddenField; ?>" value="Y">
	<p><?php _e("Store ID:", 'ROC_SettingsMenu' ); ?> 
		<input type="text" name="<?php echo $ROC_fieldName; ?>" value="<?php echo $ROC_optVal; ?>" size="20">
	</p><hr />
	<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</p>
	</form></div>
<?php
}

add_filter('wp_enqueue_scripts', 'ROC_getJsFile');
function ROC_getJsFile() {
	$ROC_thisPage = get_page_by_title( 'Store' );
	if ( is_page($ROC_thisPage->ID) )
		$ROC_storeId = get_option( 'ROC_storeId' );
		$ROC_jsUrl = "http://remote.romancart.com/display.asp?storeid=".$ROC_storeId."&catnav=ok&cart=ok";
		wp_enqueue_script( 'ROC_callJsFile', $ROC_jsUrl);
}

// filter to update the store id
function ROC_writePage($content) {
	$ROC_thisPage = get_page_by_title( 'Store' );
	if ( is_page($ROC_thisPage->ID) )
		$ROC_pageCode = "<div id='ROC_cart'>Cart</div>\n";
		$ROC_pageCode .= "<div id='ROC_catnav'>Category Navigator</div>\n";
		$ROC_pageCode .= "<div id='ROC_display'>Item List</div>\n";
	return $ROC_pageCode;
}
add_filter('the_content', 'ROC_writePage');

// add page "Store" to WP.
register_activation_hook(__FILE__,'ROC_createstore');
function ROC_createstore() {

	global $wpdb;
	$ROC_pageTitle = 'Store';
	$ROC_pageName = 'store';

	delete_option("ROC_pageTitle");
		add_option("ROC_pageTitle", $ROC_pageTitle, '', 'yes');
	delete_option("ROC_pageName");
		add_option("ROC_pageName", $ROC_pageName, '', 'yes');
	delete_option("ROC_pageId");
		add_option("ROC_pageId", '0', '', 'yes');

	$ROC_pageCode = "<div id='ROC_cart'>Cart</div>\n";
	$ROC_pageCode .= "<div id='ROC_catnav'>Category Navigator</div>\n";
	$ROC_pageCode .= "<div id='ROC_display'>Item List</div>\n";

	$ROC_thisPage = get_page_by_title( $ROC_pageTitle );
	$_p = array();
	$_p['post_title'] = $ROC_pageTitle;
	$_p['post_content'] = $ROC_pageCode;
	$_p['post_status'] = 'publish';
	$_p['post_type'] = 'page';
	$_p['comment_status'] = 'closed';
	$_p['ping_status'] = 'closed';
	$_p['post_category'] = array(1); // the default 'Uncatrgorised'

	$ROC_pageId = wp_insert_post( $_p ); // add to db

	delete_option( 'ROC_pageId' );
	add_option( 'ROC_pageId', $ROC_pageId );
}

?>