<?php // define
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('TABLE_ORDERS') or define('TABLE_ORDERS','#__virtuemart_orders');
defined('TABLE_MYPARCEL_CONFIG') or define('TABLE_MYPARCEL_CONFIG', 'myparcel_config');
defined('TABLE_VIRTUEMART_MYPARCEL_ORDER') or define('TABLE_VIRTUEMART_MYPARCEL_ORDER', 'myparcel_virtuemart_orders');
defined('TABLE_MYPARCEL_VIRTUEMART_ORDER_OPTION') or define('TABLE_MYPARCEL_VIRTUEMART_ORDER_OPTION', 'myparcel_virtuemart_order_options');
defined('DELIVERY_TYPE_MORNING') or define('DELIVERY_TYPE_MORNING', 1);
defined('DELIVERY_TYPE_STANDARD') or define('DELIVERY_TYPE_STANDARD', 2);
defined('DELIVERY_TYPE_NIGHT') or define('DELIVERY_TYPE_NIGHT', 3);

$rootFolder = explode(DS, dirname(__FILE__));

//current level in diretoty structure
$currentfolderlevel = 3;

array_splice($rootFolder,-$currentfolderlevel);
$base_folder = implode(DS,$rootFolder);

if(is_dir($base_folder.DS.'libraries'.DS.'joomla')) {
    if(!defined('_JEXEC')) define('_JEXEC', 1);
    if(!defined('JPATH_BASE')) define('JPATH_BASE',implode(DS,$rootFolder));

    require_once(JPATH_BASE .DS.'includes'.DS.'defines.php');
    require_once(JPATH_BASE .DS.'includes'.DS.'framework.php');
}
if(!class_exists('MyParcelNL\\Sdk\\src\\Helper\\MyParcelCollection')){
    require_once ('libraries/myparcelnl/AutoLoader.php');
}
function isSSL(){
    return (
        ( ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ( ! empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        || ( ! empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)
        || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https')
    );
}

function getPaperFormat(){
    return array(
        'A4' => 'A4',
        'A6' => 'A6',
    );
}

function getMyparcelConfigRow(){
    // define
    $db = JFactory::getDBO();

    // get query
    $sql = "SELECT * FROM ".TABLE_MYPARCEL_CONFIG." LIMIT 1";
    $db->setQuery($sql);
    $data = $db->loadAssoc();

    if(!$data){
        // insert query
        $now = date('Y-m-d H:i:s', time());
        $sql = "INSERT INTO ".TABLE_MYPARCEL_CONFIG." (updated_at, configs) VALUES ('".$now."', NULL)";
        $db->setQuery($sql);
        $db->execute();
    }

    return $data;
}

function getMyparcelConfig(){
    $row = getMyparcelConfigRow();

    if(!isset($row['configs'])) return array();

    $configs = @unserialize($row['configs']);

    return ($configs) ? $configs : getMyparcelConfigDefault();
}

function getMyparcelConfigDefault(){
    return array(
        'connect_customer_email' => 1,
        'return_no_answer' => 1,
        'package_type' => 1,
    );
}

function setMyparcelConfig($config = array()){
    // define
    $db = JFactory::getDBO();
    $config = (array) $config;
    $row = getMyparcelConfigRow();

    if(isset($row['id']) && count($config) > 0){
        $config = serialize($config);
        $now = date('Y-m-d H:i:s', time());
        $sql = "UPDATE ".TABLE_MYPARCEL_CONFIG." SET updated_at = '".$now."', configs = '".$config."' WHERE id = ".$row['id']." ";
        $db->setQuery($sql);
        $db->execute();

        return true;
    }

    return false;
}

function checkMyparcelOrders($listOrderID = array()){
    if(count($listOrderID) == 0) return array();

    $db = JFactory::getDBO();

    $sql = 'SELECT * FROM '.TABLE_VIRTUEMART_MYPARCEL_ORDER.' WHERE order_id IN('.implode(',', $listOrderID).')';
    $db->setQuery($sql);
    return $db->loadObjectList();
}

function getOrder($virtuemartOrderId = 0){
    $db = JFactory::getDBO();
    $order = array();
    $virtuemartOrderId = (int)$virtuemartOrderId;

    // Get the order details
    $q = "SELECT  u.*,o.*,
		s.order_status_name,state.state_name as virtuemart_state_name,c.country_2_code as virtuemart_country_2_code
		FROM #__virtuemart_orders o
		LEFT JOIN #__virtuemart_orderstates s
		ON s.order_status_code = o.order_status
		LEFT JOIN #__virtuemart_order_userinfos u
		ON u.virtuemart_order_id = o.virtuemart_order_id
		LEFT JOIN #__virtuemart_states state 
		ON state.virtuemart_state_id = u.virtuemart_state_id
		LEFT JOIN #__virtuemart_countries c 
		ON c.virtuemart_country_id = u.virtuemart_country_id
		WHERE o.virtuemart_order_id=".$virtuemartOrderId;
    $db->setQuery($q);
    $order['details'] = $db->loadObjectList('address_type');

    // Get the order items
    $q = 'SELECT virtuemart_order_item_id, product_quantity, order_item_name,
		order_item_sku, i.virtuemart_product_id, product_item_price,
		product_final_price, product_basePriceWithTax, product_discountedPriceWithoutTax, product_priceWithoutTax, product_subtotal_with_tax, product_subtotal_discount, product_tax, product_attribute, order_status,
		intnotes, virtuemart_category_id
		FROM (#__virtuemart_order_items i
		LEFT JOIN #__virtuemart_products p
		ON p.virtuemart_product_id = i.virtuemart_product_id)
		LEFT JOIN #__virtuemart_product_categories c
		ON p.virtuemart_product_id = c.virtuemart_product_id
		WHERE `virtuemart_order_id`="'.$virtuemartOrderId.'" group by `virtuemart_order_item_id`';
    //group by `virtuemart_order_id`'; Why ever we added this, it makes trouble, only one order item is shown then.
    // without group by we get the product 3 times, when it is in 3 categories and similar, so we need a group by
    //lets try group by `virtuemart_order_item_id`
    $db->setQuery($q);
    $order['items'] = $db->loadObjectList();

    $q = "SELECT  *
		FROM #__virtuemart_order_calc_rules AS z
		WHERE  virtuemart_order_id=".$virtuemartOrderId;
    $db->setQuery($q);
    $order['calc_rules'] = $db->loadObjectList();

    return $order;
}

//get number and street from address
function getAddressComponents($address)
{
    $ret = array();
    $address = trim($address);
    $is_single_word = (strpos($address, ' ') === false) ? true : false;


    $arrExclude = [' bus ', ' bte ', ' box ',' boîte '];
    foreach ($arrExclude as $value){
        if(strpos($address,$value) !== false){
            $address = explode($value,$address)[0];
            break;
        }
    }

    if ($is_single_word) {
        $ret['street']          = $address;
        $ret['house_number']    = '';
        $ret['number_addition'] = '';

        return $ret;
    }

//        $parts = explode(' ', $address);
//        if (!empty($parts) && !is_numeric($parts[count($parts) - 1])) {
//            $ret['street']          = $address;
//            $ret['house_number']    = '';
//            $ret['number_addition'] = '';
//
//            return $ret;
//        }

    $matches = _splitStreet($address);

    if (isset($matches['street'])) {
        $ret['street'] = $matches['street'];
    }

    if (isset($matches['number'])) {
        $ret['house_number']    = trim($matches['number']);
    }

    if (isset($matches['number_suffix'])) {
        $ret['number_addition']    = trim($matches['number_suffix']);
    }

    if (empty($ret['street'])) {
        $ret['street'] = $address;
    }

    /** START @Since the fix for negative house number (64-69) **/
    if (strlen($ret['street']) && substr($ret['street'], -1) == '-') {
        $ret['street'] = str_replace(' -', '', $ret['street']);
        $ret['street'] = str_replace('-', '', $ret['street']);
        $ret['street'] .= ' -' . $ret['house_number'];
        $ret['force_addition_number'] = true;
        return _splitMultipleHouseNumberStreet( $ret['street'] ,$ret['number_addition']);
    }
    /** END @Since the fix for negative house number (64-69) **/

    return $ret;
}

function _splitStreet($fullStreet = ''){
    $split_street_regex = '~(?P<street>.*?)\s?(?P<street_suffix>(?P<number>[\d]+)-?(?P<number_suffix>[a-zA-Z/\s]{0,5}$|[0-9/]{0,5}$|\s[a-zA-Z]{1}[0-9]{0,3}$))$~';
    $fullStreet = preg_replace("/[\n\r]/", "", $fullStreet);
    $result = preg_match($split_street_regex, $fullStreet, $matches);

    if (!$result || !is_array($matches) || $fullStreet != @$matches[0]) {
        if ($fullStreet != @$matches[0]) {
            // Characters are gone by preg_match
            echo json_encode(array('status' => false, 'message' => 'Something went wrong with splitting up address ' . $fullStreet));
            exit();
        } else {
            // Invalid full street supplied
            echo json_encode(array('status' => false, 'message' => 'Invalid full street supplied: ' . $fullStreet));
            exit();
        }
    }

    return $matches;
}

/** START @Since the fix for negative house number (64-69)
 * 64 is house number and 69 is additional number
 **/
function _splitMultipleHouseNumberStreet($address, $number_addition = '', $force=true)
{
    $ret = array();
    $ret['house_number']    = '';
    $ret['number_addition'] = '';

    $address = str_replace(array('?', '*', '[', ']', ',', '!'), ' ', $address);
    $address = preg_replace('/\s\s+/', ' ', $address);

    preg_match('/^([0-9]*)(.*?)([0-9]+)(.*)/', $address, $matches);

    if (!empty($matches[2]))
    {
        $ret['street']          = trim($matches[1] . $matches[2]);
        $ret['house_number']    = trim($matches[3]);
        $ret['number_addition'] = trim($matches[4]);
        if($number_addition != ''){
            $ret['number_addition'] = str_replace('-','',$ret['number_addition']);
            $ret['number_addition'].= ' ' . $number_addition;
        }
    }
    else // no street part
    {
        $ret['street'] = $address;
    }

    if ($force) {
        $ret['force_addition_number'] = true;
    }

    return $ret;
}
/** END @Since the fix for negative house number (64-69) **/

function exportMultiOrder($listOrderID = array()){
    if(count($listOrderID) == 0) return array('status' => false, 'message' => 'No order.');

    // get api key
    $myParcelNlConfig = getMyparcelConfig();
    $apiKey = $myParcelNlConfig['api_key'];
    $connect_email = (int) @$myParcelNlConfig['connect_customer_email'];
    $connect_phone = (int) @$myParcelNlConfig['connect_customer_phone'];
    $label_description = @$myParcelNlConfig['label_description'];
    $package_type = (int) @$myParcelNlConfig['package_type'];
    $config_address_type = (int) @$myParcelNlConfig['use_addition_address_as_number_suffix'];

    // package 1
    $only_recipient = ($package_type == 1) ? (int) @$myParcelNlConfig['only_recipient'] : 0;
    $extra_large_size = ($package_type == 1) ? (int) @$myParcelNlConfig['extra_large_size'] : 0;
    $return_no_answer = ($package_type == 1) ? (int) @$myParcelNlConfig['return_no_answer'] : 0;
    $signature_delivery = ($package_type == 1) ? (int) @$myParcelNlConfig['signature_delivery'] : 0;
    $age_check = ($package_type == 1) ? (int) @$myParcelNlConfig['age_check'] : 0;
    $insured = ($package_type == 1) ? (int) @$myParcelNlConfig['insured'] : 0;
    $insured_amount = ($insured == 1) ? (int) @$myParcelNlConfig['insured_amount'] : 0;
    $insured_amount_value = (int) @$myParcelNlConfig['insured_amount_value'];
    if($insured == 1 && $insured_amount == 0){
        $insured_amount = $insured_amount_value;
    }

    // package 4
    $default_weight = ($package_type == 1) ? (int) @$myParcelNlConfig['default_weight'] : 0; // kg

    $shipmentParams = array(
        'data' => array(
            'shipments' => array(),
        )
    );
    $arrOrderIdCreateShippment = [];
    // define
    $db = JFactory::getDBO();
    foreach ($listOrderID as $virtuemartOrderId){
        $order_label_description = '';

        //get myparcel_virtuemart_orders
        $q = "SELECT * FROM ". TABLE_VIRTUEMART_MYPARCEL_ORDER . " WHERE order_id =" . $virtuemartOrderId;
        $db->setQuery($q);
        $virtuemartOrder = $db->loadAssoc();
        $virtuemartOrder = null;
        //if order has been created shipment
        if($virtuemartOrder == null){
            $arrOrderIdCreateShippment[] = $virtuemartOrderId;

            //get order by order_id
            $order = getOrder($virtuemartOrderId);

            // Label description
            if($label_description != ''){
                $order_label_description = str_replace('[ORDER_NR]', $virtuemartOrderId, $label_description);
            }
            $orderOptions = getMyparcelOrderDeliveryOptions($virtuemartOrderId);


            // Get options
            $options = array(
                'package_type' => $package_type,
                'only_recipient' => (isset($orderOptions['recipient_only'])) ? intval($orderOptions['recipient_only'])  : $only_recipient,
                'signature' => (isset($orderOptions['signed'])) ? intval($orderOptions['signed'])  : $signature_delivery,
                'return' => $return_no_answer,
                'label_description' => $order_label_description,
                'large_format' => $extra_large_size,
                'age_check' => $age_check,
                'insurance' => array(
                    'amount' => $insured_amount,
                    'currency' => 'EUR',
                ),
            );
            $options = array_merge($options, getOrderOptions($virtuemartOrderId));


            // All data
            $reference_identifier = @$order['details']['BT']->order_number;
            $region = (@$order['details']['ST']->virtuemart_state_name == '') ? @$order['details']['BT']->virtuemart_state_name : @$order['details']['ST']->virtuemart_state_name;
            $person_title = (@$order['details']['ST']->title == '') ? @$order['details']['BT']->title : $order['details']['ST']->title;
            $person_first_name = (@$order['details']['ST']->first_name == '') ? @$order['details']['BT']->first_name : $order['details']['ST']->first_name;
            $person_middle_name = (@$order['details']['ST']->middle_name == '') ? @$order['details']['BT']->middle_name : $order['details']['ST']->middle_name;
            $person_last_name = (@$order['details']['ST']->last_name == '') ? @$order['details']['BT']->last_name : $order['details']['ST']->last_name;
            $address_city = (@$order['details']['ST']->city == '') ? @$order['details']['BT']->city : $order['details']['ST']->city;
            $address_zip = (@$order['details']['ST']->zip == '') ? @$order['details']['BT']->zip : $order['details']['ST']->zip;
            $address_number = (@$order['details']['ST']->house_number == '') ? @$order['details']['BT']->house_number : $order['details']['ST']->house_number;
            $address_number_suffix = (@$order['details']['ST']->house_number_add == '') ? @$order['details']['BT']->house_number_add : $order['details']['ST']->house_number_add;
            $country_2_code = (@$order['details']['ST']->virtuemart_country_2_code == '') ? @$order['details']['BT']->virtuemart_country_2_code : $order['details']['ST']->virtuemart_country_2_code;
            $company = (@$order['details']['ST']->company == '') ? @$order['details']['BT']->company : $order['details']['ST']->company;
            $email = (@$order['details']['ST']->email == '') ? @$order['details']['BT']->email : $order['details']['ST']->email;
            $phone_1 = (@$order['details']['ST']->phone_1 == '') ? @$order['details']['BT']->phone_1 : $order['details']['ST']->phone_1;
            $phone_2 = (@$order['details']['ST']->phone_2 == '') ? @$order['details']['BT']->phone_2 : $order['details']['ST']->phone_2;
            $fax = (@$order['details']['ST']->fax == '') ? @$order['details']['BT']->fax : $order['details']['ST']->fax;
            $address_1 = (@$order['details']['ST']->address_1 == '') ? @$order['details']['BT']->address_1 : $order['details']['ST']->address_1;
            $address_2 = (@$order['details']['ST']->address_2 == '') ? @$order['details']['BT']->address_2 : $order['details']['ST']->address_2;



            // Get address component
            $number = '';
            $street = '';
            $number_suffix = '';

            switch($config_address_type){
                case '1':
                    $streetAddress = getAddressComponents($address_1);
                    $number = @$streetAddress['house_number'] ?? $address_number;
                    $street = @$streetAddress['street'];
                    $number_suffix = empty($address_2) ? '' : $address_2;
                    break;

                case '2':
                    $streetAddress = getAddressComponents($address_1.' '.$address_2);
                    $number = @$streetAddress['house_number'];
                    $street = @$streetAddress['street'];
                    $number_suffix = @$streetAddress['number_addition'];
                    break;

                default: // 0
                    $streetAddress = getAddressComponents($address_1);
                    $street = @$streetAddress['street'];
                    $number = (@$streetAddress['house_number'] == '') ? $address_number : @$streetAddress['house_number'];
                    $number_suffix = (@$streetAddress['number_addition'] == '') ? (string) $address_number_suffix : @$streetAddress['number_addition'];
            }

            // name
            $person = '';
            $person .= (!empty($person_title)) ? trim($person_title).'. ' : '';
            $person .= (!empty($person_first_name)) ? trim($person_first_name).' ' : '';
            $person .= (!empty($person_middle_name)) ? trim($person_middle_name).' ' : '';
            $person .= (!empty($person_last_name)) ? trim($person_last_name).' ' : '';

            $pickupData = null;
            if ( $pickup = isPickup( $virtuemartOrderId ) ) {
                $pickupData = array(
                    'postal_code'	=> $pickup['postal_code'],
                    'street'		=> $pickup['street'],
                    'city'			=> $pickup['city'],
                    'number'		=> $pickup['number'],
                    'location_name'	=> $pickup['location'],
                );

                if ($country_2_code == 'BE') {
                    $pickupData['location_code'] = $pickup['location_code'];
                    $pickupData['retail_network_id'] = $pickup['retail_network_id'];
                }
                $options['only_recipient'] = 1;
                $options['signature'] = 1;
                $options['return'] = 0;
                $options['age_check'] = 0;

            }

            if($mailbox = isMailbox( $virtuemartOrderId )){
                $options['package_type'] = 2;
                $illegal_options = array( 'delivery_type', 'only_recipient', 'signature', 'return', 'large_format', 'insurance', 'delivery_date' );
                foreach ($options as $key => $option) {
                    if (in_array($key, $illegal_options)) {
                        unset($options[$key]);
                    }
                }
            }

            $shipmentParams['data']['shipments'][] = array(
                'reference_identifier' => $reference_identifier,
                'recipient' => array(
                    'cc' => $country_2_code,
                    'region' => (!empty($region)) ? $region : '',
                    'city' => $address_city,
                    'street' => $street,
                    'number' => $number,
                    'number_suffix' => $number_suffix,
                    'postal_code' => $address_zip,
                    'person' => $person,
                    'company' => (!empty($company)) ? $company : '',
                    'phone' => ($connect_phone == 0) ? '' : (string) $phone_1,
                    'email' => ($connect_email == 0) ? '' : $email,
                ),
                'options' => $options,
                'pickup' => $pickupData,
                'carrier' => 1,
                'physical_properties' => array(
                    'weight' => ($default_weight * 1000), // gram
                ),
            );
        }
    }
    if(count($arrOrderIdCreateShippment) == 0) return array('status' => true,'data' => array());

    // object
    $requestModel = new \MyParcelNL\Sdk\src\Model\MyParcelRequest();

    try{
        // request
        $requestModel->setRequestParameters($apiKey, json_encode($shipmentParams), \MyParcelNL\Sdk\src\Model\MyParcelRequest::REQUEST_HEADER_SHIPMENT);
        $requestModel->sendRequest();
        $response = $requestModel->getResult();

        if(isset($response['data']['ids'])){
            foreach ($response['data']['ids'] as $key => $data){
                $now = date('Y-m-d H:i:s', time());
                $sql = "INSERT INTO ".TABLE_VIRTUEMART_MYPARCEL_ORDER." 
				(order_id, consignment_id, postcode, created_at) 
				VALUES ('". $arrOrderIdCreateShippment[$key] ."','". $data['id'] ."','".$shipmentParams['data']['shipments'][$key]['recipient']['postal_code']."','".$now."')";
                $db->setQuery($sql);
                $db->execute();
            }
            return array('status' => true, 'data' => $response);
        }else{
            return array('status' => false, 'data' => $response);
        }
    }catch(Exception $e){
        $error_msg = (!empty(@$requestModel->getError())) ? $requestModel->getError() : $e->getMessage();
        return array('status' => false, 'message' => $error_msg);
    }
}

function printOrders($virtuemartOrderID = array(),$position_label = array()){
    if(count($virtuemartOrderID) == 0) return array('status' => false,'message' => 'No order.');

    // define
    $db = JFactory::getDBO();

    //get myparcel_virtuemart_orders
    $q = "SELECT * FROM ". TABLE_VIRTUEMART_MYPARCEL_ORDER . " WHERE order_id IN(" . implode(',', $virtuemartOrderID).") order by order_id ASC";
    $db->setQuery($q);
    $virtuemartOrders = $db->loadObjectList();
    $positions = array();
    $consignment_ids = array();

    if($virtuemartOrders == null){
        return array('status' => false, 'data' => []);
    }else{
        foreach($virtuemartOrders as $i=>$virtuemartOrder){
            array_push($positions, ($i+1));
            array_push($consignment_ids, $virtuemartOrder->consignment_id);
        }
    }

    $rootPath = str_replace('administrator','',JPATH_BASE);
    $myParcelPath = $rootPath.'images/virtuemart/myparcel_nl/';
    $myParcelPathToDay = $myParcelPath. date('Y-m-d');
    $allDirs = array_filter(glob($myParcelPath.'*'), 'is_dir');

    if(!empty($allDirs)){
        foreach ($allDirs as $dir){
            if($dir != $myParcelPathToDay){
                foreach(glob($dir . '/' . '*') as $file) {
                    unlink($file);
                }
                rmdir($dir);
            }
        }
    }
    if (!is_dir($myParcelPathToDay)) {
        $ret = mkdir($myParcelPathToDay,0777,true);
    }
    $pathFile = $myParcelPathToDay .'/'. implode('_', $consignment_ids) . '.pdf';
    $pathFileReturn = JUri::root() . str_replace('\\','/',str_replace($rootPath,'',$pathFile));


    if(file_exists($pathFile)){
//        return array('status' => true, 'data' => ['path_file' => $pathFileReturn]);
        unlink($pathFile);
    }

    // object
    $requestModel = new \MyParcelNL\Sdk\src\Model\MyParcelRequest();

    try{
        // get api key
        $myParcelNlConfig = getMyparcelConfig();
        $apiKey = $myParcelNlConfig['api_key'];
        $paper_format = (isset($myParcelNlConfig['paper_format'])) ? $myParcelNlConfig['paper_format'] : 'A4';
        if(empty($position_label)){
            $position_label = $positions;
        }

        // request
        $requestModel->setRequestParameters($apiKey, '', 'User-Agent:CustomApiCall/2;');
        $requestModel->sendRequest('GET','shipment_labels/'. implode(';', $consignment_ids) . '?format='. $paper_format .'&positions='. implode(';', $position_label) . '');
        $response =  $requestModel->getResult();
        $bytes = file_put_contents($pathFile, $response);

        if($bytes > 0) return array('status' => true, 'data' => ['path_file' => $pathFileReturn]);
        else return array('status' => false);
    }catch(Exception $e){
        $error_msg = (!empty(@$requestModel->getError())) ? $requestModel->getError() : $e->getMessage();
        return array('status' => false, 'message' => $error_msg);
    }
}

function checkShippingStatus($consignment_id = 0){
    // object
    $requestModel = new \MyParcelNL\Sdk\src\Model\MyParcelRequest();

    try{
        // get api key
        $myParcelNlConfig = getMyparcelConfig();
        $apiKey = $myParcelNlConfig['api_key'];

        // request
        $requestModel->setRequestParameters($apiKey, '', 'User-Agent:CustomApiCall/2;');
        $requestModel->sendRequest('GET','tracktraces/'. $consignment_id);
        $response =  $requestModel->getResult();

        if(!isset($response['data']['tracktraces'])) return array('status' => false);

        $tracktraces = $response['data']['tracktraces'];

        if(count($tracktraces) == 0) return array('status' => false,);

        return array('status' => true, 'tracktrace' => $tracktraces[0]);
    }catch(Exception $e){
        $error_msg = (!empty(@$requestModel->getError())) ? $requestModel->getError() : $e->getMessage();
        return array('status' => false, 'message' => $error_msg);
    }

}

function getMyparcelOrderDeliveryOptions($orderId){
    $db = JFactory::getDBO ();

    $q = "SELECT *  FROM ". TABLE_MYPARCEL_VIRTUEMART_ORDER_OPTION ." WHERE order_id=" . (int)$orderId;

    $db->setQuery ($q);
    $data = $db->loadAssoc();
    return $data;

}

function isPickup($orderId){
    $orderOptions = getMyparcelOrderDeliveryOptions($orderId);
    $deliveryOption = (isset($orderOptions['delivery_options'])) ? json_decode($orderOptions['delivery_options'],true) : null;
    if($deliveryOption == null)
        return false;

    $pickup_types = array( 'retail', 'retailexpress' );
    if ( !empty($deliveryOption['price_comment']) && in_array($deliveryOption['price_comment'], $pickup_types) ) {
        return $deliveryOption;
    } else {
        return false;
    }
}

function isMailbox($orderId){
    $orderOptions = getMyparcelOrderDeliveryOptions($orderId);
    $deliveryOption = (isset($orderOptions['delivery_options'])) ? json_decode($orderOptions['delivery_options'],true) : null;
    if($deliveryOption == null)
        return false;


    if ( isset($deliveryOption['time'][0]['price_comment'])  && $deliveryOption['time'][0]['price_comment'] == 'mailbox') {
        return $deliveryOption;
    } else {
        return false;
    }
}

function getOrderOptions($orderId){
    $options = [];
    $orderOptions = getMyparcelOrderDeliveryOptions($orderId);
    $deliveryOption = (isset($orderOptions['delivery_options'])) ? json_decode($orderOptions['delivery_options'],true) : null;
    if($deliveryOption == null)
        return [];
    $delivery_time = null;
    $delivery_type = DELIVERY_TYPE_STANDARD; //default is the standard shipping
    if(isset($deliveryOption['time'])){
        $delivery_time = array_shift($deliveryOption['time']); // take first element in time array
    }

    if ( !empty($deliveryOption) && !empty($deliveryOption['date']) ) {
        $delivery_date = $deliveryOption['date'];
        $delivery_type = (isset($delivery_time['type'])) ? intval($delivery_time['type']) : $delivery_type;

        if ( in_array($delivery_type, array(DELIVERY_TYPE_MORNING, DELIVERY_TYPE_NIGHT)) && !empty($delivery_time) ) {
            $delivery_time = $delivery_time['start'];
            $options['only_recipient'] = 1;
            $options['age_check'] = 0;
        } else {
            $delivery_time = '00:00:00';
        }
        $delivery_date = "{$delivery_date} {$delivery_time}";
        $options['delivery_date'] = $delivery_date;
    }
    $options['delivery_type'] = $delivery_type;
    return $options;
}
