<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* This file contains all general functions for WooCommerce Zendesk Connect
**/
require_once('wzn-zendesk.php');
/** 
* Check if Woo_Check already exists, otherwhiste require
* @since 0.0.6
*/
if ( ! class_exists( 'WOO_Check' ) )
	require_once 'class-check-woocommerce.php';

/**
* WC Detection
* @since 0.0.5
*/
if ( ! function_exists( 'is_woocommerce_active' ) ) {
	function is_woocommerce_active() {
		return WOO_Check::woocommerce_active_check();
	}
}

/**
* Generate admin messages
* @since 0.0.5
*/
function wznMessage($message, $errormsg = false)
{
	if ($errormsg) { echo '<div id="message" class="error">';}
	else {echo '<div id="message" class="updated fade">';}
	echo "<p><strong>$message</strong></p></div>";
}    
function wznAdminMessages() {wznMessage(__( 'WooCommerce is not active. Please activate WooCommerce before using WooCommerce Zendesk Connect plugin.', 'woo-wzn'), true);}

/** 
* WordPress Administration Menu
* Shows WooCommerce submenu item for plugin
* @since 0.0.5
*/
function woocommerce_wzn_admin_menu() {
	$page = add_submenu_page('woocommerce', __( 'Zendesk Connect', 'woo-wzn' ), __( 'Zendesk Connect', 'woo-wzn' ), 'manage_woocommerce', 'woocommerce_wzn', 'woocommerce_wzn_page' );
}

/**
* Add basic frontend styling if is choosen in admin
* Since 0.0.5
*/
function woo_wzn_styles() {
	wp_enqueue_style('woo-wzn-style', plugins_url('assets/css/woo-wzn.css',dirname(__FILE__)));
}

/**
* Zendesk connection through api
* @since 0.0.5
*/
function woo_wzn_zendesk() {
	return new zendesk(get_option('woo_wzn_apikey'), get_option('woo_wzn_apiuser'), get_option('woo_wzn_subdomain'), $suffix = '.json', $test = false);
}

/**
* Create Zendesk ticket
* @since 0.0.5
*/
function woo_wzn_ticket($args=array()) {
	$zendesk = woo_wzn_zendesk();
	$recp=get_option('woo_wzn_email');
	
	//check if user already in Zendesk
	$user_meta=get_user_meta($args['user_id']);
	$create  = json_encode(array('ticket' => array('subject' => $args['subject'], 'description' => $args['body'], 'requester' => array('name' => $args['name'], 'email' => $args['email']),'recipient'=>$recp,'requester_id'=>$user_meta['wzn_user'][0],'organization_id'=>$user_meta['wzn_org'][0])), JSON_FORCE_OBJECT);
	$data    = $zendesk->call("/tickets", $create, "POST");
}

/**
* Create or update Zendesk end-user on customer checkout
* @since 0.0.5
*/
function woo_wzn_user($arr) {
	$user_id = get_current_user_id();
	$zendesk = woo_wzn_zendesk();
	
	// get Organization id;
	if(get_option('woo_wzn_create_org')=='on') {
	  $org_id=woo_wzn_org($arr['org']);
	} else {
	  $org_id='';
	}
	
	// search if user already exists
	$data    = $zendesk->call("/users/search.json?query=".$arr['mail'],'','GET');
	// check if user id exists
	$tags=str_replace(' ', '_', str_replace(', ', ',', get_option('woo_wzn_taguser')));
	if(get_option('woo_wzn_verify_user')=='on') {
		$verified=true;
	} else {
	    $verified=false;
	}
	if(isset($data->users[0]->id) && $data->users[0]->id!="") {
	  // check if the details are already listed, if not add it to existing details.
	  if (strlen(strstr($data->users[0]->details,$arr['details']))==0 && $arr['details']!="") {
		$details=$arr['details'].'
=========== 
'.$data->users[0]->details;
	  } else {$details=$data->users[0]->details;}
	  // check if the notes are already listed, if not add it to existing notes.
	  if (strlen(strstr($data->users[0]->notes,$arr['notes']))==0 && $arr['notes']!="") {
		$notes=$arr['notes'].'
=========== 
'.$data->users[0]->notes;
	  } else {$notes=$data->users[0]->notes;}
	  // update user with new data
	  $update  = json_encode(array('user' => array('id'=>$data->users[0]->id,'name'=>$arr['name'],'email' => $arr['mail'],'role'=>'end-user','verified'=>$verified,'phone'=>$arr['phone'],'details'=>$details,'notes'=>$notes,'organization_id'=>$org_id,'tags'=>$tags)), JSON_FORCE_OBJECT);
	  $data    = $zendesk->call("/users/".$data->users[0]->id,$update,'PUT');
		if ( get_user_meta($user_id, 'wzn_user') !=$data->user->id ) {
			update_user_meta($user_id, 'wzn_user', $data->user->id);
		} else {
			add_user_meta($user_id, 'wzn_user', $data->user->id);
		}
		//print_r(get_user_meta($user_id));
	} else {
	  // create user
	  $create  = json_encode(array('user' => array('name'=>$arr['name'],'email' => $arr['mail'],'role'=>'end-user','verified'=>$verified,'phone'=>$arr['phone'],'details'=>$arr['details'],'notes'=>$arr['notes'],'organization_id'=>$org_id,'tags'=>$tags)), JSON_FORCE_OBJECT);
	  $data    = $zendesk->call("/users",$create,'POST');
	  		if ( get_user_meta($user_id, 'wzn_user') !=$data->id ) {
			update_user_meta($user_id, 'wzn_user', $data->id);
		} else {
			add_user_meta($user_id, 'wzn_user', $data->id);
		}

	}
}

/**
* Create or update Zendesk end-user on customer checkout
* @since 0.0.5
*/
function woo_wzn_org($org,$org_id='') {
	$zendesk = woo_wzn_zendesk();
	global $user_id;
	// search if org already exists
	// if given org_id (for update)
	if($org_id=='') {
	  $url='/search.json?query=type:organization';
	  $url.=urlencode(' "'.$org.'"');
	} else {
	  $url='/organizations/'.$org_id;
	}
	$data    = $zendesk->call($url,'','GET');
	
	$tags=str_replace(' ', '_', str_replace(', ', ',', get_option('woo_wzn_tagorg')));
	
	// check if organization id exists
	$user_id = get_current_user_id();
	
	if(isset($data->organization->id) && $data->organization->id!="" && $org_id!="") {
	  // update org with new data
	  $update  = json_encode(array('organization' => array('name'=>$org,'tags'=>$tags)), JSON_FORCE_OBJECT);
	  $data    = $zendesk->call("/organizations/".$org_id,$update,'PUT');
	  return 'updated';
	} elseif((isset($data->results[0]->id) && $data->results[0]->id!="")) {
		if ( get_user_meta($user_id, 'wzn_org') !=$data->results[0]->id ) {
				update_user_meta($user_id, 'wzn_org', $data->results[0]->id);
			} else {
				add_user_meta($user_id, 'wzn_org', $data->results[0]->id);
			}
	  return $data->results[0]->id;
	} else {
	  // create org
	  $create  = json_encode(array('organization' => array('name'=>$org,'tags'=>$tags)), JSON_FORCE_OBJECT);
	  $data    = $zendesk->call("/organizations",$create,'POST');
		if ( get_user_meta($user_id, 'wzn_org') !=$data->organization->id ) {
		  update_user_meta($user_id, 'wzn_org', $data->organization->id);
		} else {
		  add_user_meta($user_id, 'wzn_org', $data->organization->id);
		}
	  return $data->organization->id;
	}
	return false;
}

/** 
* Hook into the WooCommerce checkout
* @since 0.0.5
*/
function woo_wzn_checkout_process() {
global $woocommerce;
  // Used fields for Zendesk
  $arr['name']=$_POST['billing_first_name'].' '.$_POST['billing_last_name'];
  $arr['org']=$_POST['billing_company'];
  $arr['mail']=$_POST['billing_email'];
  $arr['phone']=$_POST['billing_phone'];
  $address='';
  if($_POST['billing_address_1']!="") { $address.=$_POST['billing_address_1'].'\n'; }
  if($_POST['billing_address_2']!="") { $address.=$_POST['billing_address_2'].'\n'; }
  if($_POST['billing_postcode']!="") { $address.=$_POST['billing_postcode'].' '; }
  if($_POST['billing_city']!="") { $address.=$_POST['billing_city'].'\n'; }
  if($_POST['billing_state']!="") { $address.=$_POST['billing_state'].'\n'; }
  if($_POST['billing_city']!="") { $address.=$_POST['billing_city'].'\n'; }
  if($_POST['billing_country']!="") { $address.=woo_wzn_country($_POST['billing_country']).'\n'; }
  if(get_option( 'woo_wzn_address' )=='udetails') {
	$arr['details']=$address;
  } elseif(get_option( 'woo_wzn_address' )=='unotes') {
    $arr['notes']=$address;
  }
  /* other fields not used now (for later reference)
  [billing_country], [billing_address_1, [billing_address_2], [billing_postcode], [billing_city], [billing_state]
  /**/
  
  // Create Zendesk user from post data
  if(get_option('woo_wzn_create_user')=='on') {
    // if create user option is yes
    woo_wzn_user($arr);
  } elseif(get_option('woo_wzn_create_org')=='on') {
    // if create user option is no & create org option is yes
    woo_wzn_org($arr['org']);
  }
}

/** 
* Hook after the WooCommerce checkout (thank you page)
* @since 0.0.5
*/
function woo_wzn_checkout_finised($order_id) {
global $woocommerce;
$order = new WC_Order( $order_id );
  
  // Used fields for Zendesk
  $arr['name']=$order->billing_first_name.' '.$order->billing_last_name;
  $arr['org']=$order->billing_company;
  $arr['mail']=$order->billing_email;
  $arr['phone']=$order->billing_phone;
  /* other fields not used now (for later reference)
  [billing_country], [billing_address_1, [billing_address_2], [billing_postcode], [billing_city], [billing_state]
  /**/
  $address = woo_wzn_address($order->get_formatted_billing_address());  
  if(get_option( 'woo_wzn_address' )=='udetails') {
	$arr['details']=$address;
  } elseif(get_option( 'woo_wzn_address' )=='unotes') {
    $arr['notes']=$address;
  }
  
  // Create Zendesk user from post data
  if(get_option('woo_wzn_create_user')=='on') {
    // if create user option is yes
    woo_wzn_user($arr);
  } elseif(get_option('woo_wzn_create_org')=='on') {
    // if create user option is no & create org option is yes
    woo_wzn_org($arr['org']);
  }
}

/**
* Choose where the Zendesk plugin hooks into WooCommerce
* @since 0.0.5
*/
if(get_option('woo_wzn_con_type')=='checkout') {
  // Hook on checkout
  add_action('woocommerce_checkout_process', 'woo_wzn_checkout_process');
} elseif(get_option('woo_wzn_con_type')=='thankyou') {
  // Hook on thank you page
  add_action('woocommerce_thankyou', 'woo_wzn_checkout_finised');
}

/**
* Get formatted country name from WooCommerce
* @since 0.0.5
*/
function woo_wzn_country($country) {
  global $woocommerce; 
  $countries=$woocommerce->countries->countries;
  return $countries[$country];
}

function woo_wzn_address($address) {
  $address=htmlspecialchars(preg_replace('/<br(\s+)?\/?>/i', "", $address), ENT_QUOTES); 
  return preg_replace("/[\n\r]/","\n",$address);
}

/** 
* Add Zenddesk Feedback Tab
* @since 0.0.5
*/
function woo_wzn_fbt() {
  if(get_option( 'woo_wzn_fbt_pos' )=='all') {
	$true=1;
  } elseif(get_option( 'woo_wzn_fbt_pos' )=='store' && (is_woocommerce() || is_cart() || is_checkout() || is_account_page()))  {
	$true=1;
  } else { return false;}
  if($true==1){
    echo stripslashes(get_option('woo_wzn_fbt'));
  }
}
add_action('wp_footer','woo_wzn_fbt');
/**
* Basic WooCommerce checks
* @since 0.0.5
*/
function woo_wzn_get_page_id( $page ) {
		$page = apply_filters('woocommerce_get_' . $page . '_page_id', get_option('woocommerce_' . $page . '_page_id'));
		return ( $page ) ? $page : -1;
}
function woo_wzn_is_shop() {
		return ( is_post_type_archive( 'product' ) || is_page( woo_wzn_get_page_id( 'shop' ) ) ) ? true : false;
}
function woo_wzn_is_product() {
		return is_singular( array( 'product' ) );
}
/**
* Add support link to order email
* @since 0.0.5
*/
function woo_wzn_email() {
	if(get_option('woo_wzn_email_title')!="") { echo '<h2>'.__(get_option('woo_wzn_email_title'),'woo-wzn').'</h2>'; }
	echo '<p><a href="http://'.get_option('woo_wzn_subdomain').'.zendesk.com" target=_blank title="'.__(get_option('woo_wzn_email_title'),'woo-wzn').'">'.__(get_option('woo_wzn_email_text'),'woo-wzn').'</a></p>';
}
//
if(get_option( 'woo_wzn_email_enable' )!='off') {
  add_action('woocommerce_email_'.get_option( 'woo_wzn_email_pos' ).'_order_table', 'woo_wzn_email');
}


/**
// add_action( 'woocommerce_after_my_account','ticket_function' );   --> op my account pagina helemaal onderaan (na adressen)
// woocommerce_before_my_account --> op my account pagina tussen de introtekst en "recent orders"
// woocommerce_order_details_after_order_table --> op order detail pagina na order details table
// woocommerce_my_account_my_orders_actions  --> gebruiken voor UMF om de upload links toe te voegen op de juiste manier.
// woocommerce_after_single_product / woocommerce_before_single_product ---> product single page helemaal aan het eind
// woocommerce_before_main_content / woocommerce_after_main_content --> helemaal bovenaan nog zelfs voor breadcrumbs
// woocommerce_after_single_product_summary --> na korte product omschrijving.
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 ); --> voor product titel
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 35 ); -->  na add to cart
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10 ); --> na product titel, voor prijs
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 15 ); --> na prijs, voor add to cart
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 45 ); --> na categorie
add_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );   --> na tabs, voor related products
*/


/**
* Display Custom Tab on Frontend of Website for WooCommerce 2.0
* @since 0.0.5
* code from: http://pastebin.com/1Zzyq9Cq
*/
add_filter( 'woocommerce_product_tabs', 'woo_wzn_product_tab' );
function woo_wzn_product_tab( $tabs ) {
	global $post, $product;
	if ( get_option('woo_wzn_tab_title') != '' ){
		$tabs['woo-wzn'] = array(
			'title'    => get_option('woo_wzn_tab_title'),
			'priority' => 999,
			'callback' => 'woo_wzn_product_tab_cnt',					
			//'content'  => $custom_tab_options['content']
		);
	}
	return $tabs;
}

/**
* The content for the new tab
* @since 0.0.5
*/
function woo_wzn_product_tab_cnt() {
	wzn_ticket_form();
}
/**
* Ticket form on order detail page
* @since 0.0.5
*/
function woo_wzn_orderdetail() {
	echo '<h2>'.get_option('woo_wzn_orderdetail_title').'</h2>';
	wzn_ticket_form();
}
if(get_option( 'woo_wzn_orderdetail_pos' )=='before' && get_option('woo_wzn_orderdetail_title')!='') {
  add_action('woocommerce_view_order','woo_wzn_orderdetail');
} elseif(get_option( 'woo_wzn_orderdetail_pos' )=='after' && get_option('woo_wzn_orderdetail_title')!='') {
  add_action('woocommerce_order_details_after_order_table','woo_wzn_orderdetail');
}

/**
* Ticket form on My Account page
* @since 0.0.5
*/
function woo_wzn_myaccount() {
	$style='';
	if(get_option( 'woo_wzn_myaccount_pos' )=='after') { $style='style="margin:30px 0 0 0;"';}
	echo '<h2 '.$style.'>'.get_option('woo_wzn_myaccount_title').'</h2>';
	wzn_ticket_form();
}
if(get_option( 'woo_wzn_myaccount_pos' )=='before' && get_option('woo_wzn_myaccount_title')!='') {
  add_action('woocommerce_before_my_account','woo_wzn_myaccount');
} elseif(get_option( 'woo_wzn_myaccount_pos' )=='after' && get_option('woo_wzn_myaccount_title')!='') {
  add_action('woocommerce_after_my_account','woo_wzn_myaccount');
}

/**
* Clean ticket forms
* @since 0.0.5
*/
function wzn_clean($value){
	$value = @trim($value);
	if(get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}
	return $value;
}

/**
* Imports javascript to send tickets
* @since 0.0.5
*/
function woo_wzn_scripts() {
  if(is_woocommerce() || is_cart() || is_checkout() || is_account_page()) { ?>
  <script type="text/javascript">
  jQuery(document).ready(function() { 
	jQuery('.woo-wzn-success').hide();
	jQuery("#wzn_ticket").submit(function(e) { 
	  var str = jQuery(this).serialize();
	  e.preventDefault();
	  var parent=jQuery(this).closest('#send_tickets');
	jQuery.ajax({
		type: "post",url: "<?php echo admin_url('admin-ajax.php');?>",data: str,
		success: function(html){ //so, if data is retrieved, store it in html
			jQuery(parent).hide(500).queue(function(n) {
				jQuery(parent).html(html);
				jQuery(parent).find('.woo-wzn-success').delay(1500).hide('slow');
				n();
			}); 
			jQuery(parent).show(500);
			
		} 
	}); //close jQuery.ajax(
	
	});
});</script>
<?php  }
}

/**
* Process send ticket form
* @since 0.0.5
*/
function wzn_process_send_ticket() {
	check_ajax_referer( "wzn_nonce",'_wpnonce' );
	if(isset($_POST['action']) && $_POST['action']=='send_ticket' && is_email($_POST['wzn_user_mail'])) {
		echo '<p class="woocommerce-message success woo-wzn-error">' . __( 'Thank you for contacting us. Your message is successfully sent.', 'woo-wzn') . '</p>';
		$args=array('name'=>wzn_clean(sanitize_text_field($_POST['wzn_user_name'])),'email'=>wzn_clean($_POST['wzn_user_mail']),'subject'=>wzn_clean(sanitize_text_field($_POST['wzn_subject'])),'body'=>wzn_clean(esc_attr($_POST['wzn_comment'])),'user_id'=>get_current_user_id());
		woo_wzn_ticket($args);
	} else {
		echo '<p class="woocommerce-error woo-wzn-error error">' . __( 'Something went wrong, please try again.', 'woo-wzn') . '</p>';
	}
	die();
}
add_action('wp_ajax_send_ticket', 'wzn_process_send_ticket' );
add_action('wp_ajax_nopriv_send_ticket', 'wzn_process_send_ticket');

/** 
* Ticket form
* @since 0.0.5
*/
function wzn_ticket_form() {
	if(isset($_GET['act']) && $_GET['act']=='s') { _e( 'Succesfully sent', 'woo-wzn'); }
	echo '<div id=send_tickets class=col2-set>';
	echo '<p class=woo-wzn-success></p>';
	echo '<div class=col-1>';
	echo '<form name=wzn_ticket id=wzn_ticket method=post>';
	wp_nonce_field('wzn_nonce');
	echo '<input type=hidden name=wzn_user_id value="'.get_current_user_id().'">';
	echo '<input type=hidden name=action value="send_ticket">';
	if(is_user_logged_in()) {
		$user=get_userdata(get_current_user_id());
		echo '<input id=user_name name="wzn_user_name" type=hidden value="'.$user->user_firstname.' '.$user->user_lastname.'">';
		echo '<input id=user_mail name="wzn_user_mail" type=hidden value="'.$user->user_email.'" >';
		echo '<p class="form-row wzn-user-name wzn-input-req"><label for=user_name>'.__( 'Name:', 'woo-wzn') .'</label> '.$user->user_firstname.' '.$user->user_lastname.'</p>';
		echo '<p class="form-row wzn-user-mail wzn-input-req"><label for=user_mail>'.__( 'Email:', 'woo-wzn') .'</label> '.$user->user_email.'</p>';
	} else {
		echo '<p class="form-row form-row-wide wzn-user-name wzn-input-req"><label for=user_name>'.__( 'Name:', 'woo-wzn') .'</label> <input type=text class=input-text id=user_name name="wzn_user_name" required aria-required="true"></p>';
		echo '<p class="form-row form-row-wide wzn-user-mail wzn-input-req"><label for=user_mail>'.__( 'Email:', 'woo-wzn') .'</label> <input type=email class=input-text id=user_mail name="wzn_user_mail" required aria-required="true"></p>';
	}
	echo '<p class="form-row form-row-wide wzn-subject wzn-input-req"><label for=wzn_subject>'.__( 'Subject:', 'woo-wzn') .'</label> <input type=text class=input-text id=wzn_subject name="wzn_subject" required aria-required="true"></p>';
	echo '<p class="form-row form-row-wide wzn-comment wzn-input-req"><label for=wzn_comment>'.__( 'Message:', 'woo-wzn') .'</label> <textarea id=wzn_comment name="wzn_comment" required aria-required="true" rows="4" cols="55" style="width:100%;"></textarea>';
	echo '<p class="form-row form-row-wide wzn-submit"><input class=button type=submit name=wzn_submit value="'.__( 'Send', 'woo-wzn') .'"></p>';
	echo '</div>';
	echo '</form>';
	echo '</div>';
}
?>