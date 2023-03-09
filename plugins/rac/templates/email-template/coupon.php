
<table class='widefat'>
	<tr>
		<td><label><?php esc_html_e( 'Coupon Value is Fetched From' , 'recoverabandoncart' ) ; ?>:</label></td>
		<td>
			<?php fp_rac_tool_tip( __( '"Global Settings" - coupon will be created based on the value configured in the coupon creation global settings. "Current Email Template" - coupon will be created based on the value configured in this email template.' , 'recoverabandoncart' ) ) ; ?>
			<select name='rac_template_coupon_mode' id="rac_template_coupon_mode">
				<option value="global" <?php selected( 'global' , $coupon_mode ) ; ?>><?php esc_html_e( 'Global Settings' , 'recoverabandoncart' ) ; ?></option>
				<option value="specific" <?php selected( 'specific' , $coupon_mode ) ; ?>><?php esc_html_e( 'Current Email Template' , 'recoverabandoncart' ) ; ?></option>
			</select>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td>
			<label><?php esc_html_e( 'Coupon Code Prefix Type' , 'recoverabandoncart' ) ; ?></label>
		</td>
		<td>
			<?php fp_rac_tool_tip( __( 'Select Prefix Text in Coupon Code' , 'recoverabandoncart' ) ) ; ?>
			<select name='rac_template_coupon[prefix_type]' class="rac_template_coupon_prefix_type">
				<option value="1" <?php selected( '1' , $template_coupon[ 'prefix_type' ] ) ; ?>><?php esc_html_e( 'Default' , 'recoverabandoncart' ) ; ?></option>
				<option value="2" <?php selected( '2' , $template_coupon[ 'prefix_type' ] ) ; ?>><?php esc_html_e( 'Custom' , 'recoverabandoncart' ) ; ?></option>
			</select>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Coupon Code Prefix' , 'recoverabandoncart' ) ; ?> </label></td>
		<td>
			<?php fp_rac_tool_tip( __( 'Enter Custom Prefix Text for Coupon Code' , 'recoverabandoncart' ) ) ; ?>
			<input type='text' name='rac_template_coupon[prefix]' class="rac_template_coupon_prefix" value="<?php echo esc_attr( $template_coupon[ 'prefix' ] ) ; ?>"/>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Discount Type' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<?php fp_rac_tool_tip( __( 'Please Select which type of discount should be applied' , 'recoverabandoncart' ) ) ; ?>
			<select name='rac_template_coupon[discount_type]'>
				<option value="fixed_cart" <?php selected( 'fixed_cart' , $template_coupon[ 'discount_type' ] ) ; ?>><?php esc_html_e( 'Amount' , 'recoverabandoncart' ) ; ?></option>
				<option value="percent" <?php selected( 'percent' , $template_coupon[ 'discount_type' ] ) ; ?>><?php esc_html_e( 'Percentage' , 'recoverabandoncart' ) ; ?></option>
			</select>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Value' , 'recoverabanoncart' ) ; ?></label></td>
		<td>
			<?php fp_rac_tool_tip( __( 'Enter the value to reduce in currency or % based on the Type of Discount Selected without any Symbols' , 'recoverabandoncart' ) ) ; ?>
			<input type='text' name='rac_template_coupon[value]' value="<?php echo esc_attr( $template_coupon[ 'value' ] ) ; ?>" />
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Validity in Days' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<?php fp_rac_tool_tip( __( 'Enter a value(days in number) for how long the Coupon should be Active' , 'recoverabandoncart' ) ) ; ?>
			<input type='text' name='rac_template_coupon[validity]' value="<?php echo esc_attr( $template_coupon[ 'validity' ] ) ; ?>" />
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Minimum Amount for Coupon Usage' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<input type='text' name='rac_template_coupon[min_amount]' value="<?php echo esc_attr( $template_coupon[ 'min_amount' ] ) ; ?>" />
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Maximum Amount for Coupon Usage' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<input type='text' name='rac_template_coupon[max_amount]' value="<?php echo esc_attr( $template_coupon[ 'max_amount' ] ) ; ?>" />
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Individual Use Only' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<input type='checkbox' name='rac_template_coupon[indivitual_use_only]' value="yes" <?php checked( 'yes' , $template_coupon[ 'indivitual_use_only' ] ) ; ?>/>
			<span><?php esc_html_e( 'Check this box if the coupon cannot be used in conjunction with other coupons.' , 'recoverabandoncart' ) ; ?></span>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Exclude sale items' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<input type='checkbox' name='rac_template_coupon[exclude_sale_items]' value="yes" <?php checked( 'yes' , $template_coupon[ 'exclude_sale_items' ] ) ; ?>/>
			<span><?php esc_html_e( 'Check this box if the coupon should not apply to items on sale. Per-item coupons will only work if the item is not on sale. Per-cart coupons will only work if there are items in the cart that are not on sale' , 'recoverabandoncart' ) ; ?></span>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Allow Free Shipping' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<input type='checkbox' name='rac_template_coupon[allow_free_shipping]' value="yes" <?php checked( 'yes' , $template_coupon[ 'allow_free_shipping' ] ) ; ?>/>
			<span><?php esc_html_e( 'Check this box if the coupon grants free shipping. A free shipping method must be enabled in your shipping zone and be set to require "a valid free shipping coupon" (see the "Free Shipping Requires" setting).' , 'recoverabandoncart' ) ; ?></span>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Restrict Coupons to Issued Users' , 'recoverabandoncart' ) ; ?></label></td>
		<td>
			<input type='checkbox' name='rac_template_coupon[allow_customer_email]' value="yes" <?php checked( 'yes' , $template_coupon[ 'allow_customer_email' ] ) ; ?>/>
			<span><?php esc_html_e( 'When enabled, the issued Coupons can be used only by the users to whom it was issued.' , 'recoverabandoncart' ) ; ?></span>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Include Products' , 'recoverabandoncart' ) ; ?> </label></td>
		<td>
			<?php
			//Include Product Search
			fp_rac_common_function_for_search_products( 'rac_template_coupon[include_products]' , false , $template_coupon[ 'include_products' ] ) ;
			?>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Exclude Products' , 'recoverabandoncart' ) ; ?> </label></td>
		<td>
			<?php
			//Exclude Product Search
			fp_rac_common_function_for_search_products( 'rac_template_coupon[exclude_products]' , false , $template_coupon[ 'exclude_products' ] ) ;
			?>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Include Categories' , 'recoverabandoncart' ) ; ?> </label></td>
		<td>
			<?php fp_rac_tool_tip( __( 'Select the Categories to which the coupons from abandoned cart emails can be applied' , 'recoverabandoncart' ) ) ; ?>
			<select multiple='multiple' name='rac_template_coupon[ínclude_categories][]'  id="rac_template_coupon_include_categories" class="fp-rac-select-field" placeholder='<?php esc_attr_e( 'Serach a User Category' , 'recoverabandoncart' ) ; ?>'>
				<?php echo do_shortcode( FP_RAC_Email_Template::rac_category_select_options( $template_coupon[ 'ínclude_categories' ] ) ) ; ?> 
			</select>
		</td>
	</tr>
	<tr class="rac_template_coupon">
		<td><label><?php esc_html_e( 'Exclude Categories' , 'recoverabandoncart' ) ; ?> </label></td>
		<td>
			<?php fp_rac_tool_tip( __( 'Select the Categories to which the coupons from abandoned cart emails cannot be applied' , 'recoverabandoncart' ) ) ; ?>
			<select multiple='multiple' name='rac_template_coupon[exclude_categories][]' id="rac_template_coupon_exclude_categories" class="fp-rac-select-field" placeholder='<?php esc_attr_e( 'Serach a User Category' , 'recoverabandoncart' ) ; ?>'>
				<?php echo do_shortcode( FP_RAC_Email_Template::rac_category_select_options( $template_coupon[ 'exclude_categories' ] ) ) ; ?> 
			</select>
		</td>
	</tr>
</table>
