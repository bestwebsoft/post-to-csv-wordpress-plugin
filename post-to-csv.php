<?php
/*
Plugin Name: Post to CSV by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/post-to-csv/
Description: Export WordPress posts to CSV file format easily. Configure data order.
Author: BestWebSoft
Text Domain: post-to-csv
Domain Path: /languages
Version: 1.3.1
Author URI: https://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2017  BestWebSoft  ( https://support.bestwebsoft.com )

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

if ( ! function_exists( 'add_psttcsv_admin_menu' ) ) {
	function add_psttcsv_admin_menu() {
		bws_general_menu();
		$settings = add_submenu_page( 'bws_panel', 'Post to CSV', 'Post to CSV', 'manage_options', "post-to-csv.php", 'psttcsv_settings_page' );
		add_action( 'load-' . $settings, 'psttcsv_add_tabs' );
	}
}

if ( ! function_exists( 'psttcsv_plugins_loaded' ) ) {
	function psttcsv_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'post-to-csv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

if ( ! function_exists ( 'psttcsv_plugin_init' ) ) {
	function psttcsv_plugin_init() {
		global $psttcsv_plugin_info;		
		
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		if ( empty( $psttcsv_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$psttcsv_plugin_info = get_plugin_data( __FILE__ );
		}

		/* Function check if plugin is compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $psttcsv_plugin_info, '3.8', '3.5' );
	}
}

if ( ! function_exists ( 'psttcsv_plugin_admin_init' ) ) {
	function psttcsv_plugin_admin_init() {
		global $bws_plugin_info, $psttcsv_plugin_info;		

		if ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) )
			$bws_plugin_info = array( 'id' => '113', 'version' => $psttcsv_plugin_info["Version"] );

		if ( isset( $_GET['page'] ) && "post-to-csv.php" == $_GET['page'] ) {
			/* Install the option defaults */
			register_psttcsv_settings();

			psttcsv_print_scv();
		}
	}
}

if ( ! function_exists ( 'register_psttcsv_settings' ) ) {
	function register_psttcsv_settings() {
		global $psttcsv_plugin_info;

		$psttcsv_default_options = array(
			'plugin_option_version' 	=> $psttcsv_plugin_info["Version"],
			'suggest_feature_banner'	=> 1
		);

		/* Install the option defaults */
		if ( ! get_option( 'psttcsv_options' ) )
			add_option( 'psttcsv_options', $psttcsv_default_options );
		$psttcsv_options = get_option( 'psttcsv_options' );

		/* Array merge incase this version has added new options */
		if ( ! isset( $psttcsv_options['plugin_option_version'] ) || $psttcsv_options['plugin_option_version'] != $psttcsv_plugin_info["Version"] ) {
			$psttcsv_options = array_merge( $psttcsv_default_options, $psttcsv_options );
			$psttcsv_options['plugin_option_version'] = $psttcsv_plugin_info["Version"];
			update_option( 'psttcsv_options', $psttcsv_options );
		}
	}
}

/* Register settings function */
if ( ! function_exists( 'psttcsv_settings_page' ) ) {
	function psttcsv_settings_page() {
		global $title, $psttcsv_message, $psttcsv_plugin_info;
		$error = $message = $select_all_status = $select_all_post_types = $select_all_fields = '';

		$args=array(
			'public'   => true,
			'_builtin' => false
		);
		$post_types =get_post_types( $args, 'names', 'and' );

		$all_post_types = array_merge( array(
			'post'		 => __( 'Post', 'post-to-csv' ),
			'page' 		 => __( 'Page', 'post-to-csv' ),
			'attachment' => __( 'Attachment', 'post-to-csv' ),
		), $post_types );

		$all_fields = array(
			'post_title' => __( 'Title', 'post-to-csv' ),
			'guid'       => __( 'Guid', 'post-to-csv' ),
			'permalink'  => __( 'Permalink', 'post-to-csv' )
		);

		$all_status = array(
			'publish' 	=> __( 'Publish', 'post-to-csv' ),
			'draft' 	=> __( 'Draft', 'post-to-csv' ),
			'inherit' 	=> __( 'Inherit', 'post-to-csv' ),
			'private' 	=> __( 'Private', 'post-to-csv' )
		);
		$order		= isset( $_POST['psttcsv_order'] ) ? $_POST['psttcsv_order'] : 'post_date';
		$direction	= isset( $_POST['psttcsv_direction'] ) ? $_POST['psttcsv_direction'] : 'desc';
		$status		= isset( $_POST['psttcsv_status'] ) ? $_POST['psttcsv_status'] : array( 'publish' );
		$post_type	= isset( $_POST['psttcsv_post_type'] ) ? $_POST['psttcsv_post_type'] : array();
		$fields		= isset( $_POST['psttcsv_fields'] ) ? $_POST['psttcsv_fields'] : array();

		if ( count( $post_type ) == count( $all_post_types ) )
			$select_all_post_types = ' checked="checked"';

		if ( count( $fields ) == count( $all_fields ) )
			$select_all_fields = ' checked="checked"';

		if ( count( $status ) == count( $all_status ) )
			$select_all_status = ' checked="checked"';

		if ( isset( $_REQUEST['psttcsv_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'psttcsv_nonce_name' ) ) {
			if ( ! isset( $_POST["psttcsv_post_type"] ) )
				$error = __( 'Please, choose at least one Post Type.', 'post-to-csv' );
			if ( ! isset( $_POST["psttcsv_fields"] ) )
				$error .= ' ' . __( 'Please, choose at least one Field.', 'post-to-csv' );
			if ( ! isset( $_POST["psttcsv_status"] ) )
				$error .= ' ' . __( 'Please, choose at least one Post status.', 'post-to-csv' );
		}
		if ( ! empty( $psttcsv_message ) )
			$message = $psttcsv_message; ?>
		<div class="wrap">
			<h1><?php echo $title; ?> <?php _e( 'Settings', 'post-to-csv' ); ?></h1>
			<div class="updated fade below-h2" <?php if ( ! isset( $_REQUEST['psttcsv_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error below-h2" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
			<form id="psttcsv_settings_form" method="post" action="admin.php?page=post-to-csv.php">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Post Type', 'post-to-csv' ); ?></th>
						<td><fieldset>
							<div class="psttcsv_div_select_all" style="display:none;"><label><input class="psttcsv_select_all" type="checkbox" <?php echo $select_all_post_types; ?> /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
							<?php foreach ( $all_post_types as $psttcsv_post_type ) { ?>
								<label><input type="checkbox" name="psttcsv_post_type[]" value="<?php echo $psttcsv_post_type; ?>" <?php if ( in_array( $psttcsv_post_type, $post_type ) ) echo 'checked="checked"'; ?> /> <?php echo ucfirst( $psttcsv_post_type ); ?></label><br />
							<?php } ?>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Fields', 'post-to-csv' ); ?></th>
						<td><fieldset>
							<div class="psttcsv_div_select_all" style="display:none;"><label><input class="psttcsv_select_all" type="checkbox" <?php echo $select_all_fields; ?> /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
							<?php foreach ( $all_fields as $field_key => $field_name ) { ?>
								<label><input type="checkbox" name="psttcsv_fields[]" value="<?php echo $field_key; ?>" <?php if ( in_array( $field_key, $fields ) ) echo 'checked="checked"'; ?> /> <?php echo $field_name; ?></label><br />
							<?php } ?>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Post status', 'post-to-csv' ); ?></th>
						<td><fieldset>
							<div class="psttcsv_div_select_all" style="display:none;"><label><input class="psttcsv_select_all" type="checkbox" <?php echo $select_all_status; ?> /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
								<?php foreach ( $all_status as $status_value => $status_name ) { ?>
									<label><input type="checkbox" name="psttcsv_status[]" value="<?php echo $status_value; ?>" <?php if ( in_array( $status_value, $status ) ) echo 'checked="checked"'; ?> /> <?php echo $status_name; ?></label><br />
								<?php } ?>
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Order By', 'post-to-csv' ); ?></th>
						<td><fieldset>
							<label><input type="radio" name="psttcsv_order" value="post_title" <?php if ( $order == 'post_title' ) echo 'checked="checked"'; ?> /> <?php _e( 'Title', 'post-to-csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_order" value="post_date" <?php if ( $order == 'post_date' ) echo 'checked="checked"'; ?> /> <?php _e( 'Date', 'post-to-csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_order" value="post_author" <?php if ( $order == 'post_author' ) echo 'checked="checked"'; ?> /> <?php _e( 'Author', 'post-to-csv' ); ?></label><br />
						</fieldset></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Order Direction', 'post-to-csv' ); ?></th>
						<td><fieldset>
							<label><input type="radio" name="psttcsv_direction" value="asc" <?php if ( $direction == 'asc' ) echo 'checked="checked"'; ?> /> <?php _e( 'ASC', 'post-to-csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_direction" value="desc" <?php if ( $direction == 'desc' ) echo 'checked="checked"'; ?> /> <?php _e( 'DESC', 'post-to-csv' ); ?></label><br />
						</fieldset></td>
					</tr>
				</table>
				<input type="hidden" name="psttcsv_form_submit" value="submit" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save & Export', 'post-to-csv' ) ?>" />
				</p>
				<?php wp_nonce_field( plugin_basename(__FILE__), 'psttcsv_nonce_name' ); ?>
			</form>
			<?php bws_plugin_reviews_block( $psttcsv_plugin_info['Name'], 'post-to-csv' ); ?>
		</div>
	<?php }
}

if ( ! function_exists( 'psttcsv_print_scv' ) ) {
	function psttcsv_print_scv() {
		global $wpdb, $psttcsv_message;

		if ( isset( $_REQUEST['psttcsv_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'psttcsv_nonce_name' ) ) {

			if ( ! isset( $_POST["psttcsv_fields"] ) || ! isset( $_POST["psttcsv_post_type"] ) || ! isset( $_POST["psttcsv_status"] ) )
				return;

			$filename	=	tempnam( sys_get_temp_dir(), 'csv' );
			$order		=	isset( $_POST['psttcsv_order'] ) ? $_POST['psttcsv_order'] : 'post_date';
			$direction	=	isset( $_POST['psttcsv_direction'] ) ? strtoupper( $_POST['psttcsv_direction'] ) : 'DESC';
			$post_type	=	'';
			$limit		=	1000;
			$start		=	0;

			/* Write column names */
			$colArray = $fieldArray = array();
			$colArray = $_POST["psttcsv_fields"];

			if ( in_array( 'permalink', $colArray ) ) {
				unset( $_POST["psttcsv_fields"][ array_search( 'permalink', $colArray ) ] );
			}

			$status = implode( "', '", $_POST["psttcsv_status"] );
			if ( in_array( 'attachment', $_POST["psttcsv_post_type"] ) ) {
				$status .= "', 'inherit";
			}

			$fields = ( ! empty( $_POST["psttcsv_fields"] ) && is_array( $_POST["psttcsv_fields"] ) ) ? ', `' . implode( '`, `', $_POST["psttcsv_fields"] ) . '`' : '';

			$results = $wpdb->get_results( "
				SELECT `ID`, `post_type`{$fields} 
				FROM $wpdb->posts 
				WHERE `post_type` IN ('" . implode( "', '", $_POST["psttcsv_post_type"] ) . "') 
					AND `post_status` IN ('" . $status . "') 
				ORDER BY `post_type`, `" . $order . "` " . $direction . "
				LIMIT " . $start * $limit . ", " . $limit . "
			", ARRAY_A );
			if ( ! empty( $results ) ) {
				$file = fopen( $filename, "w" );
				fputcsv( $file, $colArray, ';' );
				while ( ! empty( $results ) ) {
					foreach ( $results as $result ) {
						if ( in_array( 'permalink', $colArray ) ) {
							$result['permalink'] = get_permalink( $result['ID'] ) ;
							unset( $result['ID'] );
						} else {
							unset( $result['ID'] );
						}
						if ( $post_type != '' && $post_type != $result['post_type'] )
							fputcsv( $file, array( ), ';' );
						else
							$post_type = $result['post_type'];
						unset( $result['post_type'] );
						fputcsv( $file, $result, ';' );
					}
					$start++;
					$results = $wpdb->get_results( "
						SELECT `ID`, `" . implode( "`, `", $_POST["psttcsv_fields"] ) . "` 
						FROM $wpdb->posts 
						WHERE `post_type` IN ('" . implode( "', '", $_POST["psttcsv_post_type"] ) . "')
						AND `post_status` = 'publish'
						LIMIT " . $start * $limit . ", " . $limit . "
					", ARRAY_A );
				}

				fclose( $file );
				header( "Content-Type: application/csv" );
				header( "Content-Disposition: attachment;Filename=posts_export.csv" );

				/* Send file to browser */
				readfile( $filename );
				unlink( $filename );
				exit();
			} else {
				$psttcsv_message = __( 'No items found.', 'post-to-csv' );
			}
		}
	}
}

if ( ! function_exists( 'psttcsv_admin_js' ) ) {
	function psttcsv_admin_js() {
		if ( isset( $_REQUEST['page'] ) && 'post-to-csv.php' == $_REQUEST['page'] )
			wp_enqueue_script( 'psttcsv_script', plugins_url( 'js/script.js', __FILE__ ) );
	}
}

/* add admin notices */
if ( ! function_exists ( 'psttcsv_admin_notices' ) ) {
	function psttcsv_admin_notices() {
		global $psttcsv_plugin_info;
		if ( isset( $_GET['page'] ) && 'post-to-csv.php' == $_GET['page'] ) {
			bws_plugin_suggest_feature_banner( $psttcsv_plugin_info, 'psttcsv_options', 'post-to-csv' );
		}
	}
}

if ( ! function_exists( 'psttcsv_plugin_action_links' ) ) {
	function psttcsv_plugin_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin ) $this_plugin = plugin_basename( __FILE__ );

			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=post-to-csv.php">' . __( 'Settings', 'post-to-csv' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}
/* End function psttcsv_plugin_action_links */

if ( ! function_exists( 'psttcsv_register_plugin_links' ) ) {
	function psttcsv_register_plugin_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[] = '<a href="admin.php?page=post-to-csv.php">' . __( 'Settings', 'post-to-csv' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com/hc/en-us/sections/200538829" target="_blank">' . __( 'FAQ', 'post-to-csv' ) . '</a>';
			$links[] = '<a href="https://support.bestwebsoft.com">' . __( 'Support', 'post-to-csv' ) . '</a>';
		}
		return $links;
	}
}

/* add help tab  */
if ( ! function_exists( 'psttcsv_add_tabs' ) ) {
	function psttcsv_add_tabs() {
		$screen = get_current_screen();
		$args = array(
			'id' 			=> 'psttcsv',
			'section' 		=> '200538829'
		);
		bws_help_tab( $screen, $args );
	}
}

if ( ! function_exists ( 'psttcsv_plugin_uninstall' ) ) {
	function psttcsv_plugin_uninstall() {
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				delete_option( 'psttcsv_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			delete_option( 'psttcsv_options' );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

add_action( 'admin_menu', 'add_psttcsv_admin_menu' );
add_action( 'init', 'psttcsv_plugin_init' );
add_action( 'admin_init', 'psttcsv_plugin_admin_init' );
add_action( 'plugins_loaded', 'psttcsv_plugins_loaded' );
add_action( 'admin_enqueue_scripts', 'psttcsv_admin_js' );
/* Additional links on the plugin page */
add_filter( 'plugin_action_links', 'psttcsv_plugin_action_links', 10, 2 );
add_filter( 'plugin_row_meta', 'psttcsv_register_plugin_links', 10, 2 );
/* add admin notices */
add_action( 'admin_notices', 'psttcsv_admin_notices' );

register_uninstall_hook( plugin_basename( __FILE__ ), 'psttcsv_plugin_uninstall' );