jQuery(document).ready(function($) {		
	$('.activate_vendor').click(function (e) {
		 e.preventDefault();
		 var data = {
				action : 'activate_pending_vendor',
				user_id : $(this).attr('data-id')
		 }	
		 $.post(ajaxurl, data, function(responsee) {
		 		 window.location= window.location ;
		 });
	});
	
	$('.reject_vendor').click(function (e) {
		 e.preventDefault();
		 var data = {
				action : 'reject_pending_vendor',
				user_id : $(this).attr('data-id')
		 }	
		 $.post(ajaxurl, data, function(responsee) {
		 		 window.location= window.location ;
		 });
	});
	
});