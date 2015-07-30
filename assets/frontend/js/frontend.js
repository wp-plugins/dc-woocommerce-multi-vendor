jQuery(document).ready(function($) {
	$('#report_abuse').click(function(e){
		e.preventDefault();
		$('#report_abuse_form').simplePopup();
	});
	
	$('.submit-report-abuse').on('click' , function(e) {
		 e.preventDefault();
		 var data = {
				action : 'send_report_abuse',
				product_id : $('.report_abuse_product_id').val(),
				name : $('.report_abuse_name').val(),
				email : $('.report_abuse_email').val(),
				msg : $('.report_abuse_msg').val(),
		 }	
		 $.post(woocommerce_params.ajax_url, data, function(responsee) {
		 	$('.simplePopupClose').click();
		 });		
	});
});