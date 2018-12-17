<?php
/**
* ----------------------------------------------------------------------------------------------------------------------------
* @purpose:   Installation of MyParcel Plugin
*
* @editors    MB
* @version    1.1.3
* @since      Available since release 1.0
* @support    info@myparcel.nl
* @copyright  2011 MyParcel
* @link       http://www.myparcel.nl
* ----------------------------------------------------------------------------------------------------------------------------
*/

//require('includes/application_top.php');

define('MYPARCEL_LINK', 'https://www.myparcel.nl/');
define( 'DS', DIRECTORY_SEPARATOR );
define('TABLE_ORDERS','#__virtuemart_orders');
$rootFolder = explode(DS,dirname(__FILE__));
//current level in diretoty structure
$currentfolderlevel = 3;

array_splice($rootFolder,-$currentfolderlevel);

$base_folder = implode(DS,$rootFolder);


if(is_dir($base_folder.DS.'libraries'.DS.'joomla'))   
{
   
   define( '_JEXEC', 1 );
   
   define('JPATH_BASE',implode(DS,$rootFolder));
   
   require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
   require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
}
$db = JFactory::getDBO();

/*
 *   FUNCTIONS
 */
function getOrderz($virtuemart_order_id){
		$db = JFactory::getDBO();
		$virtuemart_order_id = (int)$virtuemart_order_id;

		$order = array();

		// Get the order details
		$q = "SELECT  u.*,o.*,
				s.order_status_name
			FROM #__virtuemart_orders o
			LEFT JOIN #__virtuemart_orderstates s
			ON s.order_status_code = o.order_status
			LEFT JOIN #__virtuemart_order_userinfos u
			ON u.virtuemart_order_id = o.virtuemart_order_id
			WHERE o.virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['details'] = $db->loadObjectList('address_type');

		// Get the order history
		$q = "SELECT *
			FROM #__virtuemart_order_histories
			WHERE virtuemart_order_id=".$virtuemart_order_id."
			ORDER BY virtuemart_order_history_id ASC";
		$db->setQuery($q);
		$order['history'] = $db->loadObjectList();

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
  WHERE `virtuemart_order_id`="'.$virtuemart_order_id.'" group by `virtuemart_order_item_id`';
//group by `virtuemart_order_id`'; Why ever we added this, it makes trouble, only one order item is shown then.
// without group by we get the product 3 times, when it is in 3 categories and similar, so we need a group by
//lets try group by `virtuemart_order_item_id`
		$db->setQuery($q);
		$order['items'] = $db->loadObjectList();
// Get the order items
		$q = "SELECT  *
			FROM #__virtuemart_order_calc_rules AS z
			WHERE  virtuemart_order_id=".$virtuemart_order_id;
		$db->setQuery($q);
		$order['calc_rules'] = $db->loadObjectList();
// 		vmdebug('getOrder my order',$order);
		return $order;
}

/* Since Pg_address drop number suffix */
function isPgAddress($name, $street, $gkzip, $gkcity)
{
	$db = JFactory::getDBO();
	$query_pg_address = $db->setQuery(
		sprintf('
                SELECT * FROM `orders_myparcel_pg_address` WHERE `name`="%s" AND `street`="%s" AND `house_number`="%s" AND `postcode`="%s" AND `town`="%s" LIMIT 1'
			,
			$name,                                                          // Name
			isset($street['street']) ? $street['street'] : '',              // Street
			isset($street['house_number']) ? $street['house_number'] : '',  // House number
			$gkzip,                                                         // Postcode
			$gkcity                                                         // City
		)
	);

	$query_pg_address->execute();

	if ($query_pg_address->getNumRows()) {
		return true;
	}
	return false;
}
/*---------------------------------------*/

function getAddressComponents($address)
{
	$ret = array();
	$ret['house_number'] = '';
	$ret['number_addition'] = '';
	//$address = 'Markerkant 10 11E';
	$address = str_replace(array('?', '*', '[', ']', ',', '!'), ' ', $address);
	$address = preg_replace('/\s\s+/', ' ', $address);

	$matches = _splitStreet($address);

	if (!empty($matches[2])) {
		$ret['street'] = trim($matches[1]);
		$ret['house_number'] = trim($matches[3]);
		$ret['number_addition'] = trim($matches[4]);
	} else {
		$ret['street'] = $address;
	}

	/** START @Since the fix for negative house number (64-69) **/
	if (strlen($ret['street']) && substr($ret['street'], -1) == '-') {
		$ret['street'] = str_replace(' -', '', $ret['street']);
		return getAddressComponents( $ret['street']);
	}
	/** END @Since the fix for negative house number (64-69) **/

	return $ret;
}

function _splitStreet($fullStreet)
{
	$split_street_regex = '~(?P<street>.*?)\s?(?P<street_suffix>(?P<number>[\d]+)-?(?P<number_suffix>[a-zA-Z/\s]{0,5}$|[0-9/]{0,5}$|\s[a-zA-Z]{1}[0-9]{0,3}$))$~';
	$fullStreet = preg_replace("/[\n\r]/", "", $fullStreet);
	$result = preg_match($split_street_regex, $fullStreet, $matches);

	if (!$result || !is_array($matches) || $fullStreet != $matches[0]) {
		if ($fullStreet != $matches[0]) {
			// Characters are gone by preg_match
			exit('Something went wrong with splitting up address ' . $fullStreet);
		} else {
			// Invalid full street supplied
			exit('Invalid full street supplied: ' . $fullStreet);
		}
	}

	return $matches;
}

/** START Since version 1.0.9 **/
function getReturnUrlWithUri($uri){
    $return_url = 'http'.((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) == "on"?'s':'').'://'.$_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'].'?'.$uri;
    return $return_url;
}
/** END Since version 1.0.9 **/

/*
 *   JAVASCRIPT ACTIONS
 */
if(isset($_GET['action']))
{
    /*
     *   MYPARCEL STATUS UPDATE
     *
     *   Every time this script is called, it will check if an update of the order statuses is required
     *   Depending on the last update with a timeout, since TNT updates our status 2 times a day anyway
     *
     *   NOTE - Increasing this timeout is POINTLESS, since TNT updates our statuses only 2 times a day
     *          Please save our bandwidth and use the Track&Trace link to get the actual status. Thanks
     */

	/** START Since version 1.0.9 **/
	if (version_compare(phpversion(), '5.4.0', '<')) {
		if (session_id() == '') session_start();
	} else {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
	}

	if (empty($_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'])) {
		$_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'] = '';
		$db     = JFactory::getDBO();
		$query = "SELECT *  FROM orders_myparcel WHERE tnt_final = 0 AND tnt_updated_on < '" . date('Y-m-d H:i:s', time() - 43200) . "'";
		$db->setQuery( $query );
		$vendors = $db->loadObjectlist();
		$all_consignments = array();
		for ($i=0, $n=count( $vendors ); $i < $n; $i++) {
			$row = &$vendors[$i];
			$all_consignments[] = $row->consignment_id;
		}
	}

	/** END Since version 1.0.9 **/

    if(
		(isset($_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS']) && !empty($_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'])) ||
		!empty($all_consignments)
	)
    {
		if (empty($all_consignments)) {
			$visible_consignments = str_replace('|', ',', trim($_SESSION['MYPARCEL_VISIBLE_CONSIGNMENTS'], '|'));

			$db = JFactory::getDBO();
			$query = "SELECT *  FROM orders_myparcel WHERE consignment_id IN (" . $visible_consignments . ") AND tnt_final = 0 AND tnt_updated_on < '" . date('Y-m-d H:i:s', time() - 43200) . "'";
			$db->setQuery($query);
			$vendors = $db->loadObjectlist();
			$consignments = array();
			for ($i = 0, $n = count($vendors); $i < $n; $i++) {
				$row = &$vendors[$i];
				$consignments[] = $row->consignment_id;

				//$status_q = tep_db_query("SELECT *  ROM orders_myparcel WHERE consignment_id IN (" . $visible_consignments . ") AND tnt_final = 0 AND tnt_updated_on < '" . date('Y-m-d H:i:s', time() - 43200) . "'");

				/*while($consignment = tep_db_fetch_array($status_q))
                {
                    $consignments[] = $consignment['consignment_id'];
                }*/
			}
		} else {
			$consignments = $all_consignments;
		}

        if(!empty($consignments))
        {
			error_reporting(0);
			foreach ($consignments as $consignment) {
				$status_file = file(MYPARCEL_LINK . 'status/tnt/' . $consignment);

				if ($status_file) {
					$row = $status_file[0];
					$row = explode('|', $row);
					if(count($row) != 3) exit;

					$qupdate = "UPDATE orders_myparcel SET tnt_status='".trim($row[2])."', tnt_updated_on='".date('Y-m-d H:i:s')."', tnt_final='".(int) $row[1]."' WHERE consignment_id = '" . $row[0] . "'";
					$db->setQuery( $qupdate );
					$db->query();

					/* tep_db_perform('orders_myparcel', array(
						 'tnt_status'     => trim($row[2]),
						 'tnt_updated_on' => date('Y-m-d H:i:s'),
						 'tnt_final'      => (int) $row[1],
					 ), 'update', "consignment_id = '" . $row[0] . "'");
					*/
				}
			}
        }
    }

    /*
     *   PLUGIN POPUP CREATE / RETOUR
     */

    if($_GET['action'] == 'post' && is_numeric($_GET['order_id']))
    {
        //include(DIR_WS_CLASSES . 'order.php');
        // determine retour or normal consignment
        if(isset($_GET['retour']) && $_GET['retour'] == 'true')
        {
            $myparcel_plugin_action = 'verzending-aanmaken-retour/';
            $myparcel_action = 'retour';
        }
        else
        {
            $myparcel_plugin_action = 'verzending-aanmaken/';
            $myparcel_action = 'return';
        }

        $return_url = getReturnUrlWithUri('action=' . $myparcel_action . '&order_id=' . $_GET['order_id'] . '&timestamp=' . $_GET['timestamp']);
        $order = getOrderz($_GET['order_id']);
        //echo "aaa";
        //print_r($order['details']['BT']->virtuemart_country_id);
        //die;
        //$address = $order->delivery;
	
	
	if (isset($order['details']['ST']) && strlen($order['details']['ST']->virtuemart_country_id) > 0) {
		  $gk_virtuemart_country_id = $order['details']['ST']->virtuemart_country_id; 
	 } else {
		 $gk_virtuemart_country_id = $order['details']['BT']->virtuemart_country_id; 
	 }
	
	$db     = JFactory::getDBO();
		$query = "SELECT country_2_code AS country_code FROM #__virtuemart_countries WHERE virtuemart_country_id='".$gk_virtuemart_country_id."' LIMIT 1";
		//echo ($query);
		$db->setQuery( $query );
		//print_r($db->loadObjectlist());die;
		$vendors = $db->loadObjectlist();
		$musu_country_code='';
		for ($i=0, $n=count( $vendors ); $i < $n; $i++) 
		{
			$row = &$vendors[$i];
			//print_r($row);die;
			$musu_country_code=$row->country_code;
		}

        /*$country_sql = tep_db_query("
SELECT countries_iso_code_2 AS country_code
  FROM " . TABLE_COUNTRIES . "
 WHERE countries_name = '" . $address['country'] . "'
");
        $country = tep_db_fetch_array($country_sql);*/

if (isset($order['details']['ST']) &&  strlen($order['details']['ST']->company) > 0) {
	$gkcompany = $order['details']['ST']->company; 
} else {
	$gkcompany = $order['details']['BT']->company; 
}

if (isset($order['details']['ST']) && strlen($order['details']['ST']->zip) > 0) {
	$gkzip = $order['details']['ST']->zip; 
} else {
	$gkzip = $order['details']['BT']->zip; 
}

if (isset($order['details']['ST']) && strlen($order['details']['ST']->city) > 0) {
	$gkcity = $order['details']['ST']->city; 
} else {
	$gkcity = $order['details']['BT']->city; 
}

if (isset($order['details']['ST']) && strlen($order['details']['ST']->email) > 0) {
	$gkemail = $order['details']['ST']->email; 
} else {
	$gkemail = $order['details']['BT']->email; 
}

if (isset($order['details']['ST']) && strlen($order['details']['ST']->first_name) > 0) {
	$gkfirstname = $order['details']['ST']->first_name; 
} else {
	$gkfirstname = $order['details']['BT']->first_name; 
}
if (isset($order['details']['ST']) && strlen($order['details']['ST']->last_name) > 0) {
	$gklastname = $order['details']['ST']->last_name; 
} else {
	$gklastname = $order['details']['BT']->last_name; 
}
if (isset($order['details']['ST']) && strlen($order['details']['ST']->address_1) > 0) {
	$gkaddr = $order['details']['ST']->address_1; 
} else {
	$gkaddr = $order['details']['BT']->address_1; 
}
if (isset($order['details']['ST']) && strlen($order['details']['ST']->phone_1) > 0) {
	$gkphone = $order['details']['ST']->phone_1; 
} else {
	$gkphone = $order['details']['BT']->phone_1; 
}

// Changes for version 1.0.5

if (isset($order['details']['ST']) && strlen($order['details']['ST']->middle_name) > 0) {
	$gkmiddlename = $order['details']['ST']->middle_name;
} else {
	$gkmiddlename = $order['details']['BT']->middle_name;
}
// And the line below: 'ToAddress[name]'            => $gkfirstname. ($gkmiddlename ? " " . $gkmiddlename . " " : " ") .$gklastname,
// --------------------1.0.5


//$gkadresas_num = preg_replace('/\D/', '', $gkaddr);

//$gkadresas_street = preg_replace('/[^A-Z a-z]/', '', $gkaddr);
        if($musu_country_code=='NL')
        {
			// Added on 2016-04-07
			if (isset($order['details']['ST']) && strlen($order['details']['ST']->address_2) > 0) {
				$gkaddr2 = $order['details']['ST']->address_2;
			} else {
				$gkaddr2 = $order['details']['BT']->address_2;
			}
			$gkaddr .= (!empty($gkaddr2) ? ' ' . $gkaddr2 :'');
			// End of 2016-04-07

			/*-------------------Since pg_address----------------*/
			$raw_house_number = '';
			$raw_number_addition = '';
			$raw_street = '';
			
			if (@$order['details']['ST']->Toevoegingen != '' || @$order['details']['ST']->Huisnummer != '') {
				$raw_house_number = @$order['details']['ST']->Huisnummer;
				$raw_number_addition = @$order['details']['ST']->Toevoegingen;
				$raw_street = (@$order['details']['ST']->address_2 != '') ? @$order['details']['ST']->address_2 : @$order['details']['ST']->address_1;
			}

			if (@$order['details']['BT']->Toevoegingen != '' || @$order['details']['BT']->Huisnummer != '') {
				$raw_house_number = @$order['details']['BT']->Huisnummer;
				$raw_number_addition = @$order['details']['BT']->Toevoegingen;
				$raw_street = (@$order['details']['BT']->address_2 != '') ? @$order['details']['BT']->address_2 : @$order['details']['BT']->address_1;
			}
			
			if($raw_house_number == '' && $raw_number_addition == '' && $raw_street == ''){
				$street = getAddressComponents($gkaddr);
				$pg_address = isPgAddress($gkcompany, $street, $gkzip, $gkcity);

				if ($pg_address) {
					$street['number_addition'] = '';
				}

				$raw_house_number = $street['house_number'];
				$raw_number_addition = $street['number_addition'];
				$raw_street = $street['street'];
			}
			/*----------------------------------------pg_address*/

            $consignment = array(
            	'ToAddress[country_code]'    => $musu_country_code,
            	'ToAddress[name]'            => $gkfirstname. ($gkmiddlename ? " " . $gkmiddlename . " " : " ") .$gklastname,
            	'ToAddress[business]'        => $gkcompany,
            	'ToAddress[postcode]'        => $gkzip,
            	'ToAddress[house_number]'    => $raw_house_number,
            	'ToAddress[number_addition]' => $raw_number_addition,
            	'ToAddress[street]'          => $raw_street,
            	'ToAddress[town]'            => $gkcity,
            	'ToAddress[email]'           => $gkemail,
            	'ToAddress[phone_number]' 	 => $gkphone,
				'custom_id' => $order['details']['BT']->order_number,
            );
        }
        else // buitenland
        {
            $weight = 0;
			foreach($order['items'] as $val) {
				//echo $val->product_quantity." ".$val->virtuemart_product_id."<br />";
				$queryz = 'SELECT product_weight FROM #__virtuemart_products where virtuemart_product_id='.$val->virtuemart_product_id;
				//echo $query2."<br /><br />";
				$db->setQuery( $queryz );
				$rezultatas = $db->loadResult();
				$weight += $rezultatas*$val->product_quantity;
			}

			// Changes for version 1.0.6
			if (isset($order['details']['ST']) && strlen($order['details']['ST']->address_2) > 0) {
				$gkaddr2 = $order['details']['ST']->address_2;
			} else {
				$gkaddr2 = $order['details']['BT']->address_2;
			}
			// --------------------1.0.6

            $consignment = array(
            	'ToAddress[country_code]' => $musu_country_code,
            	'ToAddress[name]'         => $gkfirstname. ($gkmiddlename ? " " . $gkmiddlename . " " : " ") .$gklastname,
            	'ToAddress[business]'     => $gkcompany,
            	'ToAddress[street]'       => $gkaddr,
            	'ToAddress[eps_postcode]' => $gkzip,
            	'ToAddress[town]'         => $gkcity,
            	'ToAddress[email]'        => $gkemail,
            	'ToAddress[phone_number]' => $gkphone,
				'ToAddress[extraname]' 	  => $gkaddr2,
            	'weight'                  => $weight,
				'custom_id' => $order['details']['BT']->order_number,
            );
            //print_r($consignment);
            //die;
        }
?>
		<html>
		<body onload="document.getElementById('myparcel-create-consignment').submit();">
            <h4>Sending data to MyParcel ...</h4>
            <form
                action="<?php echo MYPARCEL_LINK . 'plugin/' . $myparcel_plugin_action . $_GET['order_id']; ?>?return_url=<?php echo htmlspecialchars(urlencode($return_url)); ?>"
                method="post"
                id="myparcel-create-consignment"
                style="visibility:hidden;"
                >
<?php
        foreach ($consignment as $param => $value)
        {
            echo '<input type="text" name="' . htmlspecialchars($param) . '" value="' . htmlspecialchars($value) . '" />';
        }
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /*
     *   PLUGIN POPUP RETURN CLOSE
     */
    if($_GET['action'] == 'return' || $_GET['action'] == 'retour')
    {
      $db = JFactory::getDBO();
        $order_id       = $_GET['order_id'];
        $timestamp      = $_GET['timestamp'];
        $consignment_id = $_GET['consignment_id'];
        $retour         = ($_GET['action'] == 'retour') ? 1 : 0;
        $tracktrace     = $_GET['tracktrace'];
        $postcode       = $_GET['postcode'];

        // save
        /*tep_db_perform('orders_myparcel', array(
            'orders_id'      => $order_id,
            'consignment_id' => $consignment_id,
            'retour'         => $retour,
            'tracktrace'     => $tracktrace,
            'postcode'       => $postcode,
        ));*/

        
                $qinsert = "INSERT INTO orders_myparcel SET orders_id='".$order_id."', consignment_id='".$consignment_id."', retour='".$retour."', postcode='".$postcode."', tracktrace = '" . $tracktrace . "'";
		//echo $qinsert; die;
                $db->setQuery( $qinsert );
		$db->query();

        

        $tracktrace_link = 'https://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl&B=' . $tracktrace . '&P=' . $postcode;
?>
		<html>
		<body onload="updateParentWindow();">
            <h4>Consignment <?php echo $consignment_id; ?> aangemaakt [<a href="<?php echo MYPARCEL_LINK; ?>plugin/label/<?php echo $consignment_id; ?>">label bekijken</a>]</h4>
            <h4><a id="close-window" style="display:none;" href="#" onclick="window.close(); return false;">Click here to close this window and return to webshop</a></h4>
            <script type="text/javascript">
                function updateParentWindow()
                {
                    if (!window.opener || !window.opener.MyParcel || !window.opener.MyParcel.virtuemart) {
                        alert('No connection with osCommerce webshop');
                        return;
                    }
                    window.opener.MyParcel.virtuemart.setConsignmentId('<?php echo $order_id; ?>', '<?php echo $timestamp; ?>', '<?php echo $consignment_id; ?>', '<?php echo $tracktrace_link; ?>', '<?php echo $retour; ?>', 'http<?php echo ($_SERVER['HTTPS'] != "on"?'':'s');?>://<?php echo $_SERVER["SERVER_NAME"]; ?>/');
                    document.getElementById('close-window').style.display = 'block';
                }
            </script>
        </body>
        </html>
<?php
        exit;
    }

    /*
     *   PLUGIN POPUP PRINT
     */
    if($_GET['action'] == 'print')
    {
        $consignments = $_GET['consignments'];
?>
		<html>
		<body onload="document.getElementById('myparcel-create-pdf').submit();">
            <h4>Sending data to MyParcel ...</h4>
            <form
                action="<?php echo MYPARCEL_LINK; ?>plugin/genereer-pdf"
                method="post"
                id="myparcel-create-pdf"
                style="visibility:hidden;"
                >
<?php
        echo '<input type="text" name="consignments" value="' . htmlspecialchars($consignments) . '" />';
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /*
     *   PLUGIN BATCH CREATE
     */
    if($_GET['action'] == 'process')
    {
        //include(DIR_WS_CLASSES . 'order.php');

        $return_url = getReturnUrlWithUri('action=batchreturn&timestamp=' . $_GET['timestamp']);

        $order_ids = (strpos($_GET['order_ids'], '|') !== false)
        ? explode('|', $_GET['order_ids'])
        : array($_GET['order_ids']);

        $formParams = array();

        foreach($order_ids as $order_id)
        {
            //$order = new order($order_id);
	    $order = getOrderz($order_id/*$_GET['order_id']*/);//echo $order_id."<br/>";
            /*$address = $order->delivery;

            $country_sql = tep_db_query("
SELECT countries_iso_code_2 AS country_code
  FROM " . TABLE_COUNTRIES . "
 WHERE countries_name = '" . $address['country'] . "'
");
            $country = tep_db_fetch_array($country_sql);*/ 
	    
	    if (isset($order['details']['ST']) && strlen($order['details']['ST']->virtuemart_country_id) > 0) {
		  $gk_virtuemart_country_id = $order['details']['ST']->virtuemart_country_id; 
	 } else {
		 $gk_virtuemart_country_id = $order['details']['BT']->virtuemart_country_id; 
	 }
	
	$db     = JFactory::getDBO();
		$query = "SELECT country_2_code AS country_code FROM #__virtuemart_countries WHERE virtuemart_country_id='".$gk_virtuemart_country_id."' LIMIT 1";
		//echo ($query);
		$db->setQuery( $query );
		//print_r($db->loadObjectlist());die;
		$vendors = $db->loadObjectlist();
		$musu_country_code='';
		for ($i=0, $n=count( $vendors ); $i < $n; $i++) 
		{
			$row = &$vendors[$i];
			//print_r($row);die;
			$musu_country_code=$row->country_code;
		}
		
		
		
	        if (isset($order['details']['ST']) && strlen($order['details']['ST']->company) > 0) {
			$gkcompany = $order['details']['ST']->company; 
		} else {
			$gkcompany = $order['details']['BT']->company; 
		}
		
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->zip) > 0) {
			$gkzip = $order['details']['ST']->zip; 
		} else {
			$gkzip = $order['details']['BT']->zip; 
		}
		
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->city) > 0) {
			$gkcity = $order['details']['ST']->city; 
		} else {
			$gkcity = $order['details']['BT']->city; 
		}
		
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->email) > 0) {
			$gkemail = $order['details']['ST']->email; 
		} else {
			$gkemail = $order['details']['BT']->email; 
		}
		
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->first_name) > 0) {
			$gkfirstname = $order['details']['ST']->first_name; 
		} else {
			$gkfirstname = $order['details']['BT']->first_name; 
		}
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->last_name) > 0) {
			$gklastname = $order['details']['ST']->last_name; 
		} else {
			$gklastname = $order['details']['BT']->last_name; 
		}
		// Changes for version 1.0.5
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->middle_name) > 0) {
			$gkmiddlename = $order['details']['ST']->middle_name;
		} else {
			$gkmiddlename = $order['details']['BT']->middle_name;
		}
		// And the line below: 'ToAddress[name]'            => $gkfirstname. ($gkmiddlename ? " " . $gkmiddlename . " " : " ") .$gklastname,
		// --------------------1.0.5
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->address_1) > 0) {
			$gkaddr = $order['details']['ST']->address_1; 
		} else {
			$gkaddr = $order['details']['BT']->address_1; 
		}
		if (isset($order['details']['ST']) && strlen($order['details']['ST']->phone_1) > 0) {
			$gkphone = $order['details']['ST']->phone_1; 
		} else {
			$gkphone = $order['details']['BT']->phone_1; 
		}

		if($musu_country_code=='NL')
	    {
			// Added on 2016-04-07
			if (isset($order['details']['ST']) && strlen($order['details']['ST']->address_2) > 0) {
				$gkaddr2 = $order['details']['ST']->address_2;
			} else {
				$gkaddr2 = $order['details']['BT']->address_2;
			}
			$gkaddr .= (!empty($gkaddr2) ? ' ' . $gkaddr2 :'');
			// End of 2016-04-07

			/*-------------------Since pg_address----------------*/
			$raw_house_number = '';
			$raw_number_addition = '';
			$raw_street = '';

			if (@$order['details']['ST']->Toevoegingen != '' || @$order['details']['ST']->Huisnummer != '') {
				$raw_house_number = @$order['details']['ST']->Huisnummer;
				$raw_number_addition = @$order['details']['ST']->Toevoegingen;
				$raw_street = (@$order['details']['ST']->address_2 != '') ? @$order['details']['ST']->address_2 : @$order['details']['ST']->address_1;
			}

			if (@$order['details']['BT']->Toevoegingen != '' || @$order['details']['BT']->Huisnummer != '') {
				$raw_house_number = @$order['details']['BT']->Huisnummer;
				$raw_number_addition = @$order['details']['BT']->Toevoegingen;
				$raw_street = (@$order['details']['BT']->address_2 != '') ? @$order['details']['BT']->address_2 : @$order['details']['BT']->address_1;
			}
			
			if($raw_house_number == '' && $raw_number_addition == '' && $raw_street == ''){
				$street = getAddressComponents($gkaddr);
				$pg_address = isPgAddress($gkcompany, $street, $gkzip, $gkcity);
				if ($pg_address) {
					$street['number_addition'] = '';
				}
				
				$raw_house_number = $street['house_number'];
				$raw_number_addition = $street['number_addition'];
				$raw_street = $street['street'];
			}
			/*----------------------------------------pg_address*/

			$consignment = array(
				'ToAddress' => array(
					'country_code'    => $musu_country_code,
					'name'            => $gkfirstname. ($gkmiddlename ? " " . $gkmiddlename . " " : " ") .$gklastname,
					'business'        => $gkcompany,
					'postcode'        => $gkzip,
					'house_number'    => $raw_house_number,
					'number_addition' => $raw_number_addition,
					'street'          => $raw_street,
					'town'            => $gkcity,
					'email'           => $gkemail,
				),
				'custom_id' => $order['details']['BT']->order_number,
			);
		}
            else // buitenland
            {
                $weight = 0;
                /*$product_sql = tep_db_query("
SELECT op.products_quantity, p.products_weight
  FROM " . TABLE_ORDERS_PRODUCTS . " op
  LEFT JOIN " . TABLE_PRODUCTS . " p ON p.products_id = op.products_id
 WHERE orders_id = '" . $order_id . "'
");
                while($product = tep_db_fetch_array($product_sql))
                {
                    $weight += $product['products_quantity'] * $product['products_weight'];
                }*/
		foreach($order['items'] as $val) {
				//echo $val->product_quantity." ".$val->virtuemart_product_id."<br />";
				$queryz = 'SELECT product_weight FROM #__virtuemart_products where virtuemart_product_id='.$val->virtuemart_product_id;
				//echo $query2."<br /><br />";
				$db->setQuery( $queryz );
				$rezultatas = $db->loadResult();
				$weight += $rezultatas*$val->product_quantity;
			}
			    // Changes for version 1.0.6
				if (isset($order['details']['ST']) && strlen($order['details']['ST']->address_2) > 0) {
					$gkaddr2 = $order['details']['ST']->address_2;
				} else {
					$gkaddr2 = $order['details']['BT']->address_2;
				}
				// --------------------1.0.6

                $consignment = array(
                    'ToAddress' => array(
						'country_code' => $musu_country_code,
						'name'         => $gkfirstname. ($gkmiddlename ? " " . $gkmiddlename . " " : " ") .$gklastname,
						'business'     => $gkcompany,
						'street'       => $gkaddr,
						'eps_postcode' => $gkzip,
						'town'         => $gkcity,
						'email'        => $gkemail,
						'phone_number' => $gkphone,
						'extraname' 	=> $gkaddr2,
					),
                    'weight' => $weight,
		    		'custom_id' => $order['details']['BT']->order_number,
                );
            }
            $formParams[$order_id] = serialize($consignment);
        }
?>
		<html>
		<body onload="document.getElementById('myparcel-create-consignmentbatch').submit();">
            <h4>Sending data to MyParcel ...</h4>
            <form
                action="<?php echo MYPARCEL_LINK . 'plugin/verzending-batch'; ?>?return_url=<?php echo htmlspecialchars(urlencode($return_url)); ?>"
                method="post"
                id="myparcel-create-consignmentbatch"
                style="visibility:hidden;"
                >
<?php
        //print_r($formParams);
	foreach ($formParams as $param => $value)
        {
            
	    echo '<input type="text" name="' . htmlspecialchars($param) . '" value="' . htmlspecialchars($value) . '" />';
        }
?>
        	</form>
        </body>
        </html>
<?php
        exit;
    }

    /*
     *   PLUGIN BATCH RETURN CLOSE
     */
    if($_GET['action'] == 'batchreturn')
    {
        //print_r($_POST);
	foreach($_POST as $order_id => $serialized_data)
        {
            //echo "--".$order_id."++<br/>";
	    if(!is_numeric($order_id)) continue;

            //$check_sql = tep_db_query("SELECT orders_id FROM " . TABLE_ORDERS . " WHERE orders_id = '" . tep_db_input($order_id) . "'");

			$query2 = 'SELECT COUNT(virtuemart_order_id) FROM ' . TABLE_ORDERS . ' WHERE virtuemart_order_id = "' . $db->escape($order_id) . '"';
			//echo $query2."<br /><br />";
			$db->setQuery( $query2 );
			$rezultatas = $db->loadResult();
			//echo "---"; print_r($rezultatas); echo "+++";
			//if ($rezultatas == 1) {

            if($rezultatas == 1)
            {
                $data = unserialize($serialized_data);

                // save
                /*tep_db_perform('orders_myparcel', array(
                    'orders_id'      => $order_id,
                    'consignment_id' => $data['consignment_id'],
                    'retour'         => null,
                    'tracktrace'     => $data['tracktrace'],
                    'postcode'       => $data['postcode'],
                ));*/
                
                $qinsert = "INSERT INTO orders_myparcel SET orders_id='".$order_id."', consignment_id='".$data['consignment_id']."', retour='', postcode='".$data['postcode']."', tracktrace = '" . $data['tracktrace'] . "'";
		//echo "<br/><br/>".$qinsert;//die;
                $db->setQuery( $qinsert );
		$db->query();
                
            }
        }
?>
		<html>
		<body onload="updateParentWindow();">
            <h4>Consignments aangemaakt</h4>
            <h4><a id="close-window" style="display:none;" href="#" onclick="window.close(); return false;">Click here to close this window and return to webshop</a></h4>
            <script type="text/javascript">
                function updateParentWindow()
                {
                    if (!window.opener || !window.opener.MyParcel || !window.opener.MyParcel.virtuemart) {
                        alert('No connection with osCommerce webshop');
                        return;
                    }
                    document.getElementById('close-window').style.display = 'block';
                    window.opener.location.reload();
                    window.close();
                }
            </script>
        </body>
        </html>
<?php
        exit;
    }
}
?>
