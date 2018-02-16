<?php
/*
	Plugin Name: WPJobster Category Tax
	Plugin URL: http://wpjobster.com/
	Description: WPJobster Category Tax.
	Version: 1.0.0
	Author: WPJobster
	Author URI: http://wpjobster.com/
*/
add_action('admin_enqueue_scripts', 'wpj_ct_load_scripts');
function wpj_ct_load_scripts() {
	// Register the script
	wp_register_script( 'wpj-ct-main-scripts', plugins_url( 'script.js', __FILE__ ) );

	// Enqueued script with localized data.
	wp_enqueue_script( 'wpj-ct-main-scripts' );
}

add_action( 'wpj_html_fees_for_buyers', 'wpj_ct_show_admin_gateway_content', 10, 1 );
function wpj_ct_show_admin_gateway_content( $css_class ) {
	$category_taxes = get_option( 'category_taxes' );

	if ( $category_taxes ) {
		foreach ( $category_taxes as $key => $value ) { ?>
			<tr class="tc-repeater-row <?php echo $css_class; ?>">
				<td><?php wpjobster_theme_bullet(__('Select a category and tax for it','wpjobster')); ?></td>
				<td><?php echo __('Tax for category','wpjobster-tax-category') . ':';
					$taxo = 'job_cat';
					$ret = '<select name="wpjobster_category_taxes[]" class="sel_category_taxes grey_input styledselect" style="width:97px;">';
						$ret .= '<option value="">-</option>';

						$args = "orderby=name&order=ASC&hide_empty=0&parent=0";
						$terms = get_terms($taxo, $args);
						foreach ( $terms as $term ) {
							$ide = $term->term_id;
							$sel = ( $ide == $key ) ? "selected" : "";
							$ret .= '<option ' . $sel . ' value="'.$ide.'">'.$term->name.'</option>';

							$args1 = "orderby=name&order=ASC&hide_empty=0&parent=" . $ide;
							$sub_terms = get_terms($taxo, $args1);
							foreach ( $sub_terms as $sub_term ) {
								$sub_id = $sub_term->term_id;
								$sel1 = ( $sub_id == $key ) ? "selected" : "";
								$ret .= '<option ' . $sel1 . ' value="'.$sub_id.'">&nbsp;&nbsp;|&nbsp;'.$sub_term->name.'</option>';

								$args2 = "orderby=name&order=ASC&hide_empty=0&parent=" . $sub_id;
								$sub_terms2 = get_terms($taxo, $args2);
								foreach ( $sub_terms2 as $sub_term2 ) {
									$sub_id2 = $sub_term2->term_id;
									$sel2 = ( $sub_id2 == $key ) ? "selected" : "";
									$ret .= '<option ' . $sel1 . ' value="'.$sub_id2.'">&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;'.$sub_term2->name.'</option>';
								}
							}
						}
					$ret .= '</select>';
					echo $ret; ?>
				</td>
				<td>
					<input value="<?php echo $value; ?>" class="inp_category_taxes" type="number" step="any" min="0" max="100" size="3" name="wpjobster_category_taxes_percentage[]">%
					<button class="delete-new-tax button-secondary"><?php echo __( 'Delete Row', 'wpjobster-tax-category' ); ?></button>
				</td>
			</tr>
		<?php }
	} else { ?>
		<tr class="tc-repeater-row <?php echo $css_class; ?>">
			<td><?php wpjobster_theme_bullet(__('Select a category and tax for it','wpjobster')); ?></td>
			<td><?php echo __('Tax for category','wpjobster-tax-category') . ':';
				$taxo = 'job_cat';
				$ret = '<select name="wpjobster_category_taxes[]" class="sel_category_taxes grey_input styledselect" style="width:97px;">';
					$ret .= '<option value="">-</option>';

					$args = "orderby=name&order=ASC&hide_empty=0&parent=0";
					$terms = get_terms($taxo, $args);
					foreach ($terms as $term) {
						$ide = $term->term_id;
						$ret .= '<option value="'.$ide.'">'.$term->name.'</option>';

						$args1 = "orderby=name&order=ASC&hide_empty=0&parent=" . $ide;
						$sub_terms = get_terms($taxo, $args1);
						foreach ($sub_terms as $sub_term) {
							$sub_id = $sub_term->term_id;
							$ret .= '<option value="'.$sub_id.'">&nbsp;&nbsp;|&nbsp;'.$sub_term->name.'</option>';

							$args2 = "orderby=name&order=ASC&hide_empty=0&parent=" . $sub_id;
							$sub_terms2 = get_terms($taxo, $args2);
							foreach ($sub_terms2 as $sub_term2) {
								$sub_id2 = $sub_term2->term_id;
								$ret .= '<option value="'.$sub_id2.'">&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;'.$sub_term2->name.'</option>';
							}
						}
					}
				$ret .= '</select>';
				echo $ret; ?>
			</td>
			<td>
				<input class="inp_category_taxes" type="number" step="any" min="0" max="100" size="3" name="wpjobster_category_taxes_percentage[]">%
				<button class="delete-new-tax button-secondary"><?php echo __( 'Delete Row', 'wpjobster-tax-category' ); ?></button>
			</td>
		</tr>
	<?php } ?>

	<tr>
		<td colspan="2"></td>
		<td>
			<button class="add-new-tax button-secondary"><?php echo __( 'Add New Tax', 'wpjobster-tax-category' ); ?></button>
		</td>
	</tr>
<?php }

add_action( 'wpj_save_fees_for_buyers', 'wpj_ct_save_admin_gateway_content', 10 );
function wpj_ct_save_admin_gateway_content() {
	$category_tax = array();
	foreach ( $_POST['wpjobster_category_taxes'] as $key => $value ) {
		$category_tax[$value] = $_POST['wpjobster_category_taxes_percentage'][$key];
	}
	update_option( 'category_taxes', $category_tax );
}

add_filter( 'wpj_apply_fees_by_category', 'wpj_ct_apply_fees' );
function wpj_ct_apply_fees( $wpjobster_tax_percent ) {
	global $post;
	if ( isset( $_GET['jobid'] ) ) {
		$pid = $_GET['jobid'];
	} elseif ( isset( $post->ID ) ) {
		$pid = $post->ID;
	}
	
	if ( isset( $pid ) ) {
		$terms = get_the_terms( $pid, 'job_cat' );
		if ( $terms ){
			foreach ( $terms as $key => $value ) {
				if ( $value->parent && $value->parent != 0 ) {
					$p_term = $value->term_id; break;
				} else {
					$p_term = $value->term_id;
				}
			}
		}

		$category_taxes = get_option( 'category_taxes' );
		if ( $category_taxes ) {
			foreach ( $category_taxes as $key => $value ) {
				if( $key == $p_term ) {
					$wpjobster_tax_percent = $value;
				}
			}
		}
	}

	return $wpjobster_tax_percent;
}