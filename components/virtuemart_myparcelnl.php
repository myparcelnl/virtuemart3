<?php
define('TABLE_VIRTUEMART_MYPARCEL_ORDER', 'myparcel_virtuemart_orders');
// Reference global application object
$app = JFactory::getApplication();
$db = JFactory::getDBO();
// get post data
$data = json_decode(trim(file_get_contents("php://input")), true);
if(!isset($data['data']['hooks']) && count($data['data']['hooks']) <= 0){
    exit;
}
$arrListStatusComment = array(
    1 => 'pending - concept',
    2 => 'pending - registered',
    3 => 'enroute - handed to carrier',
    4 => 'enroute - sorting',
    5 => 'enroute - distribution',
    6 => 'enroute - customs',
    7 => 'delivered - at recipient',
    8 => 'delivered - ready for pickup',
    9 => 'delivered - package picked up',
    10 => 'delivered - return shipment ready for pickup',
    11 => 'delivered - return shipment package picked up',
    12 => 'printed - letter',
    13 => 'inactive - credited',
    14 => 'printed - digital stamp',
    30 => 'inactive - concept',
    31 => 'inactive - registered',
    32 => 'inactive - enroute - handed to carrier',
    33 => 'inactive - enroute - sorting',
    34 => 'inactive - enroute - distribution',
    35 => 'inactive - enroute - customs',
    36 => 'inactive - delivered - at recipient',
    37 => 'inactive - delivered - ready for pickup',
    38 => 'inactive - delivered - package picked up',
    99 => 'inactive - unknown'
);

foreach ($data['data']['hooks'] as $value){
    //get myparcel_virtuemart_orders
    $q = "SELECT * FROM ". TABLE_VIRTUEMART_MYPARCEL_ORDER . " WHERE consignment_id =" . $value['shipment_id'];
    $db->setQuery($q);
    $virtuemartOrder = $db->loadAssoc();
    if($virtuemartOrder != null){
        $orderStatusCode = 'P';
        switch ($value['status']){
            case 7:
            case 8:
            case 9:
                $orderStatusCode = 'S';
                break;
            case 10:
            case 11:
                $orderStatusCode = 'X';
                break;
            default:
                $orderStatusCode = 'P';
                break;
        }
        // Update the filter with the new taxonomy ids.
        $query = $db->getQuery(true);
        $query
            ->update($db->qn('#__virtuemart_orders'))
            ->set($db->qn('order_status') . ' = ' . $db->q($orderStatusCode))
            ->where($db->qn('virtuemart_order_id') . ' = ' . (int) $virtuemartOrder['order_id']);
        $db->setQuery($query);
        $db->execute();

        $comment = end($arrListStatusComment);
        if(isset($arrListStatusComment[$value['status']])){
            $comment = $arrListStatusComment[$value['status']];
        }
        //add order_status_history
        $now = date('Y-m-d H:i:s', time());
        $sql = "INSERT INTO #__virtuemart_order_histories (virtuemart_order_id, order_status_code,comments,created_on,modified_on) 
                                VALUES ('".$virtuemartOrder['order_id']."','". $orderStatusCode ."','". $comment ."','".$now."','".$now."')";
        $db->setQuery($sql);
        $db->execute();
    }
}
exit;