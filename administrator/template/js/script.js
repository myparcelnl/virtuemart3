(function($) {
    $(document).ready(function() {
        $('#MyParcelTabs').on('click', 'li > a', function() {
            var container = $(this);

            if ($(container.attr('href')).length) {
                container.closest('li').addClass('active').siblings().removeClass('active');
                $(container.attr('href')).addClass('active').siblings().removeClass('active');
            }
			
			return false;
        });
		
        $('[name="form[package_type]"]').on('click', function() {
			var container = $(this);
			
			$('.control-group-package').hide();
			
			if (container.val() == 1){ // Parcel
				return $('.control-group-package-1').show();
			}else
			if (container.val() == 4){ // Digital stamp
				return $('.control-group-package-4').show();
			}
		});
		
        $('[name="form[insured]"]').on('click', function() {
			var container = $(this);
			
			if (container.is(':checked')){ 
				$('#insured_amount').val('99');
			
				return $('#insured_amount').closest('p').show();
			}else{
				$('#insured_amount_value').closest('p').hide();
				
				return $('#insured_amount').closest('p').hide();
			}
		});
		
		$('#insured_amount').on('change', function() {
			var container = $(this);
			
			if(container.val() == 0){
				return $('#insured_amount_value').closest('p').show();
			}else{
				$('#insured_amount_value').val('');
				
				return $('#insured_amount_value').closest('p').hide();
			}
		});
    });
})(jQuery);