<?php
/*
Plugin Name: Post To CSV
Plugin URI:  http://bestwebsoft.com/plugin/
Description: The plugin Post To CSV allows to export posts of any types to a csv file.
Author: BestWebSoft
Version: 1.0
Author URI: http://bestwebsoft.com/
License: GPLv2 or later
*/

/*  © Copyright 2014  BestWebSoft  ( http://support.bestwebsoft.com )

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

require_once( dirname( __FILE__ ) . '/bws_menu/bws_menu.php' );

if ( ! function_exists( 'add_psttcsv_admin_menu' ) ) {
	function add_psttcsv_admin_menu() {
		add_menu_page( 'BWS Plugins', 'BWS Plugins', 'manage_options', 'bws_plugins', 'bws_add_menu_render',  plugins_url( "images/px.png", __FILE__ ), 1001); 
		add_submenu_page( 'bws_plugins', __( 'Post To CSV', 'post_to_csv' ), __( 'Post To CSV', 'post_to_csv' ), 'manage_options', "post-to-csv.php", 'psttcsv_settings_page' );

		//call register settings function
		//add_action( 'admin_init', 'register_psttcsv_settings' );
	}
}

// register settings function
if ( ! function_exists( 'psttcsv_settings_page' ) ) {
	function psttcsv_settings_page() { 
		global $title, $psttcsv_message;
		$error = $message = '';
		$psttcsv_all_post_types = get_post_types( array( 'public' => true ), 'names' );	
		$order		= isset( $_POST['psttcsv_order'] ) ? $_POST['psttcsv_order'] : 'post_date';
		$direction	= isset( $_POST['psttcsv_direction'] ) ? $_POST['psttcsv_direction'] : 'desc';
		$status		= isset( $_POST['psttcsv_status'] ) ? $_POST['psttcsv_status'] : 'publish';
		$post_type	= isset( $_POST['psttcsv_post_type'] ) ? $_POST['psttcsv_post_type'] : array();
		$fields		= isset( $_POST['psttcsv_fields'] ) ? $_POST['psttcsv_fields'] : array();

		if ( isset( $_REQUEST['psttcsv_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'psttcsv_nonce_name' ) ) {
			if ( ! isset( $_POST["psttcsv_post_type"] ) )
				$error = __( 'Please, choose at least one Post Type.', 'post_to_csv' );
			if ( ! isset( $_POST["psttcsv_fields"] ) )
				$error .= ' ' . __( 'Please, choose at least one Field.', 'post_to_csv' );
		}
		if ( ! empty( $psttcsv_message ) )
			$message = $psttcsv_message; ?>
		<div class="wrap">
			<div class="icon32 icon32-bws" id="icon-options-general"></div>
			<h2><?php echo $title; ?> <?php _e( 'Settings', 'post_to_csv' ); ?></h2>
			<div class="updated fade" <?php if ( ! isset( $_REQUEST['psttcsv_form_submit'] ) || $error != "" ) echo "style=\"display:none\""; ?>><p><strong><?php echo $message; ?></strong></p></div>
			<div class="error" <?php if ( "" == $error ) echo "style=\"display:none\""; ?>><p><?php echo $error; ?></p></div>
			<form id="psttcsv_settings_form" method="post" action="admin.php?page=post-to-csv.php">
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php _e( 'Post Type', 'post_to_csv' ); ?></th>
						<td>
							<?php foreach ( $psttcsv_all_post_types as $psttcsv_post_type ) { ?>
								<label><input type="checkbox" name="psttcsv_post_type[]" value="<?php echo $psttcsv_post_type; ?>" <?php if( in_array( $psttcsv_post_type, $post_type ) ) echo 'checked="checked"'; ?> /> <?php echo ucfirst( $psttcsv_post_type ); ?></label><br />
							<?php } ?>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Fields', 'post_to_csv' ); ?></th>
						<td>
							<label><input type="checkbox" name="psttcsv_fields[]" value="post_title" <?php if( in_array( 'post_title', $fields ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Title', 'post_to_csv' ); ?></label><br />
							<label><input type="checkbox" name="psttcsv_fields[]" value="guid" <?php if( in_array( 'guid', $fields ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Guid', 'post_to_csv' ); ?></label><br />
							<label><input type="checkbox" name="psttcsv_fields[]" value="permalink" <?php if( in_array( 'permalink', $fields ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Permalink', 'post_to_csv' ); ?></label><br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Post status', 'post_to_csv' ); ?></th>
						<td>
							<label><input type="radio" name="psttcsv_status" value="all" <?php if ( $status == 'all' ) echo 'checked="checked"'; ?> /> <?php _e( 'All', 'post_to_csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_status" value="publish" <?php if ( $status == 'publish' ) echo 'checked="checked"'; ?> /> <?php _e( 'Publish', 'post_to_csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_status" value="draft" <?php if ( $status == 'draft' ) echo 'checked="checked"'; ?> /> <?php _e( 'Draft', 'post_to_csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_status" value="inherit" <?php if ( $status == 'inherit' ) echo 'checked="checked"'; ?> /> <?php _e( 'Inherit', 'post_to_csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_status" value="private" <?php if ( $status == 'private' ) echo 'checked="checked"'; ?> /> <?php _e( 'Private', 'post_to_csv' ); ?></label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Order By', 'post_to_csv' ); ?></th>
						<td>
							<label><input type="radio" name="psttcsv_order" value="post_title" <?php if ( $order == 'post_title' ) echo 'checked="checked"'; ?> /> <?php _e( 'Title', 'post_to_csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_order" value="post_date" <?php if ( $order == 'post_date' ) echo 'checked="checked"'; ?> /> <?php _e( 'Date', 'post_to_csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_order" value="post_author" <?php if ( $order == 'post_author' ) echo 'checked="checked"'; ?> /> <?php _e( 'Author', 'post_to_csv' ); ?></label><br />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Order Direction', 'post_to_csv' ); ?></th>
						<td>
							<label><input type="radio" name="psttcsv_direction" value="asc" <?php if ( $direction == 'asc' ) echo 'checked="checked"'; ?> /> <?php _e( 'ASC', 'post_to_csv' ); ?></label><br />
							<label><input type="radio" name="psttcsv_direction" value="desc" <?php if ( $direction == 'desc' ) echo 'checked="checked"'; ?> /> <?php _e( 'DESC', 'post_to_csv' ); ?></label><br />
						</td>
					</tr>
				</table>
				<input type="hidden" name="psttcsv_form_submit" value="submit" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'post_to_csv' ) ?>" />
				</p>
				<?php wp_nonce_field( plugin_basename(__FILE__), 'psttcsv_nonce_name' ); ?>
			</form>
			<div class="bws-plugin-reviews">
				<div class="bws-plugin-reviews-rate">
					<?php _e( 'If you enjoy our plugin, please give it 5 stars on WordPress', 'post_to_csv' ); ?>: 
					<a href="http://wordpress.org/support/view/plugin-reviews/post-to-csv" target="_blank" title="Post to csv plugin reviews"><?php _e( 'Rate the plugin', 'post_to_csv' ); ?></a>
				</div>
				<div class="bws-plugin-reviews-support">
					<?php _e( 'If there is something wrong about it, please contact us', 'post_to_csv' ); ?>: 
					<a href="http://support.bestwebsoft.com">http://support.bestwebsoft.com</a>
				</div>
			</div>
		</div>
	<?php }
}

if ( ! function_exists( 'psttcsv_print_scv' ) ) {
	function psttcsv_print_scv(){
		global $wpdb, $psttcsv_message;

		if ( isset( $_REQUEST['psttcsv_form_submit'] ) && check_admin_referer( plugin_basename(__FILE__), 'psttcsv_nonce_name' ) ) {
			
			if ( ! isset( $_POST["psttcsv_fields"] ) || ! isset( $_POST["psttcsv_post_type"] ) )
				return;

			$filename		= tempnam( sys_get_temp_dir(), "csv" );
			$order			= isset( $_POST['psttcsv_order'] ) ? $_POST['psttcsv_order'] : 'post_date';
			$direction		= isset( $_POST['psttcsv_direction'] ) ? strtoupper( $_POST['psttcsv_direction'] ) : 'DESC';
			$status			= isset( $_POST['psttcsv_status'] ) ? $_POST['psttcsv_status'] : 'publish';
			$post_type		= '';
			$limit			= 1000;
			$start			= 0;

			// Write column names
			$colArray = $fieldArray = array();
			$colArray = $_POST["psttcsv_fields"];
			if ( in_array( 'permalink', $colArray ) ) {
				unset( $_POST["psttcsv_fields"][ array_search( 'permalink', $colArray ) ] ); 
			}
			if ( 'all' == $status ) 
				$status_sql = "";
			else {
				if ( in_array( 'attachment', $_POST["psttcsv_post_type"] ) ) {
					$status .= "', 'inherit";
				}
				$status_sql = " AND `post_status` IN ('" . $status . "')";
			}			

			$results = $wpdb->get_results( "
				SELECT `ID`, `post_type`, `" . implode( "`, `", $_POST["psttcsv_fields"] ) . "` 
				FROM $wpdb->posts 
				WHERE `post_type` IN ('" . implode( "', '", $_POST["psttcsv_post_type"] ) . "')" 
				. $status_sql . 
				"ORDER BY `post_type`, `" . $order . "` " . $direction . "
				LIMIT " . $start * $limit . ", " . $limit . "
			", ARRAY_A );
			if ( !empty( $results ) ) {
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

				// send file to browser
				readfile( $filename );
				unlink( $filename );
				exit();
			} else {
				$psttcsv_message = __( 'No items found.', 'post_to_csv' );
			}
		}
	}
}

/* Function check if plugin is compatible with current WP version  */
if ( ! function_exists ( 'psttcsv_version_check' ) ) {
	function psttcsv_version_check() {
		global $wp_version;
		$plugin_data	=	get_plugin_data( __FILE__, false );
		$require_wp		=	"3.5"; /* Wordpress at least requires version */
		$plugin			=	plugin_basename( __FILE__ );
	 	if ( version_compare( $wp_version, $require_wp, "<" ) ) {
			if ( is_plugin_active( $plugin ) ) {
				deactivate_plugins( $plugin );
				wp_die( "<strong>" . $plugin_data['Name'] . " </strong> " . __( 'requires', 'post_to_csv' ) . " <strong>WordPress " . $require_wp . "</strong> " . __( 'or higher, that is why it has been deactivated! Please upgrade WordPress and try again.', 'post_to_csv') . "<br /><br />" . __( 'Back to the WordPress', 'post_to_csv') . " <a href='" . get_admin_url( null, 'plugins.php' ) . "'>" . __( 'Plugins page', 'post_to_csv') . "</a>." );
			}
		}
	}
}

if ( ! function_exists( 'psttcsv_plugin_action_links' ) ) {
	function psttcsv_plugin_action_links( $links, $file ) {
		//Static so we don't call plugin_basename on every plugin row.
		static $this_plugin;
		if ( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

		if ( $file == $this_plugin ){
			$settings_link = '<a href="admin.php?page=post-to-csv.php">' . __( 'Settings', 'post_to_csv' ) . '</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
}
// end function psttcsv_plugin_action_links

if ( ! function_exists( 'psttcsv_register_plugin_links' ) ) {
	function psttcsv_register_plugin_links( $links, $file ) {
		$base = plugin_basename(__FILE__);
		if ( $file == $base ) {
			$links[] = '<a href="admin.php?page=post-to-csv.php">' . __( 'Settings', 'post_to_csv' ) . '</a>';
			$links[] = '<a href="http://wordpress.org/plugins/post-to-csv/faq/" target="_blank">' . __( 'FAQ', 'post_to_csv' ) . '</a>';
			$links[] = '<a href="http://support.bestwebsoft.com">' . __( 'Support', 'post_to_csv' ) . '</a>';
		}
		return $links;
	}
}

if ( ! function_exists ( 'psttcsv_plugin_init' ) ) {
	function psttcsv_plugin_init() {
		// Internationalization, first(!)
		load_plugin_textdomain( 'post_to_csv', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' ); 

		global $bws_plugin_info;
		if ( function_exists( 'get_plugin_data' ) && ( ! isset( $bws_plugin_info ) || empty( $bws_plugin_info ) ) ) {
			$plugin_info = get_plugin_data( __FILE__ );	
			$bws_plugin_info = array( 'id' => '113', 'version' => $plugin_info["Version"] );
		}		
	}
}

if ( ! function_exists ( 'psttcsv_head' ) ) {
	function psttcsv_head() {
		global $wp_version;
		if ( 3.8 > $wp_version )
			wp_enqueue_style( 'lstStylesheet', plugins_url( 'css/style_wp_before_3.8.css', __FILE__ ) );	
		else
			wp_enqueue_style( 'lstStylesheet', plugins_url( 'css/style.css', __FILE__ ) );
	}
}

// adds "Settings" link to the plugin action page
add_filter( 'plugin_action_links', 'psttcsv_plugin_action_links', 10, 2 );

//Additional links on the plugin page
add_filter( 'plugin_row_meta', 'psttcsv_register_plugin_links', 10, 2 );

add_action( 'init', 'psttcsv_plugin_init' );
add_action( 'admin_init', 'psttcsv_plugin_init' );
add_action( 'admin_init', 'psttcsv_print_scv' );
add_action( 'admin_init', 'psttcsv_version_check' );
add_action( 'admin_menu', 'add_psttcsv_admin_menu' );

add_action( 'admin_enqueue_scripts', 'psttcsv_head' );

?>