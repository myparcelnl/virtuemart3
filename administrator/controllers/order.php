<?php // No direct access
defined('_JEXEC') or die;

if(!class_exists('MyParcelController'))
    require_once(JPATH_ROOT.'/administrator/components/com_virtuemart_myparcelnl/helpers/myparcel_controller.php');


class MyparcelControllerOrder extends MyParcelController
{
    public function exportVirtueMartOrder(){
        $jinput = JFactory::getApplication()->input;
        $orderId = (array) $jinput->POST->get('order_id', array());

        if(count($orderId) > 0){
            $arrOrderId = (array) $orderId;
            $response = exportMultiOrder($arrOrderId);
            die(json_encode($response));
        }
        die(json_encode(array('status' => false,'data' => [])));
    }
	
	public function checkConsignmentStatus(){
        $jinput = JFactory::getApplication()->input;
        $consignment_id = $jinput->POST->get('consignment_id', 0);
		$response = checkShippingStatus($consignment_id);
		die(json_encode($response));
	}
	
    public function checkVirtueMartOrder(){
        $jinput = JFactory::getApplication()->input;
        $orderIds = (array) $jinput->POST->get('order_ids', array());
		$listOrders = array();
				
		if(count($orderIds) > 0){
            $orderIds = (array) $orderIds;
			$listOrders = checkMyparcelOrders($orderIds);
		}
		die(json_encode(array('status' => true,'data' => $listOrders)));
    }

    public function printVirtueMartOrder(){
        $jinput = JFactory::getApplication()->input;
        $orderId = (array) $jinput->POST->get('order_id', array());
		
        if(count($orderId) > 0){
            $orderId = (array) $orderId;
            $response = printOrders($orderId);
            die(json_encode($response));
        }
        die(json_encode(array('status' => false,'data' =>[])));
    }
}