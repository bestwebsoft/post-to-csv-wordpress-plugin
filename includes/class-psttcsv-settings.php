<?php
/**
 * Displays the content on the plugin settings page
 */
require_once ( dirname( dirname( __FILE__ ) ) . '/bws_menu/class-bws-settings.php' );

if ( ! class_exists( 'Psttcsv_Settings_Tabs' ) ) {
	class Psttcsv_Settings_Tabs extends Bws_Settings_Tabs {
		public $all_post_types, $all_fields, $all_status, $post_types;

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $psttcsv_options, $psttcsv_plugin_info;

			$tabs = array(
				'settings'	=> array( 'label' => __( 'Settings', 'post-to-csv' ) ),
				'misc'		=> array( 'label' => __( 'Misc', 'post-to-csv' ) ),
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $psttcsv_plugin_info,
				'prefix' 			 => 'psttcsv',
				'default_options' 	 => psttcsv_get_options_default(),
				'options' 			 => $psttcsv_options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'post-to-csv'
			) );

			$args=array(
				'public'	=> true,
				'_builtin'	=> false
			);
			$this->post_types = get_post_types( $args, 'names', 'and' );

			$this->post_types = array_merge( array(
				'post'		 => __( 'Post', 'post-to-csv' ),
				'page' 		 => __( 'Page', 'post-to-csv' ),
				'attachment' => __( 'Attachment', 'post-to-csv' ),
			), $this->post_types );

			$this->all_fields = array(
				'post_title'	=> __( 'Title', 'post-to-csv' ),
				'guid'			=> __( 'Guid', 'post-to-csv' ),
				'post_date'		=> __( 'Post date', 'post-to-csv' ),
				'post_author' 	=> __( 'Author', 'post-to-csv' ),
				'post_content' 	=> __( 'Content', 'post-to-csv' ),
				'permalink'		=> __( 'Permalink', 'post-to-csv' ),
			);

			$this->all_status = array(
				'publish' 	=> __( 'Publish', 'post-to-csv' ),
				'draft' 	=> __( 'Draft', 'post-to-csv' ),
				'inherit' 	=> __( 'Inherit', 'post-to-csv' ),
				'private' 	=> __( 'Private', 'post-to-csv' )
			);
			$this->options['psttcsv_order'] = $psttcsv_options['psttcsv_order'];
			$this->options['psttcsv_direction'] = $psttcsv_options['psttcsv_direction'];

			}

		public function save_options() {
			$error = '';
			if ( ! isset( $_POST['psttcsv_post_type'] ) ) {
				$error = __( 'Please choose at least one Post Type.', 'post-to-csv' ) . '<br />';
			} else {
				$this->options['psttcsv_post_type'] = $_POST['psttcsv_post_type'];
			}

			if ( ! isset( $_POST['psttcsv_fields'] ) ) {
				$error .= __( 'Please choose at least one Field.', 'post-to-csv' ) . '<br />';
			} else {
				$this->options['psttcsv_fields'] = $_POST['psttcsv_fields'];
				foreach ( $this->post_types as $post_type => $post_value ) {
					if ( isset( $_POST['psttcsv_meta_key_' . $post_type] ) ) {
						$this->options['psttcsv_meta_key_' . $post_type] = $_POST['psttcsv_meta_key_' . $post_type];
					} else {
						$this->options['psttcsv_meta_key_' . $post_type] = array();
					}
				}
			}

			if ( ! isset( $_POST['psttcsv_status'] ) ) {
				$error .= __( 'Please choose at least one Post status.', 'post-to-csv' ) . '<br />';
			} else {
				$this->options['psttcsv_status'] = $_POST['psttcsv_status'];
			}

			$this->options['psttcsv_order'] = isset( $_POST['psttcsv_order'] ) ? $_POST['psttcsv_order'] : 'post_date';
			$this->options['psttcsv_direction'] = isset( $_POST['psttcsv_direction'] ) ? $_POST['psttcsv_direction'] : 'desc';
			$this->options['psttcsv_show_hidden_fields'] = isset( $_POST['psttcsv_show_hidden_fields'] ) ? 1 : 0;

			if ( empty( $error ) ) {
				update_option( 'psttcsv_options', $this->options );
				$message = __( 'Settings saved.', 'post-to-csv' );
			}
			return compact( 'message', 'notice', 'error' );
		}

		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Post to CSV Settings', 'post-to-csv' ); ?></h3>
			<?php $this->help_phrase(); ?>
				<table class="form-table psttcsv-table-settings" id="psttcsv_settings_form">
					<tr valign="top">
						<th scope="row"><?php _e( 'Post Type', 'post-to-csv' ); ?></th>
						<td><fieldset>
								<div class="psttcsv_div_select_all  hide-if-no-js"><label><input id="psttcsv_select_all_post_types" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
								<?php foreach ( $this->post_types as $post_type => $post_type_name ) { ?>
									<label><input type="checkbox" name="psttcsv_post_type[]" value="<?php echo $post_type; ?>" class="bws_option_affect" data-affect-show="[data-post-type=<?php echo $post_type; ?>]" <?php checked( in_array( $post_type, $this->options['psttcsv_post_type'] ) ); ?> /> <?php echo ucfirst( $post_type_name ); ?></label><br />
								<?php } ?>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Fields', 'post-to-csv' ); ?></th>
						<td><fieldset>
								<div class="psttcsv_div_select_all  hide-if-no-js"><label><input id="psttcsv_select_all_fields" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
								<?php foreach ( $this->all_fields as $field_key => $field_name ) { ?>
									<label><input type="checkbox" name="psttcsv_fields[]" value="<?php echo $field_key; ?>" class="bws_option_affect" <?php checked( in_array( $field_key, $this->options['psttcsv_fields'] ) ); ?> /> <?php echo $field_name; ?></label><br />
								<?php } ?>
							</fieldset>
						</td>
					</tr>
					<?php if ( ! empty( $this->options['psttcsv_post_type'] ) ) { ?>
					<tr valign="top">
						<th scope="row"><?php _e( 'Custom Fields', 'post-to-csv' ); ?></th>
						<td>
							<div class="psttcsv-show-meta-key hide-if-no-js">
								<label><input name="psttcsv_show_hidden_fields" type="checkbox" id="psttcsv-show-hidden-meta" <?php if ( isset( $this->options['psttcsv_show_hidden_fields'] ) ) checked( $this->options['psttcsv_show_hidden_fields'] ) ?> data-affect-show=".psttcsv-hidden-option"><?php _e( 'Show hidden fields', 'post-to-csv' ); ?></label>
							</div>
							<div id="psttcsv-accordion">
								<?php foreach ( $this->post_types as $post_type => $post_type_name ) {
									$post_type_meta = psttcsv_get_all_meta( $post_type ); ?>
									<h3 data-post-type="<?php echo $post_type; ?>"><?php echo ucfirst( $post_type_name ); ?></h3>
									<div class="psttcsv_custom_fields_settings_accordion_item" data-post-type="<?php echo $post_type; ?>">
										<?php if ( empty( $post_type_meta ) ) { _e( 'No service meta keys', 'post-to-csv' ); } ?>
										<fieldset>
											<div class="hide-if-no-js" >
												<label>
													<input id="psttcsv_select_all_<?php echo $post_type; ?>" class="psttcsv_select_all" type="checkbox" /><strong><?php _e( 'All', 'post-to-csv' ); ?></strong>
												</label>
											</div>
											<?php foreach ( $post_type_meta as $item ) {
												$post_meta_field_name = esc_attr( $item['meta_key'] );
												if ( '_' == substr( $post_meta_field_name, 0, 1 ) ) { ?>
													<div class="psttcsv-hidden-option">
														<label><input  type="checkbox" class="psttcsv_checkbox_select" name="psttcsv_meta_key_<?php echo $post_type; ?>[]" value="<?php echo $post_meta_field_name; ?>" <?php if ( isset( $this->options['psttcsv_meta_key_' . $post_type] ) ) checked( in_array( $post_meta_field_name, $this->options['psttcsv_meta_key_' . $post_type] ) ); ?> /> <?php echo $post_meta_field_name; ?></label><br />
													</div>
												<?php } else { ?>
													<label><input type="checkbox" class="psttcsv_checkbox_select" name="psttcsv_meta_key_<?php echo $post_type; ?>[]" value="<?php echo $post_meta_field_name; ?>" <?php if ( isset( $this->options['psttcsv_meta_key_' . $post_type] ) ) checked( in_array( $post_meta_field_name, $this->options['psttcsv_meta_key_' . $post_type] ) ); ?> /> <?php echo $post_meta_field_name; ?></label><br />
												<?php }
												 } ?>
										</fieldset>
									</div>
								<?php } ?>
							</div>
						</td>
					</tr>
					<?php } ?>
					<tr valign="top">
						<th scope="row"><?php _e( 'Post Status', 'post-to-csv' ); ?></th>
						<td><fieldset>
								<div class="psttcsv_div_select_all hide-if-no-js"><label><input id="psttcsv_select_all_status" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
								<?php foreach ( $this->all_status as $status_value => $status_name ) { ?>
									<label><input type="checkbox" name="psttcsv_status[]" value="<?php echo $status_value; ?>" <?php if ( in_array( $status_value, $this->options['psttcsv_status'] ) ) echo 'checked="checked"'; ?> /> <?php echo $status_name; ?></label><br />
								<?php } ?>
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Sort by', 'post-to-csv' ); ?></th>
						<td><fieldset>
								<label><input type="radio" name="psttcsv_order" value="post_title" <?php if ( 'post_title' == $this->options['psttcsv_order'] ) echo 'checked="checked"'; ?> /> <?php _e( 'Title', 'post-to-csv' ); ?></label><br />
								<label><input type="radio" name="psttcsv_order" value="post_date" <?php if ( 'post_date' == $this->options['psttcsv_order'] ) echo 'checked="checked"'; ?> /> <?php _e( 'Date', 'post-to-csv' ); ?></label><br />
								<label><input type="radio" name="psttcsv_order" value="post_author" <?php if ( 'post_author' == $this->options['psttcsv_order'] ) echo 'checked="checked"'; ?> /> <?php _e( 'Author', 'post-to-csv' ); ?></label><br />
							</fieldset>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Arrange by', 'post-to-csv' ); ?></th>
						<td><fieldset>
								<label><input type="radio" name="psttcsv_direction" value="asc" <?php if ( 'asc' == $this->options['psttcsv_direction'] ) echo 'checked="checked"'; ?> /> <?php _e( 'ASC', 'post-to-csv' ); ?></label><br />
								<label><input type="radio" name="psttcsv_direction" value="desc" <?php if ( 'desc' == $this->options['psttcsv_direction'] ) echo 'checked="checked"'; ?> /> <?php _e( 'DESC', 'post-to-csv' ); ?></label><br />
							</fieldset>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Export to CSV', 'post-to-csv' ); ?></th>
						<td>
							<input type="submit" name="psttcsv_export_submit" class="button-primary" value="<?php _e( 'Export', 'post-to-csv' ) ?>" />
						</td>
					</tr>
				</table>
		<?php }
	}
}