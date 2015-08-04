jQuery(document).ready(function() { 
	jQuery("#wzn_ticket").submit(function(e) { 
	  alert('test');
	  var str = jQuery(this).serialize();
	  var nonce= jQuery(this).find('#_wpnonce').val();
	  alert(nonce);
	  e.preventDefault();
	  var wzn_data=jQuery(this).data('delete');
	  var wzn_order_id=jQuery(this).data('order_id');
	  var wzn_product_id=jQuery(this).data('product_id');
	  var parent=jQuery(this).closest('.woo-wzn-item-box');
	  var wzn_box_name=jQuery(parent).find('.woo-wzn-upload-title').html();
	jQuery.ajax({
		type: "post",url: "<?php echo admin_url('admin-ajax.php');?>",data: { action: 'send_ticket', _ajax_nonce: nonce, str:str},
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
});


//jQuery(document).ready(function() { 
//	jQuery("#contact_me").submit(function() { 
		//var str = jQuery(this).serialize(); 
		//jQuery.ajax({  type: "POST", url: "/wp-admin/admin-ajax.php", data: 'action=contact_form&'+str, success: function(msg) { jQuery(".contact .node").ajaxComplete(function(event, request, settings){ if(msg == 'sent') { jQuery(".contact .node").hide(); jQuery(".contact form").fadeOut('slow');jQuery(".contact .success").delay(500).fadeIn("slow"); }else {result = msg; jQuery(".contact .node").html(result); jQuery(".contact .node").fadeIn("slow"); } }); } }); return false; });});