(function($) {
    $(document).ready(function() {
        initTable();
		initBulkAction();
		initMyparcelData();
		
		$('#adminForm table.adminlist').on('click', '.btn-myparcel-consignment-new', function(){
			var container = $(this);
			var order_id = parseInt(container.data('id')) || 0;
			
			return exportMyparcelOrder(order_id);
		});
		
		$('#adminForm table.adminlist').on('click', '.btn-myparcel-consignment-retour', function(){
			var container = $(this);
			var order_id = parseInt(container.data('id')) || 0;
			
			return false;
		});
		
		$('#adminForm table.adminlist').on('click', '.btn-myparcel-pdf', function(){
			var container = $(this);
			var order_id = parseInt(container.data('id')) || 0;
			
			return printMyparcelOrder(order_id);			
		});
		
		$('#filterbox').on('change', '.myparcel-bulk-action', function(){
			var container = $(this);
			var order_ids = [];
			
			if($('#adminForm table.adminlist').length){
				var table = $('#adminForm table.adminlist').eq(0);
				
				if(table.find('tbody').length){
					table.find('tbody').find('tr').each(function(){
						var tr = $(this);
						var order_input = tr.find('input[type=checkbox]').eq(0);
						
						if(order_input.is(':checked')) order_ids.push(order_input.val());
					});
				}
			}
			
			if(order_ids.length == 0) return myparcel_popup_error('You must choose at least 1 order.');
			
			var select_action = container.val();
			
			// reset data
			$('#adminForm table.adminlist tbody input[type=checkbox]').prop('checked', false); 
			$('#filterbox .myparcel-bulk-action option[value=""]').prop('selected', true);
			
			if(select_action == 'export'){
				return exportMyparcelOrder(order_ids);
			}else
			if(select_action == 'print'){
				return printMyparcelOrder(order_ids);
			}
			
			return false;
		});
		
		// functions
		function initBulkAction(){
			if($('#filterbox table').length){
				var html_td_action = '';
				var table = $('#filterbox table').eq(0);
				
				// tbody
				if(table.find('tbody').length){
					var table_action = table.find('tbody');
					var tr_action = table_action.find('tr').eq(0);
					var td_actions = tr_action.find('td');
					
					
					html_td_action += '<tr>';
					html_td_action += '<td colspan="'+td_actions.length+'">';
					html_td_action += '<div class="text-left" style="display: inline-block;padding: 0.5em 1em;border: 1px solid;">';
					html_td_action += '<strong>MyParcel Actions:</strong>';
					html_td_action += '<select class="form-control myparcel-bulk-action">';
					html_td_action += '<option value="">Choose action</option>';
					html_td_action += '<option value="export">Export shipment</option>';
					html_td_action += '<option value="print">Print shipment</option>';
					html_td_action += '</select>';
					html_td_action += '</div>';
					html_td_action += '</td>';
					html_td_action += '</tr>';
					
					if(table_action.find('.myparcel-bulk-action').length == 0){
						table_action.append(html_td_action);
					}
				}
			}
		}
		function initTable(){
			if($('#adminForm table.adminlist').length){
				var table = $('#adminForm table.adminlist').eq(0);
				
				// thead
				if(table.find('thead').length){
					var tr = table.find('thead').find('tr').eq(0);
					
					if(tr.find('th.myparcel-label').length == 0){
						var html_th_label = '<th class="myparcel-label">MyParcel Labels</th>';
						tr.append(html_th_label);
					}
					
					if(tr.find('th.myparcel-actions').length == 0){
						var html_th_actions = '<th class="myparcel-actions">MyParcel Actions</th>';
						tr.append(html_th_actions);
					}
				}
				
				// tbody
				if(table.find('tbody').length){
					table.find('tbody').find('tr').each(function(){
						var tr = $(this);
						var order_input = tr.find('input[type=checkbox]').eq(0);
						var order_id = order_input.val() || 0;
					
						if(tr.find('td.myparcel-label').length == 0){
							var html_td_label = '<td class="myparcel-label myparcel-label-'+order_id+'" data-id="'+order_id+'"></td>';
							tr.append(html_td_label);
						}
						
						if(tr.find('td.myparcel-actions').length == 0){
							var html_td_actions = '<td class="myparcel-actions">';
							html_td_actions += '<a href="javascript:;" class="btn-myparcel-consignment-new" data-id="'+order_id+'" title="Export to Myparcel"><img src="'+myparcel_base+'components/com_virtuemart_myparcelnl/template/images/myparcel_pdf_add.png" /></a>';
							html_td_actions += '<a href="javascript:;" class="btn-myparcel-consignment-retour hide" data-id="'+order_id+'" style="margin-left: 0.5em;"><img src="'+myparcel_base+'components/com_virtuemart_myparcelnl/template/images/myparcel_retour_add.png" /></a>';
							html_td_actions += '</td>';
							tr.append(html_td_actions);
						}
					});
				}
			}
		}
		function initMyparcelData(close_popup){
			var order_ids = [];
			var close_popup = close_popup || false;
			
			if($('#adminForm table.adminlist').length){
				var table = $('#adminForm table.adminlist').eq(0);
				
				if(table.find('tbody').length){
					table.find('tbody').find('tr').each(function(){
						var tr = $(this);
						var order_input = tr.find('input[type=checkbox]').eq(0);
						
						order_ids.push(order_input.val());
					});
				}
			}
			
			if(order_ids.length == 0) return false;
			
			myparcel_popup_loading('Loading Myparcel Data...');
			
			$.ajax({
				url : myparcel_base +'index.php?option=com_virtuemart_myparcelnl&view=order&task=checkVirtueMartOrder',
				type : 'POST',
				dataType: 'json',
				data : {
					order_ids : order_ids,
				},
                success: function(result){
					if(result.status){
						var data = result.data || [];
						
						if(data.length > 0){
							for(var i = 0; i < data.length; i++){
								var consignment = data[i];
								
								if($('.myparcel-label-'+consignment.order_id).length){
									var html_consignment = '<a href="javascript:;" class="btn-myparcel-pdf" data-id="'+consignment.order_id+'" data-consignment_id="'+consignment.consignment_id+'" title="Print Myparcel label"><img src="'+myparcel_base+'components/com_virtuemart_myparcelnl/template/images/myparcel_pdf.png" /></a>';
									$('.myparcel-label-'+consignment.order_id).html(html_consignment);
								}
							}
						}
						
						if(close_popup) return;
						else return myparcel_popup_close();
					}else{
						var message = result.message;
						
						if(message) return myparcel_popup_error(message);
						else return myparcel_popup_error('Init Error');
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
						initMyparcelData(true);
						
						return myparcel_popup_success('Export Success');
					}else{
						var message = result.message;
						
						if(message) return myparcel_popup_error(message);
						else return myparcel_popup_error('Export Error');
					}
                }
			});
		}
		function printMyparcelOrder(data_order){
			myparcel_popup_loading('Loading Myparcel Data...');
			
			$.ajax({
                url : myparcel_base +'index.php?option=com_virtuemart_myparcelnl&view=order&task=printVirtueMartOrder',
                type : 'POST',
				dataType: 'json',
                data : {
                    action: 'export',
                    order_id : data_order,
                },
                success: function(result){
                    if(result.status){
                        myWindow = window.open(result.data.path_file, '', 'width=1000 ,height=600');
						
						if(!myWindow){
							return myparcel_popup_error('Popup browser is blocked');
						}
						
						return myparcel_popup_close();
                    }
                    else{
						var message = result.message;
						
						if(message) return myparcel_popup_error(message);
						else return myparcel_popup_error('Can\'t Print Order');
                    }
                }
            });
		}
    });
})(jQuery);