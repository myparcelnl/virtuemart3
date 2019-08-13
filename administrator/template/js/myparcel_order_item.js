(function($) {
    $(document).ready(function() {
		initMyparcelData();
		
		$('#adminForm table.adminlist').on('click', '.btn-myparcel-consignment-new', function(){
			var container = $(this);
			var order_id = parseInt(container.data('id')) || 0;
			
			return exportMyparcelOrder(order_id);
		});
		
		$('#adminForm table.adminlist').on('click', '.btn-myparcel-pdf', function(){
			var container = $(this);
			var order_id = parseInt(container.data('id')) || 0;
			
			return printMyparcelOrder(order_id);			
		});
		
		function initMyparcelData(){
			var order_id = $('input[name=virtuemart_order_id]').val() || 0;
			
			myparcel_popup_loading('Loading Myparcel Data...');
			
			$.ajax({
				url : myparcel_base +'index.php?option=com_virtuemart_myparcelnl&view=order&task=checkVirtueMartOrder',
				type : 'POST',
				dataType: 'json',
				data : {
					order_ids : [order_id],
				},
                success: function(result){
					if(result.status){
						var data = result.data || [];
						var consignment_id = 0;
						var html_myparcel_actions = '<a href="javascript:;" class="btn btn-small btn-myparcel-consignment-new" data-id="'+order_id+'" style="margin-left: .5em;" title="Export to Myparcel"><img src="'+myparcel_base+'components/com_virtuemart_myparcelnl/template/images/myparcel_pdf_add.png" /></a>';
						
						if(data.length > 0){
							var consignment = data[0];
							consignment_id = consignment.consignment_id;
							
							var html_consignment = '<a href="javascript:;" class="btn btn-small btn-myparcel-pdf" data-id="'+consignment.order_id+'" data-consignment_id="'+consignment_id+'" style="margin-left: .5em;" title="Print Myparcel label"><img src="'+myparcel_base+'components/com_virtuemart_myparcelnl/template/images/myparcel_pdf.png" /></a>';
							html_myparcel_actions = html_consignment + html_myparcel_actions;
						}
						
						html_myparcel_actions = '<span class="myparcel-actions" style="margin-left: 1em;"><strong>MyParcel Actions: </strong>'+html_myparcel_actions+'</span>';
						
						if($('#adminForm table').length){
							var table = $('#adminForm table').eq(0);
							var td = table.find('tbody').eq(0).find('tr').eq(0).find('td').eq(0);
							
							if(td.find('.myparcel-actions').length){
								td.find('.myparcel-actions').remove();
							}
							
							if(td.find('span.btn-small').length){
								$(html_myparcel_actions).insertAfter(td.find('span.btn-small').last());
							}
						}
						
						if(consignment_id > 0) return initMyparcelConsignment(consignment_id);
						else return myparcel_popup_close();
					}else{
						return myparcel_popup_close();
					}
                }
			});
		}
		function initMyparcelConsignment(consignment_id){
			$.ajax({
				url : myparcel_base +'index.php?option=com_virtuemart_myparcelnl&view=order&task=checkConsignmentStatus',
				type : 'POST',
				dataType: 'json',
				data : {
					consignment_id : consignment_id,
				},
                success: function(result){
					if(result.status){
						var tracktrace = result.tracktrace;
						
						if($('#orderForm').length){
							var table_payment = $('#orderForm').find('table').eq(0);
							
							//
							var html_table_shipping = '<td width="50%" valign="top"><table class="adminlist ui-sortable" cellspacing="0" cellpadding="0">';
							html_table_shipping += '<thead>';
							html_table_shipping += '<tr>';
							html_table_shipping += '<th class="key" colspan="2"><strong><img src="'+myparcel_base+'components/com_virtuemart_myparcelnl/template/images/logo-myparcel-alt.svg" style="height: 20px;" /> MyParcel Tracking</strong></th>';
							html_table_shipping += '</tr>';
							html_table_shipping += '</thead>';
							html_table_shipping += '<tbody>';
							html_table_shipping += '<tr>';
							html_table_shipping += '<td class="key"><strong>Code</strong></td>';
							html_table_shipping += '<td>'+tracktrace.code+'</td>';
							html_table_shipping += '</tr>';
							html_table_shipping += '<tr>';
							html_table_shipping += '<td class="key"><strong>Description</strong></td>';
							html_table_shipping += '<td>'+tracktrace.description+'</td>';
							html_table_shipping += '</tr>';
							html_table_shipping += '<tr>';
							html_table_shipping += '<tr>';
							html_table_shipping += '<td class="key"><strong>Updated at</strong></td>';
							html_table_shipping += '<td><a href="'+tracktrace.link_tracktrace+'" target="_blank" title="Click to view tracking status">Tracking URL</a><br/>'+tracktrace.time+'</td>';
							html_table_shipping += '</tr>';
							html_table_shipping += '</tbody>';
							html_table_shipping += '</table></td>';
							
							table_payment.find('tr').eq(0).append(html_table_shipping);
						}
						
						return myparcel_popup_close();
					}else{
						return myparcel_popup_close();
					}
                }
			});
		}
		function exportMyparcelOrder(data_order){
			myparcel_popup_loading();
			
			$.ajax({
				url : myparcel_base +'index.php?option=com_virtuemart_myparcelnl&view=order&task=exportVirtueMartOrder',
				type : 'POST',
				dataType: 'json',
				data : {
					order_id : data_order,
				},
                success: function(result){
					if(result.status){
						initMyparcelData();
						
						return myparcel_popup_success('Export Success');
					}
					else{
						var message = result.message;
						
						if(message) return myparcel_popup_error(message);
						else return myparcel_popup_error('Export Error');
					}
                }
			});
		}
		function printMyparcelOrder(order_id){
			myparcel_popup_loading('Loading Myparcel Data...');
			
			$.ajax({
                url : myparcel_base +'index.php?option=com_virtuemart_myparcelnl&view=order&task=printVirtueMartOrder',
                type : 'POST',
				dataType: 'json',
                data : {
                    action: 'export',
                    order_id : order_id,
                },
                success: function(result){
                    if(result.status){
                        myWindow = window.open(result.data.path_file, '', 'width=1000 ,height=600');
						
						if(!myWindow){
							return myparcel_popup_error('Popup browser is blocked');
						}
						
						return myparcel_popup_close();
                    }else{
                        var message = result.message;
						
						if(message) return myparcel_popup_error(message);
						else return myparcel_popup_error('Can\'t Print Order');
                    }
                }
            });
		}
    });
})(jQuery);