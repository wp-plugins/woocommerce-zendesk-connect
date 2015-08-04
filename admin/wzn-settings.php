<?php
/**
* Title: WordPress settings page for WooCommerce Zendesk Connect
* Description: Shows settings page for plugin
* Copyright: Copyright (c) 2013
* Company: WP Fortune
* @author Bart Pluijms
*/

/**
* Settings page starts here 
* @since 0.0.5
*/
function woocommerce_wzn_page() {

	// Check the user capabilities
	if ( !current_user_can( 'manage_woocommerce' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'woo-wzn' ) );
	}
	
	// Save all settings
	if ( isset( $_POST['wzn_fields_submitted'] ) && $_POST['wzn_fields_submitted'] == 'submitted' ) {
	check_admin_referer( 'wzn_nonce');
	
	if(!is_email($_POST['woo_wzn_email'])) {
		$_POST['woo_wzn_email']='';
	}
	if(!is_email($_POST['woo_wzn_apiuser'])) {
		$_POST['woo_wzn_apiuser']='';
	}
	
	update_option('woo_wzn_use_style','off');
	update_option('woo_wzn_create_org','off');
	update_option('woo_wzn_create_user','off');
	update_option('woo_wzn_verify_user','off');
	update_option('woo_wzn_email_enable','off');
		foreach ( $_POST as $key => $value ) {
			if($key!="woo_wzn_fbt") $value=sanitize_text_field($value);
			if ( get_option( $key ) != $value ) {
				update_option( $key, $value );
			} else {
				add_option( $key, $value, '', 'no' );
			}
		}
	}
	
	// Load settings page specific scripts
	wp_enqueue_script("jquery-ui-tabs");
	?>
	<style>#poststuff h2.nav-tab-wrapper{padding-bottom:0px;}.tab{display:none;}.tab.active{display:block;}table td p {padding:0px !important;}</style>
	<script>
		jQuery(document).ready(function(){	
			var active_tab = window.location.hash.replace('#top#','');
			if ( active_tab == '' )
			active_tab = 'orguser';
			jQuery('#'+active_tab).addClass('active');
			jQuery('#'+active_tab+'-tab').addClass('nav-tab-active');
		
			jQuery('.nav-tab-wrapper a').click(function() {
				jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
				jQuery('.tab').removeClass('active');
	
				var id = jQuery(this).attr('id').replace('-tab','');
				jQuery('#'+id).addClass('active');
				jQuery(this).addClass('nav-tab-active');
			});
		});
	</script>

	<?php // start output ?>
	<div class="wrap">
	  <div id="icon-options-general" class="icon32"></div>
	  <h2><?php _e( 'WooCommerce Zendesk Connect', 'woo-wzn' ); ?></h2>
	  <?php if ( isset( $_POST['wzn_fields_submitted'] ) && $_POST['wzn_fields_submitted'] == 'submitted' ) { ?>
			<div id="message" class="updated fade"><p><strong><?php _e( 'Your settings have been saved.', 'woo-wzn' ); ?></strong></p></div>
		<?php } ?>
		<div id="content">
		  
			<div id="poststuff">
				<div style="float:left; width:72%; padding-right:3%;">
					<div id="tabs">
					<h2 class=nav-tab-wrapper>

					  <a id=orguser-tab class="nav-tab" href="#top#orguser"><?php _e( 'Users & Organizations', 'woo-wzn' ); ?></a>
					  <a id=contact-tab class="nav-tab" href="#top#contact"><?php _e( 'Contact methods', 'woo-wzn' ); ?></a>
					  <a id=feedback-tab class="nav-tab" href="#top#feedback"><?php _e( 'Integrations', 'woo-wzn' ); ?></a>
					  <a id=zendesk-tab class="nav-tab" href="#top#zendesk"><?php _e( 'Authentication', 'woo-wzn' ); ?></a>

					</h2>
				<form method="post" action="" id="wzn_settings">
				  <?php wp_nonce_field('wzn_nonce'); ?>
				  <input type=hidden name=woo_wzn_version value="0.0.5">
				  <input type="hidden" name="wzn_fields_submitted" value="submitted">
					<div id="zendesk" class="tab">
						<h2><?php _e( 'Authentication with Zendesk', 'woo-wzn' ); ?></h2>
						<div class="inside wzn-settings">
							<table class="form-table">
								<tr>
    								<th>
    									<label for="woo_wzn_subdomain"><b><?php _e( 'Account subdomain:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
    									<input type="text" name="woo_wzn_subdomain" placeholder="yourname" size=70 required value="<?php echo esc_attr(get_option('woo_wzn_subdomain'));?>">
    									<br>
										<span class="description">
											<?php _e( 'Enter your Zendesk account subdomain. For example: enter <code>yourname</code> if your zendesk is located at yourname.zendesk.com.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_apikey"><b><?php _e( 'Zendesk API token:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
    									<input type="text" name="woo_wzn_apikey" size=70 required value="<?php echo esc_attr(get_option('woo_wzn_apikey'));?>">
    									<br>
										<span class="description">
											<?php _e( 'Enter the API token.', 'woo-wzn' );?><br>
											<?php _e( 'To generate a new API key, log-in to your Zendesk account, go to Channels --> API --> Token Access. Click on enable and copy paste the API token.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_apiuser"><b><?php _e( 'Zendesk API administrator:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
    									<input type="email" name="woo_wzn_apiuser" size=70 placeholder="yourname@me.com" required value="<?php echo esc_attr(get_option('woo_wzn_apiuser'));?>">
    									<br>
										<span class="description">
											<?php _e( 'Enter the email address of the user who generated the Zendesk API key.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>								
								<tr>
									<td colspan=2>
										<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Changes', 'woo-wzn' ); ?>" /></p>
									</td>
								</tr>
							</table>
						</div>
					</div><div id="orguser" class="tab">
						<h2><?php _e( 'Users & Organizations', 'woo-wzn' ); ?></h2>
						<div class="inside wzn-settings">
							<table class="form-table">
							    <tr>
    								<th>
    									<label for="woo_wzn_con_type"><b><?php _e( 'Connection type:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
    									<select name="woo_wzn_con_type" class="select">
											<option value="thankyou" <?php selected( esc_attr(get_option( 'woo_wzn_con_type' )), 'thankyou' ); ?>><?php echo _e( 'After checkout', 'woo-wzn' );?></option>
											<option value="checkout" <?php selected( esc_attr(get_option( 'woo_wzn_con_type' )), 'checkout' ); ?>><?php echo _e( 'On checkout', 'woo-wzn' );?></option>
										</select><br>
										<span class="description">
											<?php _e( 'Choose when you want to connect to Zendesk and add users / organizations:', 'woo-wzn' );
											echo '<br><strong>'.__( 'On checkout', 'woo-wzn' ).':</strong> '.__( 'Connection is made during checkout, which makes the checkout process a bit slower.', 'woo-wzn' ).'
											<br><strong>'.__( 'After checkout', 'woo-wzn' ).':</strong> '.__( 'Connection is made after checkout, which is a faster, but when a customer does not return to your page after payment no user is created.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_create_user"><b><?php _e( 'Create users:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
										<input id=woo_wzn_create_user type=checkbox <?php if(esc_attr(get_option( 'woo_wzn_create_user' ))=='on' || esc_attr(get_option( 'woo_wzn_create_user' ))=="") { echo 'checked';}?> name="woo_wzn_create_user"> <label for="woo_wzn_create_user"><?php _e( 'Enable auto creation of Zendesk end-users.', 'woo-wzn' ); ?></label><br>
										<span class="description">
											<?php _e( 'Create Zendesk end-users from new customers on WooCommerce checkout.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_verify_user"><b><?php _e( 'Verify users:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
										<input id=woo_wzn_verify_user type=checkbox <?php if(esc_attr(get_option( 'woo_wzn_verify_user' ))!='off') { echo 'checked';}?> name="woo_wzn_verify_user"> <label for="woo_wzn_verify_user"><?php _e( 'Enable verify upon creation.', 'woo-wzn' ); ?></label><br>
										<span class="description">
											<?php _e( 'By default, Zendesk sends a verification email to a user when the user\'s email address is added to a profile. Check to enable this option and verify user upon creation.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
    								<th>
    									<b><?php _e( 'Zendesk organizations:', 'woo-wzn' ); ?></b>
    								</th>
    								<td>
										<input id=woo_wzn_create_org type=checkbox <?php if(esc_attr(get_option( 'woo_wzn_create_org' ))=='on' || esc_attr(get_option( 'woo_wzn_create_org' ))=="") { echo 'checked';}?> name="woo_wzn_create_org"> <label for="woo_wzn_create_org"><?php _e( 'Enable auto creation of Zendesk Organizations.', 'woo-wzn' ); ?></label><br>
										<span class="description">
											<?php _e( 'Create Zendesk organization if a company name is provided by customer.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
									<td colspan=2>
										<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Changes', 'woo-wzn' ); ?>" /></p>
									</td>
								</tr>
							</table>
						</div>
					</div><div id="contact" class="tab">
						<h2><?php _e( 'Zendesk contact methods', 'woo-wzn' ); ?></h2>
						<div class="inside wzn-settings">
						<p><?php _e( 'Specify in which ways your customers can contact you.','woo-wzn');?></p>
						
							<table class="form-table">
								<tr>
									<td colspan=2><h3><?php _e( 'Add support details to customer emails','woo-wzn');?></h3>
									</td>
								</tr>
								<tr>
    								<th>
    									<b><?php _e( 'Email support section', 'woo-wzn' );?></b>
    								</th>
    								<td>
										<input id=woo_wzn_email_enable type=checkbox <?php if(esc_attr(get_option( 'woo_wzn_email_enable' ))!='off') { echo 'checked';}?> name="woo_wzn_email_enable"> <label for="woo_wzn_email_enable"><?php _e( 'Add a support section to customer emails.', 'woo-wzn' ); ?></label><br>
    									<span class="description">
											<?php _e('A new section will be added to all customer emails with support information.','woo_wzn');?>
										</span>
    								</td>
    							</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_email_title"><b><?php _e( 'Support heading:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
									  <select name="woo_wzn_email_title" class="select">
										<option value="" <?php selected( get_option( 'woo_wzn_email_title' ), '' ); ?>><?php echo _e( 'No heading', 'woo-wzn' );?></option>
										<option value="Need Support?" <?php selected( get_option( 'woo_wzn_email_title' ), 'Need Support?' ); ?>><?php echo _e( 'Need Support?', 'woo-wzn' );?></option>
										<option value="Any Questions?" <?php selected( get_option( 'woo_wzn_email_title' ), 'Any Questions?' ); ?>><?php echo _e( 'Any Questions?', 'woo-wzn' );?></option>
										<option value="Need Help?" <?php selected( get_option( 'woo_wzn_email_title' ), 'Need Help?' ); ?>><?php echo _e( 'Need Help?', 'woo-wzn' );?></option>
										<option value="Service" <?php selected( get_option( 'woo_wzn_email_title' ), 'Service' ); ?>><?php echo _e( 'Service', 'woo-wzn' );?></option>
										<option value="Ask us" <?php selected( get_option( 'woo_wzn_email_title' ), 'Ask us' ); ?>><?php echo _e( 'Ask us', 'woo-wzn' );?></option>
										<option value="More information" <?php selected( get_option( 'woo_wzn_email_title' ), 'More information' ); ?>><?php echo _e( 'More information', 'woo-wzn' );?></option>
									  </select><br>
									  <span class="description">
											<?php _e( 'Specify heading of the new support section.', 'woo-wzn');?>
									  </span>
								    </td>
								</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_email_text"><b><?php _e( 'Support text:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
									  <input type="text" name="woo_wzn_email_text" size=70 placeholder="<?php _e('Our Knowledgebase is the first place to look for help with our products. If you have any questions, please submit a ticket to our Help Desk.','woo-wzn');?>" value="<?php if(get_option('woo_wzn_email_text')=="") { _e('Our Knowledgebase is the first place to look for help with our products. If you have any questions, please submit a ticket to our Help Desk.','woo-wzn'); } else { echo get_option('woo_wzn_email_text');} ?>"><br>
									  <span class="description">
											<?php _e( 'We will link this text to your Zendesk support desk.', 'woo-wzn');?>
									  </span>
								    </td>
								</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_email_pos"><b><?php _e( 'Position:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
										<select name="woo_wzn_email_pos" class="select">
											<option value="before" <?php selected( get_option( 'woo_wzn_email_pos' ), 'before' ); ?>><?php echo _e( 'Before order item table', 'woo-wzn' );?></option>
											<option value="after" <?php selected( get_option( 'woo_wzn_email_pos' ), 'after' ); ?>><?php echo _e( 'After order item table', 'woo-wzn' );?></option>
										</select><br>
										<span class="description">
											<?php _e( 'Where do you want to show the new support section?', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
									<td colspan=2><h3><?php _e( 'Add support ticket forms to WooCommerce','woo-wzn');?></h3>
									</td>
								</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_email"><b><?php _e( 'Support email:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
									  <input type="email" name="woo_wzn_email" size=70 placeholder="yourname@me.com" value="<?php echo esc_attr(get_option('woo_wzn_email'));?>">
									  <br>
									  <span class="description">
											<?php _e( 'This email address will be used to contact you in all below forms.', 'woo-wzn');?>
									  </span>
								    </td>
								</tr>
								
								<tr>
    								<th>
    									<label for="woo_wzn_myaccount_title"><b><?php _e( 'Order overview page:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
										<select name="woo_wzn_myaccount_title" class="select">
											<option value="" <?php selected( get_option( 'woo_wzn_myaccount_title' ), '' ); ?>><?php echo _e( "Don't add form", 'woo-wzn' );?></option>
											<option value="Need Support?" <?php selected( get_option( 'woo_wzn_myaccount_title' ), 'Need Support?' ); ?>><?php echo _e( 'Need Support?', 'woo-wzn' );?></option>
											<option value="Any Questions?" <?php selected( get_option( 'woo_wzn_myaccount_title' ), 'Any Questions?' ); ?>><?php echo _e( 'Any Questions?', 'woo-wzn' );?></option>
											<option value="Need Help?" <?php selected( get_option( 'woo_wzn_myaccount_title' ), 'Need Help?' ); ?>><?php echo _e( 'Need Help?', 'woo-wzn' );?></option>
											<option value="Service" <?php selected( get_option( 'woo_wzn_myaccount_title' ), 'Service' ); ?>><?php echo _e( 'Service', 'woo-wzn' );?></option>
											<option value="Ask us" <?php selected( get_option( 'woo_wzn_myaccount_title' ), 'Ask us' ); ?>><?php echo _e( 'Ask us', 'woo-wzn' );?></option>
											<option value="More information" <?php selected( get_option( 'woo_wzn_myaccount_title' ), 'More information' ); ?>><?php echo _e( 'More information', 'woo-wzn' );?></option>
										</select><br>
										<span class="description">
											<?php _e( 'Enable Zendesk ticket form on order overview / My Account page.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_myaccount_pos"><b><?php _e( 'Position:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
										<select name="woo_wzn_myaccount_pos" class="select">
											<option value="before" <?php selected( get_option( 'woo_wzn_myaccount_pos' ), 'before' ); ?>><?php echo _e( 'Before order item table', 'woo-wzn' );?></option>
											<option value="after" <?php selected( get_option( 'woo_wzn_myaccount_pos' ), 'after' ); ?>><?php echo _e( 'After order item table', 'woo-wzn' );?></option>
										</select><br>
										<span class="description">
											<?php _e( 'Where do you want to show the new support section on the order detail page?', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
									<td colspan=2>
										<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Changes', 'woo-wzn' ); ?>" /></p>
									</td>
								</tr>
							</table>
						</div>
					</div><div id="feedback" class="tab">
						<h2><?php _e( 'Zendesk Feedback Tab', 'woo-wzn' ); ?></h2>
						<div class="inside wzn-settings">
						<p><?php _e( 'The Feedback Tab lets you add support options to your web page. You can configure your Feedback Tab so that users can submit tickets, search the forums, or chat with agents.','woo-wzn');?></p>
						
							<table class="form-table">
								<tr>
    								<th>
    									<label for="woo_wzn_fbt"><b><?php _e( 'Code snippet:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
									  <textarea style="width:90%;height:150px;" id=woo_wzn_fbt name=woo_wzn_fbt><?php echo esc_textarea(stripslashes(get_option('woo_wzn_fbt')));?></textarea>
									  <br>
									  <span class="description">
											<?php echo sprintf(__( 'Copy / paste your Zendesk Feedback Tab code snippet. You can generate the code snippet from %s.', 'woo-wzn' ),'<a href="https://'.get_option('woo_wzn_subdomain').'.zendesk.com/agent/#/admin/dropboxes" target=_blank>'.__( 'this page', 'woo-wzn' ).'</a>');?>
										</span>
								    </td>
								</tr>
								<tr>
    								<th>
    									<label for="woo_wzn_fbt_pos"><b><?php _e( 'Position:', 'woo-wzn' ); ?></b></label>
    								</th>
    								<td>
										<select name="woo_wzn_fbt_pos" class="select">
											<option value="" <?php selected( get_option( 'woo_wzn_fbt_pos' ), '' ); ?>><?php echo _e( 'Don\'t add', 'woo-wzn' );?></option>
											<option value="all" <?php selected( get_option( 'woo_wzn_fbt_pos' ), 'all' ); ?>><?php echo _e( 'All pages', 'woo-wzn' );?></option>
											<option value="store" <?php selected( get_option( 'woo_wzn_fbt_pos' ), 'store' ); ?>><?php echo _e( 'Only shop pages', 'woo-wzn' );?></option>
										</select><br>
										<span class="description">
											<?php _e( 'Choose where you want to show the Feedback Tab.', 'woo-wzn' );?>
										</span>
    								</td>
    							</tr>
								<tr>
									<td colspan=2>
										<p class="submit"><input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Changes', 'woo-wzn' ); ?>" /></p>
									</td>
								</tr>
							</table>
						</div>
					</div></form>
					</div>
				</div>
				<?php // right column with Plugin information ?>
				<div style="float:right; width:25%;">
					<div class="postbox">
						<h3><?php _e( 'Buy Pro!', 'woo-wzn' ); ?></h3>
						<div class="inside zendesk-preview">

                            <p><?php echo __( 'Check out our ', 'woo-wzn' ); ?> <a href="https://wpfortune.com/shop/plugins/woocommerce-zendesk-connect/">website</a> <?php _e('to find out more about WooCommerce Zendesk Connect.', 'woo-wzn' );?></p>
							<p><?php _e('For only &euro; 35,00 you will get a lot of features and access to our support section.', 'woo-wzn' );?></p>
							<p><?php _e('A couple of features:', 'woo-wzn' );?></p>
                            	<ul style="list-style:square;padding-left:20px;margin-top:-10px;"><li><strong><?php _e('New', 'woo-wzn' );?></strong>: <?php _e('Add order information to a user in Zendesk', 'woo-wzn' );?></li><li><?php _e('Add tags to users and organizations', 'woo-wzn' );?></li><li><?php _e('Add customer address to users or organizations', 'woo-wzn' );?></li><li><?php _e('Add ticket form to product detail page', 'woo-wzn' );?></li><li><?php _e('Add ticket form to order detail page', 'woo-wzn' );?></li></ul>

						</div>
					</div>
				</div>
			</div>
	</div>
</div>
<?php }