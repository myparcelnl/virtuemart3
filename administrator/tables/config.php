<?php

/**
 * @version     1.0.0
 * @package     com_myparcel
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Balticode <giedrius@balticode.com> - www.balticode.com
 */
// No direct access
defined('_JEXEC') or die;
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

/**
 * config Table class
 */
class MyparcelTableconfig extends JTable
{

    /**
     * Constructor
     *
     * @param JDatabase A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__myparcel_config', 'id', $db);
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param	array		Named array
     * @return	null|string	null is operation was satisfactory, otherwise returns an error
     * @see		JTable:bind
     * @since	1.5
     */
    public function bind($array, $ignore = '')
    {


        $input = JFactory::getApplication()->input;
        $task = $input->getString('task', '');
        if (($task == 'save' || $task == 'apply') && (!JFactory::getUser()->authorise('core.edit.state', 'com_myparcel') && $array['state'] == 1)) {
            $array['state'] = 0;
        }
		
		if($task == 'publish'){ 
			return $this->publish($array, 1);
		}else
		if($task == 'unpublish'){ 
			return $this->publish($array, 0);
		}

        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['params']);
            $array['params'] = (string) $registry;
        }

        if (isset($array['metadata']) && is_array($array['metadata'])) {
            $registry = new JRegistry();
            $registry->loadArray($array['metadata']);
            $array['metadata'] = (string) $registry;
        }
        if (!JFactory::getUser()->authorise('core.admin', 'com_myparcel.config.' . $array['id'])) {
            $actions = JFactory::getACL()->getActions('com_myparcel', 'config');
            $default_actions = JFactory::getACL()->getAssetRules('com_myparcel.config.' . $array['id'])->getData();
            $array_jaccess = array();
            foreach ($actions as $action) {
                $array_jaccess[$action->name] = $default_actions[$action->name];
            }
            $array['rules'] = $this->JAccessRulestoArray($array_jaccess);
        }
        //Bind the rules for ACL where supported.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $this->setRules($array['rules']);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * This function convert an array of JAccessRule objects into an rules array.
     * @param type $jaccessrules an arrao of JAccessRule objects.
     */
    private function JAccessRulestoArray($jaccessrules)
    {
        $rules = array();
        foreach ($jaccessrules as $action => $jaccess) {
            $actions = array();
            foreach ($jaccess->getData() as $group => $allow) {
                $actions[$group] = ((bool) $allow);
            }
            $rules[$action] = $actions;
        }
        return $rules;
    }

    /**
     * Overloaded check function
     */
    public function check()
    {

        //If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->id == 0) {
            $this->ordering = self::getNextOrder();
        }

        return parent::check();
    }

    /**
     * Method to set the publishing state for a row or list of rows in the database
     * table.  The method respects checked out rows by other users and will attempt
     * to checkin rows that it can after adjustments are made.
     *
     * @param    mixed    An optional array of primary key values to update.  If not
     *                    set the instance property value is used.
     * @param    integer The publishing state. eg. [0 = unpublished, 1 = published]
     * @param    integer The user id of the user performing the operation.
     * @return    boolean    True on success.
     * @since    1.0.4
     */
    public function publish($pks = null, $state = 1, $userId = 0)
    {
        // Initialise variables.
        $k = $this->_tbl_key;

        // Sanitize input.
        JArrayHelper::toInteger($pks);
        $userId = (int) $userId;
        $state = (int) $state;
        // If there are no primary keys set check to see if the instance key is set.
        if (empty($pks)) {
            if ($this->$k) {
                $pks = array($this->$k);
            }
            // Nothing to set publishing state on, return false.
            else {
                $this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
                return false;
            }
        }

        // Build the WHERE clause for the primary keys.
        $where = $k . '=' . implode(' OR ' . $k . '=', $pks);

        // Determine if there is checkin support for the table.
        if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
            $checkin = '';
        } else {
            $checkin = ' AND (checked_out = 0 OR checked_out = ' . (int) $userId . ')';
        }

        // Update the publishing state for rows with the given primary keys.
		$sql = 'UPDATE `' . $this->_tbl . '`' .
                ' SET `state` = ' . (int) $state .
                ' WHERE (' . $where . ')' .
                $checkin;
				
        $this->_db->setQuery($sql);
        $this->_db->query();

        // Check for a database error.
        if ($this->_db->getErrorNum()) {
            $this->setError($this->_db->getErrorMsg().' '.$sql);
            return false;
        }

        // If checkin is supported and all rows were adjusted, check them in.
        if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
            // Checkin each row.
            foreach ($pks as $pk) {
                $this->checkin($pk);
            }
        }

        // If the JTable instance value is in the list of primary keys that were set, set the instance.
        if (in_array($this->$k, $pks)) {
            $this->state = $state;
        }

        $this->setError('');

        ///***************************************************************////
		$templates = array(
			'list.php',
			'orders.php',
		);
		
		foreach($templates as $template_item){
			$mano111_tpl_file_i = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'views' . DS . 'orders' . DS . 'tmpl' . DS . $template_item;
			$mano111_tpl_file_o = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'views' . DS . 'orders' . DS . 'tmpl' . DS . $template_item; 
			$mano111_tpl_file_i_bcp = $mano111_tpl_file_i . "manoBCP";
			if (file_exists($mano111_tpl_file_i)) {
				if (file_exists($mano111_tpl_file_i_bcp)) {
					file_put_contents($mano111_tpl_file_i, file_get_contents($mano111_tpl_file_i_bcp));
				} else {
					file_put_contents($mano111_tpl_file_i_bcp, file_get_contents($mano111_tpl_file_i));
				}
				$mano111_tpl_file_i_current = file_get_contents($mano111_tpl_file_i);
				$mano111_tpl_file_i_new = $mano111_tpl_file_i_current;
				$musu_replace_array = array();

				$musu_i = 0;
				/* $musu_replace_array[$musu_i]['input']='AdminUIHelper::startAdminArea ();';
				  $musu_replace_array[$musu_i++]['output']='AdminUIHelper::startAdminArea ();
				  JHTML::script(\'myparcel_plugin.js\', JURI::base().\'components/com_myparcel/assets/js/\');'; */
				$musu_replace_array[$musu_i]['input'] = 'AdminUIHelper::startAdminArea';
				$musu_replace_array[$musu_i++]['output'] = '
					JHTML::script(\'myparcel_plugin.js\', JURI::base().\'components/com_myparcel/assets/js/\');
					JHTML::script(JURI::base().\'components/com_myparcel/assets/js/myparcel_plugin.js\');
					AdminUIHelper::startAdminArea';

				$musu_replace_array[$musu_i]['input'] = '<form action="index.php" method="post" name="adminForm" id="adminForm">';
				$musu_replace_array[$musu_i++]['output'] = '<style type="text/css">
						.myparcel_table { width:100%;min-width:190px; }
						.myparcel_table td { text-align:right; border: none !important; }
						.myparcel_table td.mypafunc { width:60px; vertical-align:top; padding-left:10px; }
						.myparcel_table div { white-space:nowrap; width:100%; }
						.myparcel_table img { width:18px; height:20px; margin-left:5px; vertical-align:top; }
						.mypaleft { float:left; margin-left:10px; }
						.myparcel-pdf myparight { float: right;}
						.gktopheader { width: 100%; }
						.gktopheader td {background-color: #f7f7f7 !important; border: none !important;}
					</style>
					<form action="index.php" method="post" name="adminForm" id="adminForm">';


				$musu_replace_array[$musu_i]['input'] = '<th><?php echo $this->sort (\'virtuemart_order_id\', \'COM_VIRTUEMART_ORDER_LIST_ID\')  ?></th>';
				$musu_replace_array[$musu_i++]['output'] = '<th><?php echo $this->sort (\'virtuemart_order_id\', \'COM_VIRTUEMART_ORDER_LIST_ID\')  ?></th>
				<th class="dataTableHeadingContent" align="right">

					
					<table class="gktopheader">
						<tbody><tr>
							<td style="width: 121px; text-align: center;">
								<?php $_SESSION[\'MYPARCEL_VISIBLE_CONSIGNMENTS\'] = \'\'; ?> MyParcel Labels <br/><input type="checkbox" onclick="selectAllConsignmentsForPrint(this);"/><a href="#" onclick="return printConsignmentSelection();" class="myparcel-pdf myparight"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_pdf.png" /></a>
								<a href="#" onclick="return processConsignmentSelection(\'<?php echo count ($this->orderslist); ?>\');" class="myparcel-pdf myparight"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_pdf_add.png" /></a>
							</td>
							<td class="mypafunc">
								Actions
							</td>
						</tr>
						</tbody></table>
				</th>';


				$musu_replace_array[$musu_i]['input'] = '<tr class="row<?php echo $k; ?>">';
				$musu_replace_array[$musu_i++]['output'] = '<tr id="og_plugin_<?php echo $k; ?>" class="row<?php echo $k; ?>">';


				$musu_replace_array[$musu_i]['input'] = 'COM_VIRTUEMART_ORDER_EDIT_ORDER_ID\') . \' \' . $order->virtuemart_order_id)); ?></td>';
				$musu_replace_array[$musu_i++]['output'] = 'COM_VIRTUEMART_ORDER_EDIT_ORDER_ID\') . \' \' . $order->virtuemart_order_id)); ?></td>
					<td class="dataTableContent" align="right">
						<table class="myparcel_table">
						<tr><td id="mypa_exist_<?php echo $order->virtuemart_order_id; ?>">
						<?php

							$db     = JFactory::getDBO();
							$query = "SELECT * FROM orders_myparcel WHERE orders_id = \'" . $order->virtuemart_order_id . "\'";
							$db->setQuery( $query );
							$vendors = $db->loadObjectlist();
							for ($i2=0, $n=count( $vendors ); $i2 < $n; $i2++) 
							{
								$row = &$vendors[$i2];


								$_SESSION[\'MYPARCEL_VISIBLE_CONSIGNMENTS\'] .= $row->consignment_id . \'|\';
								$mypa_tracktrace_link = \'https://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl&B=\' . $row->tracktrace . \'&P=\' . $row->postcode;
								$mypa_tnt_status      = empty($row->tnt_status) ? \'Track&Trace\' : $row->tnt_status;
								$mypa_pdf_image       = ($row->retour == 1) ? \'myparcel_retour.png\' : \'myparcel_pdf.png\';
								echo \'<div><input type="checkbox" value="\' . $row->consignment_id . \'" class="mypaleft mypacheck" /><a href="\' . $mypa_tracktrace_link . \'" target="_blank">\' . $mypa_tnt_status . \'</a><a href="#" onclick="return printConsignments(\\\'\' . $row->consignment_id . \'\\\');" class="myparcel-pdf"><img src="\'.JURI::base().\'components/com_myparcel/assets/images/\'.$mypa_pdf_image.\'" /></a></div>\';
							}
						?>
						</td><td class="mypafunc">
						<a href="#" class="myparcel-consignment-new" onclick="return createNewConsignment(\'<?php echo $order->virtuemart_order_id; ?>\');"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_pdf_add.png" /></a>
						<a href="#" class="myparcel-consignment-retour" onclick="return createNewConsignment(\'<?php echo $order->virtuemart_order_id; ?>\', true);"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_retour_add.png" /></a>

						</td></tr>
						</table>
					</td>';

				$musu_replace_array[$musu_i]['input'] = '<td colspan="12">';
				$musu_replace_array[$musu_i++]['output'] = '<td colspan="13">';




				// vm 2.0.0 ---->
				/* $musu_replace_array[$musu_i]['input']='AdminUIHelper::startAdminArea();';
				  $musu_replace_array[$musu_i++]['output']='AdminUIHelper::startAdminArea();
				  JHTML::script(\'myparcel_plugin.js\', JURI::base().\'components/com_myparcel/assets/js/\');'; */

				$musu_replace_array[$musu_i]['input'] = '<th><?php echo JHTML::_(\'grid.sort\', \'COM_VIRTUEMART_ORDER_LIST_ID\', \'virtuemart_order_id\', $this->lists[\'filter_order_Dir\'], $this->lists[\'filter_order\']); ?></th>';
				$musu_replace_array[$musu_i++]['output'] = '<th><?php echo JHTML::_(\'grid.sort\', \'COM_VIRTUEMART_ORDER_LIST_ID\', \'virtuemart_order_id\', $this->lists[\'filter_order_Dir\'], $this->lists[\'filter_order\']); ?></th>
				<th class="dataTableHeadingContent" align="right">

					
					<table class="gktopheader">
						<tbody><tr>
							<td style="width: 121px; text-align: center;">
								<?php $_SESSION[\'MYPARCEL_VISIBLE_CONSIGNMENTS\'] = \'\'; ?> MyParcel Labels <br/><input type="checkbox" onclick="selectAllConsignmentsForPrint(this);"/><a href="#" onclick="return printConsignmentSelection();" class="myparcel-pdf myparight"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_pdf.png" /></a>
								<a href="#" onclick="return processConsignmentSelection(\'<?php echo count ($this->orderslist); ?>\');" class="myparcel-pdf myparight"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_pdf_add.png" /></a>
							</td>
							<td class="mypafunc">
								Actions
							</td>
						</tr>
						</tbody></table>
				</th>';


				$musu_replace_array[$musu_i]['input'] = '<tr class="row<?php echo $k ; ?>">';
				$musu_replace_array[$musu_i++]['output'] = '<tr id="og_plugin_<?php echo $k ; ?>" class="row<?php echo $k; ?>">';

				$musu_replace_array[$musu_i]['input'] = '<td colspan="11">';
				$musu_replace_array[$musu_i++]['output'] = '<td colspan="12">';


				// vm 2.0.10
				$musu_replace_array[$musu_i]['input'] = '<th><?php echo $this->sort(\'virtuemart_order_id\', \'COM_VIRTUEMART_ORDER_LIST_ID\')  ?></th>';
				$musu_replace_array[$musu_i++]['output'] = '<th><?php echo $this->sort(\'virtuemart_order_id\', \'COM_VIRTUEMART_ORDER_LIST_ID\')  ?></th>
				<th class="dataTableHeadingContent" align="right">

					
					<table class="gktopheader">
						<tbody><tr>
							<td style="width: 121px; text-align: center;">
								<?php $_SESSION[\'MYPARCEL_VISIBLE_CONSIGNMENTS\'] = \'\'; ?> MyParcel Labels <br/><input type="checkbox" onclick="selectAllConsignmentsForPrint(this);"/><a href="#" onclick="return printConsignmentSelection();" class="myparcel-pdf myparight"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_pdf.png" /></a>
								<a href="#" onclick="return processConsignmentSelection(\'<?php echo count ($this->orderslist); ?>\');" class="myparcel-pdf myparight"><img src="<?php echo JURI::base(); ?>components/com_myparcel/assets/images/myparcel_pdf_add.png" /></a>
							</td>
							<td class="mypafunc">
								Actions
							</td>
						</tr>
						</tbody></table>
				</th>';


				if ($state == 1) {
					echo "ijungta";

					//echo "+++"; print_r($mano111_tpl_file_i_current); echo "---<br/>";
					foreach ($musu_replace_array AS $musu_replace_array_item) {
						$mano111_tpl_file_i_new = str_replace($musu_replace_array_item['output'], $musu_replace_array_item['input'], $mano111_tpl_file_i_new);
						$mano111_tpl_file_i_new = str_replace($musu_replace_array_item['input'], $musu_replace_array_item['output'], $mano111_tpl_file_i_new);
					}

					//print_r($mano111_tpl_file_i_current);die;
				} else {
					echo "ishjungta";
					foreach ($musu_replace_array AS $musu_replace_array_item) {
						$mano111_tpl_file_i_new = str_replace($musu_replace_array_item['output'], $musu_replace_array_item['input'], $mano111_tpl_file_i_new);
					}
					rename($mano111_tpl_file_i_bcp, $mano111_tpl_file_i_bcp . (microtime()));
				}
				file_put_contents($mano111_tpl_file_o, $mano111_tpl_file_i_new);
			}
		}

        //edit_address.php
        $db11 = JFactory::getDbo();
        $db11->setQuery('SELECT * FROM #__myparcel_config');
        $mano_db_result = $db11->loadAssoc();
        $mano_plugin_status = $mano_db_result['my_frontend_plugin'];        

        $mano111_tpl_file_i = JPATH_SITE . DS . 'components' . DS . 'com_virtuemart' . DS . 'views' . DS . 'user' . DS . 'tmpl' . DS . 'edit_address.php';
        $mano111_tpl_file_o = JPATH_SITE . DS . 'components' . DS . 'com_virtuemart' . DS . 'views' . DS . 'user' . DS . 'tmpl' . DS . 'edit_address.php';
        $mano111_tpl_file_i_bcp = $mano111_tpl_file_i . "manoBCP";
        if (file_exists($mano111_tpl_file_i)) {
            if (file_exists($mano111_tpl_file_i_bcp)) {
                file_put_contents($mano111_tpl_file_i, file_get_contents($mano111_tpl_file_i_bcp));
            } else {
                file_put_contents($mano111_tpl_file_i_bcp, file_get_contents($mano111_tpl_file_i));
            }

            $mano111_tpl_file_i_current = file_get_contents($mano111_tpl_file_i);
            $mano111_tpl_file_i_new = $mano111_tpl_file_i_current;
            $musu_replace_array = array();

            $musu_i = 0;
            $musu_replace_array[$musu_i]['input'] = 'if (!class_exists (\'VirtueMartCart\'))';
            $musu_replace_array[$musu_i++]['output'] = 'if ($this->address_type == \'ST\'): ?>
                <p>Kies <span onclick="return pakjegemak();" style="cursor: pointer; text-decoration: underline">hier</span> uw locatie indien u het pakket op een PostNL afleverlocatie wil laten bezorgen.</p>
                <?php
                    $db = JFactory::getDbo();
                    $db->setQuery(\'SELECT * FROM #__myparcel_config\');
                    $mano_db_result = $db->loadAssoc();
                    $username = $mano_db_result[\'my_name\'];
                    $api_key  = $mano_db_result[\'my_api_key\'];

                    $webshop = JURI::base().\'components\'.DS.\'com_myparcel\'.\'/myparcel-passdata-virtuemart.html\';
                    $uw_hash = hash_hmac(\'sha1\', $username . \'MyParcel\' . $webshop, $api_key);
                ?>
                <script type="text/javascript">
                var pg_popup;
                function pakjegemak()
                {
                    if(!pg_popup || pg_popup.closed)
                    {
                        pg_popup = window.open(
                            \'//www.myparcel.nl/pakjegemak-locatie?hash=<?php echo $uw_hash; ?>&webshop=<?php echo urlencode($webshop); ?>&user=<?php echo $username; ?>\',
                            \'myparcel-pakjegemak\',
                            \'width=980,height=680,dependent,resizable,scrollbars\'
                        );
                        if(window.focus) { pg_popup.focus(); }
                    }
                    else
                    {
                        pg_popup.focus();
                    }
                    return false;
                }
                </script>
                <?php endif; ?> <?php if (!class_exists (\'VirtueMartCart\'))';


            if (($state == 1) && ($mano_plugin_status == 1)) {
                echo "ijungta";

                //echo "+++"; print_r($mano111_tpl_file_i_current); echo "---<br/>";
                foreach ($musu_replace_array AS $musu_replace_array_item) {
                    $mano111_tpl_file_i_new = str_replace($musu_replace_array_item['output'], $musu_replace_array_item['input'], $mano111_tpl_file_i_new);
                    $mano111_tpl_file_i_new = str_replace($musu_replace_array_item['input'], $musu_replace_array_item['output'], $mano111_tpl_file_i_new);
                }

                //print_r($mano111_tpl_file_i_current);die;
            } else {
                echo "ishjungta";
                foreach ($musu_replace_array AS $musu_replace_array_item) {
                    $mano111_tpl_file_i_new = str_replace($musu_replace_array_item['input'], $musu_replace_array_item['output'], $mano111_tpl_file_i_new);
                }
                rename($mano111_tpl_file_i_bcp, $mano111_tpl_file_i_bcp . (microtime()));
            }
            file_put_contents($mano111_tpl_file_o, $mano111_tpl_file_i_new);




            ///***************************************************************////



            return true;
        } else {
            return false;
        }
    }

    /**
     * Define a namespaced asset name for inclusion in the #__assets table
     * @return string The asset name 
     *
     * @see JTable::_getAssetName 
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_myparcel.config.' . (int) $this->$k;
    }

    /**
     * Returns the parrent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
     *
     * @see JTable::_getAssetParentId 
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        // We will retrieve the parent-asset from the Asset-table
        $assetParent = JTable::getInstance('Asset');
        // Default: if no asset-parent can be found we take the global asset
        $assetParentId = $assetParent->getRootId();
        // The item has the component as asset-parent
        $assetParent->loadByName('com_myparcel');
        // Return the found asset-parent-id
        if ($assetParent->id) {
            $assetParentId = $assetParent->id;
        }
        return $assetParentId;
    }

}
