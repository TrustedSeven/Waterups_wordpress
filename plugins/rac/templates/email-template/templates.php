
<table class='widefat'>
	<tr>
		<td></td>
	</tr>
	<!--Start Email Template Message shortcodes -->
		<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.productname} to display the product name in the email subject</strong>
			</span>
		</td>
	</tr>
		
		<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.firstname} to insert Reciever First Name in the email</strong>
			</span>
		</td>
	</tr>

	<tr>
		<td colspan='2'>
			<span><strong>Use {rac.lastname} to insert Receiver Last Name in the email</strong>
			</span>
		</td>
	</tr>
		
	<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.cartlink} to insert the Cart Link in the email</strong>
			</span>
		</td>
	</tr>

	<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.date} to insert the Abandoned Cart Date in the email</strong>
			</span>
		</td>
	</tr>

	<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.time} to insert the Abandoned Cart Time in the email</strong>
			</span>
		</td>
	</tr>

	<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.Productinfo} to insert Product Information in the email</strong>
			</span>
		</td>
	</tr>

	<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.coupon} to insert Coupon Code in the email</strong>
			</span>
		</td>
	</tr>

	<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.coupon_expired_date} to insert coupon expiry date in the email</strong>
			</span>
		</td>
	</tr>

		<tr>
		<td colspan='2'>
			<span>
				<strong>Use {rac.unsubscribe} to display the unsubscribe in the email</strong>
			</span>
		</td>
	</tr>
	<!--End Email Template Message shortcodes -->
	<tr>
		<td></td>
	</tr>

	<?php if ( 'send' != $rac_template_type ) { ?>
		<!--Start Template Name-->
		<tr>
			<td><?php esc_html_e( 'Template Name', 'recoverabandoncart' ) ; ?> : </td>
			<td>
				<input type="text" name="rac_template_name" id="rac_template_name" value="<?php echo esc_attr( $rac_template_name ) ; ?>" />
			</td>
		</tr>
		<!-- End Template Name-->
		<!-- Start Template Status-->
		<tr>
			<td><?php esc_html_e( 'Template Status', 'recoverabandoncart' ) ; ?>:</td>
			<td>
				<select name='rac_template_status' id='rac_template_status'>
					<?php echo do_shortcode( FP_RAC_Email_Template::rac_template_status_select_options( $status ) ) ; ?>
				</select>
			</td>
		</tr>
		<!-- End Template Status-->

		<!--Start Segmentation as Dropdown-->

		<!--Start Segmentation Lists-->
		<tr>
			<td><?php esc_html_e( 'Segmentation', 'recoverabandoncart' ) ; ?>:</td>
			<td>
				<select name='rac_template_segmentation[rac_template_seg_type]' id='rac_template_seg_type'>
					<?php echo do_shortcode( FP_RAC_Email_Template::rac_segmentation_select_options( $rac_segmentation[ 'rac_template_seg_type' ] ) ) ; ?>
				</select>
			</td>
		</tr>
		<!--End Segmentation Lists-->

		<!--Start Segmentation Minimum Order Total-->
		<tr class='rac_colsh rac_template_seg_odrer_count'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Minimum Order Total', 'recoverabandoncart' ) ; ?> :</label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_odrer_count_min]' id='rac_template_seg_odrer_count_min' class='fp_text_min_max' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_odrer_count_min' ] ) ; ?>" data-min='0' />
			</td>
		</tr>
		<!--End Segmentation Minimum Order Total-->

		<!--Start Segmentation Maximum Order Total-->
		<tr class='rac_colsh rac_template_seg_odrer_count'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Maximum Order Total  : ', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_odrer_count_max]' id='rac_template_seg_odrer_count_max' class='fp_text_min_max' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_odrer_count_max' ] ) ; ?>" data-min='0' />
			</td>
		</tr>
		<!--End Segmentation Maximum Order Total-->

		<!--Start Segmentation Minimum Order Amount-->
		<tr class='rac_colsh rac_template_seg_odrer_amount'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Minimum Order Amount :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_odrer_amount_min]' id='rac_template_seg_odrer_amount_min' class='fp_text_min_max' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_odrer_amount_min' ] ) ; ?>" data-min='1' />
			</td>
		</tr>
		<!--End Segmentation Minimum Order Amount-->

		<!--Start Segmentation Maximum Order Amount-->
		<tr class='rac_colsh rac_template_seg_odrer_amount'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Maximum Order Amount :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_odrer_amount_max]' id='rac_template_seg_odrer_amount_max' class='fp_text_min_max' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_odrer_amount_max' ] ) ; ?>" data-min='1' />
			</td>
		</tr>
		<!--End Segmentation Maximum Order Amount-->

		<!--Start Segmentation Minimum Cart Amount-->
		<tr class='rac_colsh rac_template_seg_cart_total'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Minimum Cart Amount :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_cart_total_min]' id='rac_template_seg_cart_total_min' class='fp_text_min_max' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_cart_total_min' ] ) ; ?>" data-min='1' />
			</td>
		</tr>
		<!--End Segmentation Minimum Cart Amount-->

		<!--Start Segmentation Maximum Cart Amount-->
		<tr class='rac_colsh rac_template_seg_cart_total'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Maximum Cart Amount :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_cart_total_max]' id='rac_template_seg_cart_total_max' class='fp_text_min_max' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_cart_total_max' ] ) ; ?>" data-min='1'/>
			</td>
		</tr>
		<!--End Segmentation Maximum Cart Amount-->

		<!--Start Segmentation From Cart Abandon Date -->
		<tr class='rac_colsh rac_template_seg_cart_date'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'From Abadon Date :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_cart_from_date]' id='rac_template_seg_cart_from_date' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_cart_from_date' ] ) ; ?>" />
			</td>
		</tr>
		<!--End Segmentation From Cart Abandon Date -->

		<!--Start Segmentation To Cart Abandon Date -->
		<tr class='rac_colsh rac_template_seg_cart_date'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'To Abadon Date :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_cart_to_date]' id='rac_template_seg_cart_to_date' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_cart_to_date' ] ) ; ?>" />
			</td>
		</tr>
		<!--End Segmentation To Cart Abandon Date -->

		<!--Start Segmentation Minimum Cart Quantity-->
		<tr class='rac_colsh rac_template_seg_cart_quantity'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Minimum Cart Quantity :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_cart_quantity_min]' id='rac_template_seg_cart_quantity_min' class="fp_text_min_max" value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_cart_quantity_min' ] ) ; ?>" data-min="1" />
			</td>
		</tr>
		<!--End Segmentation Minimum Cart Quantity-->

		<!--Start Segmentation Maximum Cart Quantity-->
		<tr class='rac_colsh rac_template_seg_cart_quantity'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Maximum Cart Quantity :', 'recoverabandoncart' ) ; ?></label>
				<input type='text' name='rac_template_segmentation[rac_template_seg_cart_quantity_max]' id='rac_template_seg_cart_quantity_max' class='fp_text_min_max' value="<?php echo esc_attr( $rac_segmentation[ 'rac_template_seg_cart_quantity_max' ] ) ; ?>" data-min='1' />
			</td>
		</tr>
		<!--End Segmentation Maximum Cart Quantity-->

		<!--Start Segmentation User Role-->
		<tr class='rac_colsh rac_template_seg_user_role'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'User Role', 'recoverabandoncart' ) ; ?> :</label>
				<select multiple='multiple' name='rac_template_segmentation[rac_template_seg_selected_user_role][]' class="fp-rac-select-field" id='rac_template_seg_selected_user_role' placeholder='<?php esc_attr_e( 'Serach a User Role', 'recoverabandoncart' ) ; ?>'>
					<?php echo do_shortcode( FP_RAC_Email_Template::rac_user_roles_select_options( $rac_segmentation[ 'rac_template_seg_selected_user_role' ] ) ) ; ?>
				</select>
			</td>
		</tr>
		<!--End Segmentation User Role-->

		<!--Start Select Product and Category Restriction-->
		<tr class='rac_colsh rac_template_seg_cart_product'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Product Selection', 'recoverabandoncart' ) ; ?>:</label>
				<select name='rac_template_segmentation[rac_template_seg_cart_product_category]' class='rac_template_seg_cart_product_category'>
					<?php echo do_shortcode( FP_RAC_Email_Template::rac_seg_product_incart_select_options( $rac_segmentation[ 'rac_template_seg_cart_product_category' ] ) ) ; ?>
				</select>
			</td>
		</tr>
		<!--End Select Product and Category Restriction-->

		<!--Start Search Product & Category Selection-->

		<!--Start Include Product Selection-->
		<tr class='rac_colsh rac_template_seg_cart_product rac_col_product_sh rac_include_product'>
			<td></td>
			<td>
				<?php
				//Include Product Search
				update_option( 'rac_template_segmentation[rac_template_seg_selected_product_in_cart]', $rac_segmentation[ 'rac_template_seg_selected_product_in_cart' ] ) ;
				fp_rac_common_function_for_search_products( 'rac_template_segmentation[rac_template_seg_selected_product_in_cart]', 'Include Products' ) ;
				?>
			</td>
		</tr>
		<!--End Include Product Selection-->

		<!--Start Exclude Product Selection-->
		<tr class='rac_colsh rac_template_seg_cart_product rac_col_product_sh rac_exclude_product'>
			<td></td>
			<td>
				<?php
				//Exclude Product Search
				update_option( 'rac_template_segmentation[rac_template_seg_selected_product_not_in_cart]', $rac_segmentation[ 'rac_template_seg_selected_product_not_in_cart' ] ) ;
				fp_rac_common_function_for_search_products( 'rac_template_segmentation[rac_template_seg_selected_product_not_in_cart]', 'Exclude Products' ) ;
				?>
			</td>
		</tr>
		<!--End Exclude Product Selection-->

		<!--Start Include Category Selection-->
		<tr class='rac_colsh rac_template_seg_cart_product rac_col_product_sh rac_include_category'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Include Categories', 'recoverabandoncart' ) ; ?> :</label>

				<select multiple='multiple' name='rac_template_segmentation[rac_template_seg_selected_category_in_cart][]' id='rac_template_seg_selected_category_in_cart' class="fp-rac-select-field" placeholder='<?php esc_attr_e( 'Serach a User Category', 'recoverabandoncart' ) ; ?>'>
					<?php echo do_shortcode( FP_RAC_Email_Template::rac_category_select_options( $rac_segmentation[ 'rac_template_seg_selected_category_in_cart' ] ) ) ; ?>
				</select>
			</td>
		</tr>
		<!--End Include Category Selection-->

		<!--Start Exclude Category Selection-->
		<tr class='rac_colsh rac_template_seg_cart_product rac_col_product_sh rac_exclude_category'>
			<td></td>
			<td>
				<label><?php esc_html_e( 'Exclude Categories', 'recoverabandoncart' ) ; ?> :</label>
				<select multiple='multiple' name='rac_template_segmentation[rac_template_seg_selected_category_not_in_cart][]' id='rac_template_seg_selected_category_not_in_cart' class="fp-rac-select-field" placeholder='<?php esc_attr_e( 'Serach a User Category', 'recoverabandoncart' ) ; ?>'>
					<?php echo do_shortcode( FP_RAC_Email_Template::rac_category_select_options( $rac_segmentation[ 'rac_template_seg_selected_category_not_in_cart' ] ) ) ; ?>
				</select>
			</td>
		</tr>
		<!--End Exclude Category Selection-->

		<!--End Search Product & Category Selection-->

	<?php } ?>
	<!--End Segmentation as Dropdown-->

	<!--Start Email Template Template Type-->
	<tr>
		<td><?php esc_html_e( 'Email Template Type', 'recoverabandoncart' ) ; ?>:</td>
		<td>
			<select name='rac_template_mail' class='rac_template_mail'>
				<?php echo do_shortcode( FP_RAC_Email_Template::rac_template_type_select_options( $mail ) ) ; ?>
			</select>
		</td>
	</tr>

	<!--Start Email Template Header Image For Html Template-->
	<tr class='rac_logo_link'>
		<td><?php esc_html_e( 'Header Image For HTML Template', 'recoverabandoncart' ) ; ?>:</td>
		<td>
			<input type='text' size='40' name='rac_template_link' id='rac_template_link' value='<?php echo esc_attr( $link ) ; ?>'/>
			<input class='upload_button' id='image_uploader' type='submit' value='<?php esc_attr_e( 'Media Uploader.' ) ; ?>' />
		</td>
	</tr>
	<!--End Email Template Header Image For Html Template-->

	<!--End Email Template Template Type-->

	<!--Start Email Template Sender Option-->
	<tr class = 'rac_email_sender'>
		<td><?php esc_html_e( 'Email Sender Option', 'recoverabandoncart' ) ; ?>: </td>
		<td>
			<input type='radio' name='rac_template_sender_opt' id='rac_sender_woo' value='woo' class='rac_sender_opt'  <?php checked( $sender_opt, 'woo' ) ; ?>><?php esc_html_e( 'woocommerce', 'recoverabandoncart' ) ; ?>
			<input type='radio' name='rac_template_sender_opt' id='rac_sender_local' value='local' class='rac_sender_opt'  <?php checked( $sender_opt, 'local' ) ; ?>><?php esc_html_e( 'local', 'recoverabandoncart' ) ; ?>
		</td>
	</tr>

	<!--Start Email Template Sender Option From Name-->
	<tr class='rac_local_senders'>
		<td>
			<label><?php esc_html_e( 'From Name', 'recoverabandoncart' ) ; ?> : </label>
		</td>
		<td>
			<input type='text' name='rac_template_from_name'  id='rac_template_from_name' value='<?php echo esc_attr( $from_name ) ; ?>' />
		</td>
	</tr>
	<!--End Email Template Sender Option From Name-->

	<!--Start Email Template Sender Option From Email-->
	<tr class='rac_local_senders'>
		<td>
			<label><?php esc_html_e( 'From Email', 'recoverabandoncart' ) ; ?> : </label>
		</td>
		<td>
			<input type='text' name='rac_template_from_email'  id='rac_template_from_email' value='<?php echo esc_attr( $from_email ) ; ?>' />
		</td>
	</tr>
	<!--End Email Template Sender Option From Email-->

	<!--End Email Template Sender Option-->

	<!--Start Email Template Blind carbon copy-->
	<tr>
		<td>
			<label><?php esc_html_e( 'Bcc', 'recoverabandoncart' ) ; ?> : </label>
		</td>
		<td>
			<input type='textarea' name='rac_template_blind_carbon_copy' id='rac_template_blind_carbon_copy' value='<?php echo esc_attr( $rac_blind_carbon_copy ) ; ?>' />
		</td>
	</tr>
	<!--End Email Template Blind carbon copy-->

	<!--Start Email Template Subject-->
	<tr>
		<td>
			<label><?php esc_html_e( 'Subject', 'recoverabandoncart' ) ; ?>:</label>
		</td>
		<td>
			<input type='text' name='rac_template_subject' id='rac_template_subject' spellcheck="true" value="<?php echo esc_attr( $subject ) ; ?>">
		</td>
	</tr>
	<!--End Email Template Subject-->
	<?php
	if ( 'send' != $rac_template_type ) {
		$label_text = 'edit' == $rac_template_type ? __( 'Send Email Duration', 'recoverabandoncart' ) : __( 'Duration to Send Email After Abandoned Cart', 'recoverabandoncart' ) ;
		?>
		<!--Start Email Template Mail Duration-->

		<tr>
			<td>
				<label> <?php esc_html_e( $label_text, 'recoverabandoncart' ) ; ?> : </label>
				<select name='rac_template_sending_type' id='rac_template_sending_type'>
					<?php echo do_shortcode( FP_RAC_Email_Template::rac_mail_duration_select_options( $sending_type ) ) ; ?>
				</select>
			</td>
			<td>
				<span>
					<input type='text' name='rac_template_sending_duration' id='rac_template_sending_duration' value='<?php echo esc_attr( $sending_duration ) ; ?>' />
				</span>
			</td>
		</tr>
		<!--End Email Template Mail Duration-->
	<?php } ?>
	<!--Start Email Template Cart Link Anchor Text-->
	<tr>
		<td><?php esc_html_e( 'Cart Link Anchor Text', 'recoverabandoncart' ) ; ?>: </td>
		<td>
			<input type='text' name='rac_template_anchor_text' id='rac_template_anchor_text' value='<?php echo esc_attr( $anchor_text ) ; ?>' />
		</td>
	</tr>
	<!--End Email Template Cart Link Anchor Text-->
	<!--Start Email Template Custom CSS-->
	<tr>
		<td><?php esc_html_e( 'Custom CSS', 'recoverabandoncart' ) ; ?>: </td>
		<td>
			<textarea name='rac_template_custom_css' id='rac_template_custom_css'><?php echo esc_html( $custom_css ) ; ?></textarea>
		</td>
	</tr>
	<!--End Email Template Custom CSS-->
	<!--Start Email Template Message-->
	<tr>
		<td> <?php esc_html_e( 'Message', 'recoverabandoncart' ) ; ?>:</td>
		<td>
			<?php wp_editor( $message, $msg_editorid, $msg_settings ) ; ?>
		</td>
	</tr>
	<!--End Email Template Message-->
</table>
