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
				'settings'	    => array( 'label' => __( 'Settings', 'post-to-csv' ) ),
				'comments'	    => array( 'label' => __( 'Comments', 'post-to-csv' ) ),
				'woocommerce'	=> array( 'label' => __( 'WooCommerce', 'post-to-csv' ), 'is_pro' => 1 ),
				'misc'		    => array( 'label' => __( 'Misc', 'post-to-csv' ) ),
				'custom_code'	=> array( 'label' => __( 'Custom Code', 'post-to-csv' ) ),
				'license'		=> array( 'label' => __( 'License Key', 'post-to-csv' ) ),
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $psttcsv_plugin_info,
				'prefix' 			 => 'psttcsv',
				'default_options' 	 => psttcsv_get_options_default(),
				'options' 			 => $psttcsv_options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'post-to-csv',
				'pro_page'					=> 'admin.php?page=post-to-csv-pro.php',
				'bws_license_plugin'		=> 'post-to-csv-pro/post-to-csv-pro.php',
				'link_key'					=> '8f09a91fce52ce4cf41fa8aec0f434ea',
				'link_pn'					=> '113'
			) );

			$args = array(
				'public'	=> true,
				'_builtin'	=> false
			);

			$this->post_types = get_post_types( $args, 'names', 'and' );

			$taxonomy_args = array();
			$this->post_taxonomies = get_taxonomies( $taxonomy_args, 'objects' );

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
				'taxonomy'		=> __( 'Taxonomy', 'post-to-csv' ),
				'term'		    => __( 'Term', 'post-to-csv' ),

			);

			$this->all_comment_fields = array(
				'comment_post_ID'	    => __( 'Post ID', 'post-to-csv' ),
				'permalink'	                => __( 'Post Permalink', 'post-to-csv' ),
				'comment_author'	    => __( 'Author', 'post-to-csv' ),
                'comment_author_email'	=> __( 'Author\'s Email', 'post-to-csv' ),
                'comment_content' 	    => __( 'Comment Content', 'post-to-csv' ),
                'comment_date'		    => __( 'Comment Date', 'post-to-csv' ),
			);

			$this->all_status = array(
				'publish' 	=> __( 'Publish', 'post-to-csv' ),
				'draft' 	=> __( 'Draft', 'post-to-csv' ),
				'inherit' 	=> __( 'Inherit', 'post-to-csv' ),
				'private' 	=> __( 'Private', 'post-to-csv' )
			);
			$this->options['psttcsv_order'] = $psttcsv_options['psttcsv_order'];
			$this->options['psttcsv_direction'] = $psttcsv_options['psttcsv_direction'];	
			$this->options['psttcsv_delete_html'] = $psttcsv_options['psttcsv_delete_html'];

			add_action( get_parent_class( $this ) . '_display_custom_messages', array( $this, 'display_custom_messages' ) );
			}

		public function save_options() {
			$error = '';
			$this->options['psttcsv_export_type'] = isset( $_POST['psttcsv_export_type'] ) ? $_POST['psttcsv_export_type'] : 'post_type';

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
			foreach ( $this->post_taxonomies as $taxonomy ) {
                if ( isset( $_POST['psttcsv_term_taxonomy_' . $taxonomy->name] ) ) {
                    $this->options['psttcsv_taxonomy'][ $taxonomy->name ] = $_POST['psttcsv_term_taxonomy_' . $taxonomy->name];
                }
                else {
                    $this->options['psttcsv_term_taxonomy_' . $taxonomy->name] = array();
                    $this->options['psttcsv_taxonomy'][ $taxonomy->name ] = array();
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
			$this->options['psttcsv_delete_html'] = isset( $_POST['psttcsv_delete_html'] ) ? $_POST['psttcsv_delete_html'] : 0 ;

			/*Comments Tab*/

			if ( ! isset( $_POST['psttcsv_comment_fields'] ) ) {
				$error .= __( 'Please choose at least one Comment field.', 'post-to-csv' ) . '<br />';
			} else {
				$this->options['psttcsv_comment_fields'] = $_POST['psttcsv_comment_fields'];
			}

			$this->options['psttcsv_order_comment'] = isset( $_POST['psttcsv_order_comment'] ) ? $_POST['psttcsv_order_comment'] : 'comment_ID';
			$this->options['psttcsv_direction_comment'] = isset( $_POST['psttcsv_direction_comment'] ) ? $_POST['psttcsv_direction_comment'] : 'desc';

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
                        <th scope="row"><?php _e( 'Export', 'post-to-csv' ); ?></th>
                        <td><fieldset>
                                <label><input type="radio" name="psttcsv_export_type" value="post_type" <?php if ( 'post_type' == $this->options['psttcsv_export_type'] ) echo 'checked="checked"'; ?> /> <?php _e( 'Post Type', 'post-to-csv' ); ?></label><br />
                                <label><input type="radio" name="psttcsv_export_type" value="taxonomy" <?php if ( 'taxonomy' == $this->options['psttcsv_export_type'] ) echo 'checked="checked"'; ?> /> <?php _e( 'Taxonomies', 'post-to-csv' ); ?></label><br />
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top" id="psttcsv-posttype-block">
						<th scope="row"><?php _e( 'Post Type', 'post-to-csv' ); ?></th>
						<td><fieldset>
								<div class="psttcsv_div_select_all hide-if-no-js"><label><input id="psttcsv_select_all_post_types" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
								<?php foreach ( $this->post_types as $post_type => $post_type_name ) { ?>
									<label><input type="checkbox" name="psttcsv_post_type[]" value="<?php echo $post_type; ?>" class="bws_option_affect" data-affect-show="[data-post-type=<?php echo $post_type; ?>]" <?php checked( in_array( $post_type, $this->options['psttcsv_post_type'] ) ); ?> /> <?php echo ucfirst( $post_type_name ); ?></label><br />
								<?php } ?>
							</fieldset>
						</td>
					</tr>
                    <tr valign="top" id="psttcsv-taxonomies-block">
                        <th scope="row"><?php _e( 'Taxonomies', 'post-to-csv' ); ?></th>
                        <td>
                            <div id="psttcsv-accordion-taxonomies">
		                        <?php foreach ( $this->post_taxonomies as $taxonomy ) {
			                        $terms = get_terms( $taxonomy->name, 'orderby=name&hide_empty=0' );
                                    if ( ! empty ( $terms ) ) { ?>
                                        <h3 data-post-type="<?php echo $taxonomy->name; ?>"><?php echo $taxonomy->labels->name . ' (' . $taxonomy->name . ')' ; ?></h3>
                                        <div class="psttcsv_taxonomies_settings_accordion_item" data-post-type="<?php echo $taxonomy->name; ?>">
                                            <fieldset>
                                                <div class="hide-if-no-js" >
                                                    <label>
                                                        <input id="psttcsv_select_all_<?php echo $taxonomy->name; ?>" class="psttcsv_select_all" type="checkbox" /><strong><?php _e( 'All', 'post-to-csv' ); ?></strong>
                                                    </label>
                                                </div>
                                                <?php foreach ( $terms as $item ) { ?>
                                                    <label><input type="checkbox" class="psttcsv_checkbox_select" name="psttcsv_term_taxonomy_<?php echo $taxonomy->name; ?>[]" value="<?php echo $item->name; ?>" <?php if ( isset( $this->options['psttcsv_taxonomy'][ $taxonomy->name ] ) ) checked( in_array( $item->name, $this->options['psttcsv_taxonomy'][ $taxonomy->name] ) ); ?> /> <?php echo $item->name; ?></label><br />
                                                <?php } ?>
                                            </fieldset>
                                        </div>
                                    <?php }
                                    } ?>
                            </div>
                        </td>
                    </tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Fields', 'post-to-csv' ); ?></th>
						<td><fieldset>
								<div class="psttcsv_div_select_all hide-if-no-js"><label><input id="psttcsv_select_all_fields" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
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
														<label><input type="checkbox" class="psttcsv_checkbox_select" name="psttcsv_meta_key_<?php echo $post_type; ?>[]" value="<?php echo $post_meta_field_name; ?>" <?php if ( isset( $this->options['psttcsv_meta_key_' . $post_type] ) ) checked( in_array( $post_meta_field_name, $this->options['psttcsv_meta_key_' . $post_type] ) ); ?> /> <?php echo $post_meta_field_name; ?></label><br />
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
					<tr valgin="top">
						<th scope="row"><?php _e( 'Remove HTML Tags', 'post-to-csv' ); ?></th>
						<td>
							<input type="checkbox" name="psttcsv_delete_html" value="1" <?php if ( '1' == $this->options['psttcsv_delete_html'] ) echo 'checked="checked"'; ?> />
							<span class="bws_info">
								<?php _e( 'Enable to remove HTML tags from the post content.', 'post-to-csv' ); ?>
							</span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Export to CSV', 'post-to-csv' ); ?></th>
						<td>
							<input type="submit" name="psttcsv_export_submit" class="button-primary" value="<?php _e( 'Export', 'post-to-csv' ) ?>" />
                            <span class="bws_info psttcsv_export_notice" style="display: none">
                                <strong><?php _e( 'Notice', 'post-to-csv' ); ?></strong>: <?php _e( "The plugin's settings have been changed.", 'post-to-csv' ); ?>
				                <a class="bws_save_anchor" href="#bws-submit-button"><?php _e( 'Save Changes', 'post-to-csv' ); ?></a>&nbsp;<?php _e( 'before export', 'post-to-csv' ); ?>.
                            </span>
						</td>
					</tr>
				</table>
		<?php }

		public function tab_comments() { ?>

            <h3 class="bws_tab_label"><?php _e( 'Comments Export Settings', 'post-to-csv' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <table class="form-table psttcsv-table-settings" id="psttcsv_comments_form">
                <tr valign="top">
                    <th scope="row"><?php _e( 'Comment Fields', 'post-to-csv' ); ?></th>
                    <td><fieldset>
                            <div class="psttcsv_div_select_all hide-if-no-js"><label><input class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
							<?php foreach ( $this->all_comment_fields as $comment_field_key => $comment_field_name ) { ?>
                                <label><input type="checkbox" name="psttcsv_comment_fields[]" value="<?php echo $comment_field_key; ?>" class="bws_option_affect" <?php checked( in_array( $comment_field_key, $this->options['psttcsv_comment_fields'] ) ); ?> /> <?php echo $comment_field_name; ?></label><br />
							<?php } ?>
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Sort by', 'post-to-csv' ); ?></th>
                    <td><fieldset>
                            <label><input type="radio" name="psttcsv_order_comment" value="comment_ID" <?php if ( 'comment_ID' == $this->options['psttcsv_order_comment'] ) echo 'checked="checked"'; ?> /> <?php _e( 'ID', 'post-to-csv' ); ?></label><br />
                            <label><input type="radio" name="psttcsv_order_comment" value="comment_date" <?php if ( 'comment_date' == $this->options['psttcsv_order_comment'] ) echo 'checked="checked"'; ?> /> <?php _e( 'Date', 'post-to-csv' ); ?></label><br />
                            <label><input type="radio" name="psttcsv_order_comment" value="comment_author" <?php if ( 'comment_author' == $this->options['psttcsv_order_comment'] ) echo 'checked="checked"'; ?> /> <?php _e( 'Author', 'post-to-csv' ); ?></label><br />
                        </fieldset>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e( 'Arrange by', 'post-to-csv' ); ?></th>
                    <td><fieldset>
                            <label><input type="radio" name="psttcsv_direction_comment" value="asc" <?php if ( 'asc' == $this->options['psttcsv_direction_comment'] ) echo 'checked="checked"'; ?> /> <?php _e( 'ASC', 'post-to-csv' ); ?></label><br />
                            <label><input type="radio" name="psttcsv_direction_comment" value="desc" <?php if ( 'desc' == $this->options['psttcsv_direction_comment'] ) echo 'checked="checked"'; ?> /> <?php _e( 'DESC', 'post-to-csv' ); ?></label><br />
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e( 'Export to CSV', 'post-to-csv' ); ?></th>
                    <td>
                        <input type="submit" name="psttcsv_export_submit_comments" class="button-primary" value="<?php _e( 'Export', 'post-to-csv' ) ?>" />
                        <span class="bws_info psttcsv_export_notice" style="display: none">
                                <strong><?php _e( 'Notice', 'post-to-csv' ); ?></strong>: <?php _e( "The plugin's settings have been changed.", 'post-to-csv' ); ?>
				                <a class="bws_save_anchor" href="#bws-submit-button"><?php _e( 'Save Changes', 'post-to-csv' ); ?></a>&nbsp;<?php _e( 'before export', 'post-to-csv' ); ?>.
                        </span>
                    </td>
                </tr>
            </table>
		<?php }

		public function tab_woocommerce() {

            $status = array(
                'private'   => __( 'Private', 'post-to-csv' ),
                'publish'   => __( 'Publish', 'post-to-csv' ),
                'draft'     => __( 'Draft', 'post-to-csv' ),
                'future'    => __( 'Future', 'post-to-csv' ),
                'pending'   => __( 'Pending', 'post-to-csv' ),
            );

			$woo_product_types = array(
				'simple' => __( 'Simple product', 'post-to-csv' ),
                'grouped' => __( 'Grouped product', 'post-to-csv' ),
                'external' => __( 'External/Affiliate product', 'post-to-csv' ),
                'variable' => __( 'Variable product', 'post-to-csv' ),
				'variation' => __( 'Product variations', 'post-to-csv' )
            ); ?>

            <h3 class="bws_tab_label"><?php _e( 'WooCommerce Export Product Settings', 'post-to-csv' ); ?></h3>
            <?php $this->help_phrase(); ?>
            <div class="bws_pro_version_bloc">
                <div class="bws_pro_version_table_bloc">
                    <button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'subscriber' ); ?>"></button>
                    <div class="bws_table_bg"></div>
                    <table class="form-table bws_pro_version">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'WooCommerce Product Fields', 'post-to-csv' ); ?></th>
                        <td>
                            <div id="psttcsv-accordion-woocommerce">
                                <h3 data-post-type="woocommerce_product"><?php _e( 'Product Fields', 'post-to-csv' ); ?></h3>
                                <div class="psttcsv_custom_fields_settings_accordion_item" data-post-type="woocommerce_product">
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Product Status', 'post-to-csv' ); ?></th>
                        <td><fieldset>
                                <div class="psttcsv_div_select_all hide-if-no-js"><label><input id="psttcsv_select_all_status" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
                                <?php foreach ( $status as $value => $key ) { ?>
                                    <label>
                                        <input type="checkbox" name="psttcsv_status_woocommerce[]" value="<?php echo $value; ?>" checked="checked" /> <?php echo $key; ?>
                                    </label>
                                    <br />
                                <?php } ?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Product Types', 'post-to-csv' ); ?></th>
                        <td><fieldset>
                                <div class="psttcsv_div_select_all hide-if-no-js"><label><input id="psttcsv_select_all_types" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
                                <?php foreach ( $woo_product_types as $types_id => $types_name ) { ?>
                                    <label>
                                        <input type="checkbox" name="psttcsv_product_type_woocommerce[]" value="<?php echo $types_id; ?>" checked="checked" /> <?php echo $types_name; ?>
                                    </label><br />
                                <?php } ?>
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Product Category', 'post-to-csv' ); ?></th>
                        <td><fieldset>
                                <div class="psttcsv_div_select_all hide-if-no-js"><label><input id="psttcsv_select_all_category" class="psttcsv_select_all" type="checkbox" /> <strong><?php _e( 'All', 'post-to-csv' ); ?></strong></label></div>
                                    <label>
                                        <input type="checkbox" name="psttcsv_product_category_woocommerce[]" value="uncategorized" checked="checked" /> <?php echo _e( 'Uncategorized', 'post-to-csv' ); ?>
                                    </label><br />
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Sort by', 'post-to-csv' ); ?></th>
                        <td><fieldset>
                                <label><input type="radio" name="psttcsv_order_woocommerce" value="ID" checked="checked" /> <?php _e( 'ID', 'post-to-csv' ); ?></label><br />
                                <label><input type="radio" name="psttcsv_order_woocommerce" value="name" /> <?php _e( 'Name', 'post-to-csv' ); ?></label><br />
                                <label><input type="radio" name="psttcsv_order_woocommerce" value="type" /> <?php _e( 'Type', 'post-to-csv' ); ?></label><br />
                                <label><input type="radio" name="psttcsv_order_woocommerce" value="date" /> <?php _e( 'Date', 'post-to-csv' ); ?></label><br />
                                <label><input type="radio" name="psttcsv_order_woocommerce" value="modified" /> <?php _e( 'Modified', 'post-to-csv' ); ?></label><br />
                            </fieldset>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Arrange by', 'post-to-csv' ); ?></th>
                        <td><fieldset>
                                <label><input type="radio" name="psttcsv_direction_woocommerce" value="ASC" checked="checked"'/> <?php _e( 'ASC', 'post-to-csv' ); ?></label><br />
                                <label><input type="radio" name="psttcsv_direction_woocommerce" value="DESC" /> <?php _e( 'DESC', 'post-to-csv' ); ?></label><br />
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Export to CSV', 'post-to-csv' ); ?></th>
                        <td>
                            <input type="submit" name="psttcsv_export_woocommerce_submit" class="button-primary" value="<?php _e( 'Export', 'post-to-csv' ) ?>" />
                        </td>
                    </tr>
                    </table>
                </div>
                <?php $this->bws_pro_block_links(); ?>
            </div>
		<?php }

		/**
		 * Displays custom error message on export error
		 * @access public
		 */
		public function display_custom_messages( $save_results ) {
			if ( ! empty( $_SESSION['psttcsv_error_message'] ) ) {
				if ( 'no_data' == $_SESSION['psttcsv_error_message'] ) { ?>
                    <div class="error inline psttcsv_error"><p><strong><?php _e( 'No records meet the specified criteria.', 'post-to-csv' ); ?></strong></p></div>
				<?php }
				unset( $_SESSION['psttcsv_error_message'] );
			}
		}

	}
}