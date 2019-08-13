<?php // No direct access
defined('_JEXEC') or die;

if(!class_exists('MyParcelController'))
	require_once(JPATH_ROOT.'/administrator/components/com_virtuemart_myparcelnl/helpers/myparcel_controller.php');


class MyparcelControllerConfiguration extends MyParcelController
{
	public function saveConfig(){
		$jinput = JFactory::getApplication()->input;
		$data = $jinput->POST->get('form','','array');
		
		if(is_array($data)){
			setMyparcelConfig($data);
		}
		
		JFactory::getApplication()->redirect('index.php?option=com_virtuemart_myparcelnl&view=configuration');
	}
}