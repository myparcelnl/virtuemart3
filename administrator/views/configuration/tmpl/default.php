<style>
#MyParcelContent .control-group {
	padding: 0 1em;
}
#MyParcelContent .control-group > label.control-label {
	font-weight: bold;
	width: 200px;
}
#MyParcelContent .control-group .controls {
	display: inline-block;
	margin-left: 0;
	width: calc(100% - 205px);
}
#MyParcelContent .control-group .controls .control-label {
	max-width: 70%;
	width: auto;
}
#MyParcelContent .control-group .controls input[type="radio"],
#MyParcelContent .control-group .controls input[type="checkbox"] {
	margin-right: .5em;
	margin-top: 0;
}
</style>
<div class="container-fluid container-main">
<?php if(!$this->isSSL){ ?>
	<div class="alert alert-warning">
		<button type="button" class="close hide" data-dismiss="alert">×</button>
		<h4 class="alert-heading"><?php echo JText::_('Warning'); ?></h4>
		<div class="alert-message"><?php echo JText::_('SSL certificate is required for MyParcel webhook action.'); ?></div>
	</div>
<?php } ?>

	<!-- nav -->
	<ul id="MyParcelTabs" class="nav nav-tabs">
		<li class="active"><a href="#general">General</a></li>
		<li><a href="#settings">Export Settings</a></li>
	</ul>
	<!-- end nav -->
	
	<form id="MyParcelContent" class="tab-content form-horizontal" method="POST" action="<?php echo JRoute::_('index.php?option=com_virtuemart_myparcelnl&view=configuration&task=saveConfig'); ?>">
		<!-- general -->
		<div id="general" class="tab-pane active">
			<legend><?php echo JText::_('API Settings'); ?></legend>
			<div class="control-group">
				<label for="api_key" class="control-label"><?php echo JText::_('API'); ?></label>
				<div class="controls">
					<input type="text" id="api_key" name="form[api_key]" class="span5 input_box" size="70" placeholder="<?php echo JText::_('API'); ?>" value="<?php echo @$this->configs['api_key']; ?>" />
				</div>
			</div>
			<legend><?php echo JText::_('General Settings'); ?></legend>
			<div class="control-group">
				<label for="package_type" class="control-label"><?php echo JText::_('Use addition address as number suffix'); ?></label>
				<div class="controls">
					<ul style="list-style: none;margin-left: 0;">
						<li>
							<label class="control-label">    
								<input type="radio" value="0" name="form[use_addition_address_as_number_suffix]" <?php echo (@$this->configs['use_addition_address_as_number_suffix'] == 0) ? 'checked' : ''; ?> /> 
							<?php echo JText::_('None'); ?>
							</label>
						</li>
						<li style="clear: both;">
							<label class="control-label">    
								<input type="radio" value="1" name="form[use_addition_address_as_number_suffix]" <?php echo (@$this->configs['use_addition_address_as_number_suffix'] == 1) ? 'checked' : ''; ?> /> 
							<?php echo JText::_('With this option enabled, value inputted in address 2 will be considered as number suffix in the customer address.'); ?>
							</label>
						</li>
						<li style="clear: both;">
							<label class="control-label">    
								<input type="radio" value="2" name="form[use_addition_address_as_number_suffix]" <?php echo (@$this->configs['use_addition_address_as_number_suffix'] == 2) ? 'checked' : ''; ?> /> 
							<?php echo JText::_('With this option enabled, value inputted in address 2 will be considered as house number and number suffix in the customer address.'); ?>
							</label>
						</li>
					</ul>
				</div>
			</div>
			
			<div class="control-group">
				<button type="submit" class="btn btn-success"><?php echo JText::_('Save'); ?></button>
			</div>
		</div>
		<!-- end general -->
		
		<!-- settings -->
		<div id="settings" class="tab-pane">
			<legend><?php echo JText::_('Export Settings'); ?></legend>
			<div class="control-group">
				<label for="connect_customer_email" class="control-label"><?php echo JText::_('Connect customer email'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<input type="hidden" name="form[connect_customer_email]" value="0" />
						<input type="checkbox" value="1" name="form[connect_customer_email]" <?php echo (@$this->configs['connect_customer_email'] == 1) ? 'checked' : ''; ?> /> 
					<?php echo JText::_('When you connect the customer email, MyParcel can send a Track&amp;Trace email to this address. In your <a href="https://backoffice.myparcel.nl/ttsettingstable">MyParcel backend</a> you can enable or disable this email and format it in your own style.'); ?>
					</label>
				</div>
			</div>
			
			<div class="control-group">
				<label for="connect_customer_phone" class="control-label"><?php echo JText::_('Connect customer phone'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<input type="hidden" name="form[connect_customer_phone]" value="0" />
						<input type="checkbox" value="1" name="form[connect_customer_phone]" <?php echo (@$this->configs['connect_customer_phone'] == 1) ? 'checked' : ''; ?> /> 
					<?php echo JText::_('When you connect the customer\'s phone number, the courier can use this for the delivery of the parcel. This greatly increases the delivery success rate for foreign shipments.'); ?>
					</label>
				</div>
			</div>
			
			<div class="control-group">
				<label for="package_type" class="control-label"><?php echo JText::_('Prefered package type'); ?></label>
				<div class="controls">
					<ul style="list-style: none;margin-left: 0;">
					<?php $this->configs['package_type'] = (@$this->configs['package_type'] == 0) ? 1 : $this->configs['package_type']; ?>
						<li>
							<label class="control-label">    
								<input type="radio" value="1" name="form[package_type]" <?php echo (@$this->configs['package_type'] == 1) ? 'checked' : ''; ?> /> 
							<?php echo JText::_('Parcel'); ?>
							</label>
						</li>
						<li style="clear: both;">
							<label class="control-label">    
								<input type="radio" value="2" name="form[package_type]" <?php echo (@$this->configs['package_type'] == 2) ? 'checked' : ''; ?> /> 
							<?php echo JText::_('Mailbox'); ?>
							</label>
						</li>
						<li style="clear: both;">
							<label class="control-label">    
								<input type="radio" value="3" name="form[package_type]" <?php echo (@$this->configs['package_type'] == 3) ? 'checked' : ''; ?> /> 
							<?php echo JText::_('Letter'); ?>
							</label>
						</li>
						<li style="clear: both;">
							<label class="control-label">    
								<input type="radio" value="4" name="form[package_type]" <?php echo (@$this->configs['package_type'] == 4) ? 'checked' : ''; ?> /> 
							<?php echo JText::_('Digital stamp'); ?>
							</label>
						</li>
					</ul>
				</div>
			</div>
			
			<!-- package type MyParcel only -->
			<div class="control-group control-group-package control-group-package-1" style="<?php echo (@$this->configs['package_type'] == 1) ? '' : 'display: none;'; ?>">
				<label for="extra_large_size" class="control-label"><?php echo JText::_('Extra large size'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<input type="hidden" name="form[extra_large_size]" value="0" />
						<input type="checkbox" value="1" name="form[extra_large_size]" <?php echo (@$this->configs['extra_large_size'] == 1) ? 'checked' : ''; ?> /> 
					<?php echo JText::_('Enable this option when your shipment is bigger than 100 x 70 x 50 cm, but smaller than 175 x 78 x 58 cm. An extra fee of € 2,45 will be charged.'); ?><br/>
						<small>
							<strong><?php echo JText::_('Note!'); ?></strong>
						<?php echo JText::_('If the parcel is bigger than 175 x 78 x 58 of or heavier than 30 kg, the pallet rate of € 70,00 will be charged.'); ?>
						</small>
					</label>
				</div>
			</div>
			
			<div class="control-group control-group-package control-group-package-1" style="<?php echo (@$this->configs['package_type'] == 1) ? '' : 'display: none;'; ?>">
				<label for="only_recipient" class="control-label"><?php echo JText::_('Home address only'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<input type="hidden" name="form[only_recipient]" value="0" />
						<input type="checkbox" value="1" name="form[only_recipient]" <?php echo (@$this->configs['only_recipient'] == 1) ? 'checked' : ''; ?> /> 
					<?php echo JText::_('If you don\'t want the parcel to be delivered at the neighbours, choose this option.'); ?>
					</label>
				</div>
			</div>
			
			<div class="control-group control-group-package control-group-package-1" style="<?php echo (@$this->configs['package_type'] == 1) ? '' : 'display: none;'; ?>">
				<label for="signature_delivery" class="control-label"><?php echo JText::_('Signature on delivery'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<input type="hidden" name="form[signature_delivery]" value="0" />
						<input type="checkbox" value="1" name="form[signature_delivery]" <?php echo (@$this->configs['signature_delivery'] == 1) ? 'checked' : ''; ?> /> 
					<?php echo JText::_('The parcel will be offered at the delivery address. If the recipient is not at home, the parcel will be delivered to the neighbours. In both cases, a signuture will be required.'); ?>
					</label>
				</div>
			</div>
			
			<div class="control-group control-group-package control-group-package-1" style="<?php echo (@$this->configs['package_type'] == 1) ? '' : 'display: none;'; ?>">
				<label for="return_no_answer" class="control-label"><?php echo JText::_('Return if no answer'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<input type="hidden" name="form[return_no_answer]" value="0" />
						<input type="checkbox" value="1" name="form[return_no_answer]" <?php echo (@$this->configs['return_no_answer'] == 1) ? 'checked' : ''; ?> /> 
					<?php echo JText::_('By default, a parcel will be offered twice. After two unsuccessful delivery attempts, the parcel will be available at the nearest pickup point for two weeks. There it can be picked up by the recipient with the note that was left by the courier. If you want to receive the parcel back directly and NOT forward it to the pickup point, enable this option.'); ?>
					</label>
				</div>
			</div>
			
			<div class="control-group control-group-package control-group-package-1" style="<?php echo (@$this->configs['package_type'] == 1) ? '' : 'display: none;'; ?>">
				<label for="insured" class="control-label"><?php echo JText::_('Insured shipment'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<input type="hidden" name="form[insured]" value="0" />
						<input type="checkbox" value="1" name="form[insured]" <?php echo (@$this->configs['insured'] == 1) ? 'checked' : ''; ?> /> 
					<?php echo JText::_('By default, there is no insurance on the shipments. If you still want to insure the shipment, you can do that from €0.50. We insure the purchase value of the shipment, with a maximum insured value of € 5.000. Insured parcels always contain the options "Home address only" en "Signature for delivery".'); ?>
						<p style="margin-left: 3em;margin-top: .5em;<?php echo (@$this->configs['insured'] == 1) ? '' : 'display: none;'; ?>">
							<strong style="display: inline-block;width: 120px;"><?php echo JText::_('Insured amount'); ?></strong>
							<select id="insured_amount" name="form[insured_amount]" class="form-control" style="margin-left: 1em;width: 250px;">
								<option value="99" <?php echo (@$this->configs['insured_amount'] == 99) ? 'selected' : ''; ?>><?php echo JText::_('Insured up to € 100'); ?></option>
								<option value="249" <?php echo (@$this->configs['insured_amount'] == 249) ? 'selected' : ''; ?>><?php echo JText::_('Insured up to € 250'); ?></option>
								<option value="499" <?php echo (@$this->configs['insured_amount'] == 499) ? 'selected' : ''; ?>><?php echo JText::_('Insured up to € 500'); ?></option>
								<option value="0" <?php echo (@$this->configs['insured_amount'] == 0) ? 'selected' : ''; ?>>&gt; <?php echo JText::_('€ 500 insured'); ?></option>
							</select>
						</p>
						<p style="margin-left: 3em;margin-top: .5em;<?php echo (@$this->configs['insured'] == 1 && @$this->configs['insured_amount'] == 0) ? '' : 'display: none;'; ?>">
							<strong style="display: inline-block;width: 120px;"><?php echo JText::_('Insured amount (€)'); ?></strong>
							<input type="text" id="insured_amount_value" name="form[insured_amount_value]" class="span5 input_box" size="70" placeholder="<?php echo JText::_('Insured amount'); ?>" value="<?php echo empty($this->configs['insured_amount_value']) ? '' : (int) @$this->configs['insured_amount_value']; ?>" style="margin-left: 1em;width: 250px;" />
						</p>
					</label>
				</div>
			</div>
			<!-- end package type MyParcel only -->
			
			<!-- package type Digital stamp only -->
			<div class="control-group control-group-package control-group-package-4" style="<?php echo (@$this->configs['package_type'] == 4) ? '' : 'display: none;'; ?>">
				<label for="insured" class="control-label"><?php echo JText::_('Prefered weight'); ?></label>
				<div class="controls">
					<label class="control-label">                                                
						<select id="insured_amount" name="form[default_weight]" class="form-control" style="margin-left: 1em;width: 250px;">
							<option value="0" <?php echo (@$this->configs['default_weight'] == 0) ? 'selected' : ''; ?>><?php echo JText::_('0 - 20'); ?></option>
							<option value="20" <?php echo (@$this->configs['default_weight'] == 20) ? 'selected' : ''; ?>><?php echo JText::_('20 - 50'); ?></option>
							<option value="50" <?php echo (@$this->configs['default_weight'] == 50) ? 'selected' : ''; ?>><?php echo JText::_('50 - 100'); ?></option>
							<option value="100" <?php echo (@$this->configs['default_weight'] == 100) ? 'selected' : ''; ?>>&gt; <?php echo JText::_('100 - 350'); ?></option>
							<option value="350" <?php echo (@$this->configs['default_weight'] == 350) ? 'selected' : ''; ?>>&gt; <?php echo JText::_('350 - 1000'); ?></option>
						</select>
					</label>
				</div>
			</div>
			<!-- end package type Digital stamp only -->
			
			<div class="control-group">
				<label for="label_description" class="control-label"><?php echo JText::_('Label description'); ?></label>
				<div class="controls">
					<input type="text" id="label_description" name="form[label_description]" class="span5 input_box" size="70" placeholder="<?php echo JText::_('Label description'); ?>" value="<?php echo @$this->configs['label_description']; ?>" />				
					<small style="clear: both;display: inline-block;max-width: 70%;">
					<?php echo JText::_('With this option, you can add a description to the shipment. This will be printed on the top left of the label, and you can use this to search or sort shipments in the MyParcel Backend. Use <strong>[ORDER_NR]</strong> to include the order number.'); ?>
					</small> 
				</div>
			</div>

            <!-- config paper format when print shipment label -->
            <div class="control-group">
                <label for="paper_format" class="control-label"><?php echo JText::_('Paper format'); ?></label>
                <div class="controls">
                    <select class="form-control"  name="form[paper_format]">
                        <?php foreach ($this->paperFormat as $key => $value) { ?>
                            <option <?php echo (@$this->configs['paper_format'] == $key) ? 'selected' : ''; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <!-- end config paper format when print shipment label -->
			<div class="control-group">
				<button type="submit" class="btn btn-success"><?php echo JText::_('Save'); ?></button>
			</div>
		</div>
		<!-- end settings -->
	</form>
</div>