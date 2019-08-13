(function($) {
	
});

function myparcel_popup_loading(message){
	message = message || 'Loading...';
	
	return swal({
		title: '',
		text: message,
		showCancelButton: false,
		showConfirmButton: false,
		html: false
	});;
}

function myparcel_popup_close(){
	return swal.close();
}

function myparcel_popup_error(message){
	return swal('', message, 'error');
}

function myparcel_popup_success(message){
	return swal('', message, 'success');
}