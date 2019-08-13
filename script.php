<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

class com_virtuemart_myparcelnlInstallerScript
{
    public function install($parent) {
		$this->install_sql();
		$this->replace_files();
    }
	
	public function update($parent) {
		$this->install_sql();
		$this->replace_files();
    }
	
	public function uninstall($parent) {
		$this->remove_replace_files();
    }
	
	function install_sql(){
		// db
		$db = JFactory::getDBO();
		$sql_config = "CREATE TABLE IF NOT EXISTS `myparcel_config` (
			`id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
			`updated_at` DATETIME NULL,
			`configs` TEXT NULL ,
			PRIMARY KEY (`id`)
		) DEFAULT COLLATE=utf8_general_ci;";
		$sql_orders = "CREATE TABLE IF NOT EXISTS `myparcel_virtuemart_orders` (
			`orders_myparcel_id` int(11) NOT NULL AUTO_INCREMENT,
			`order_id` int(11) NOT NULL,
			`consignment_id` bigint(20) NOT NULL,
			`postcode` varchar(6) NOT NULL,
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`orders_myparcel_id`),
			UNIQUE KEY `orders_id_consignment_id` (`order_id`,`consignment_id`)
		) DEFAULT COLLATE=utf8_general_ci;";
				
		$db->setQuery($sql_config);
		$db->execute();
			
		$db->setQuery($sql_orders);
		$db->execute();
	}
	
	function remove_replace_files(){
		// orders
		$templates = array(
			'list.php',
			'orders.php',
			'order.php',
		);
		
		foreach($templates as $template_item){
			$file = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'views' . DS . 'orders' . DS . 'tmpl' . DS . $template_item;
			
			if (file_exists($file)) {				
				if (is_writable($file)) {
					$content = file_get_contents($file);
					
					if (strpos($content, '// include myparcel js file') !== false) { // remove old replace if already existed
						$content = preg_replace('/\/\/ include myparcel js file[\s\S]+?AdminUIHelper\:\:startAdminArea/', 'AdminUIHelper::startAdminArea', $content);
						file_put_contents($file, $content);
					}
				}else{
					echo '<p>'.$file.' is not writable!</p>'; 
					return false;
				}
			}
		}
		
		return true;
	}
	
	function replace_files(){
		// list orders
		$this->replace_orders_list();
		
		// detail order
		$this->replace_order_detail();
	}
	
	function replace_orders_list(){
		$templates = array(
			'list.php',
			'orders.php',
		);
		
		foreach($templates as $template_item){
			$file = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'views' . DS . 'orders' . DS . 'tmpl' . DS . $template_item;
			
			if (file_exists($file)) {				
				if (is_writable($file)) {
					$content = file_get_contents($file);
					
					if (strpos($content, '// include myparcel js file') !== false) { // remove old replace if already existed
						$content = preg_replace('/\/\/ include myparcel js file[\s\S]+?AdminUIHelper\:\:startAdminArea/', 'AdminUIHelper::startAdminArea', $content);
					}
					
					$target_str = 'AdminUIHelper::startAdminArea';
					$target_replace = '// include myparcel js file'."\r\n";
					$target_replace .= '$document = JFactory::getDocument();'."\r\n";
					$target_replace .= '$document->addScriptDeclaration(\'var myparcel_base = "\'.JURI::base().\'"\');'."\r\n";
					$target_replace .= '$document->addStyleSheet(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/sweetalert/sweetalert.css\');'."\r\n\r\n";
					$target_replace .= '$document->addScript(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/sweetalert/sweetalert.min.js\');'."\r\n\r\n";
					$target_replace .= '$document->addScript(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/myparcel.js\');'."\r\n\r\n";
					$target_replace .= '$document->addScript(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/myparcel_orders.js\');'."\r\n\r\n";
					$target_replace .= 'AdminUIHelper::startAdminArea';
					
					$content = str_replace($target_str, $target_replace, $content);
					
					file_put_contents($file, $content);
				}else{
					echo '<p>'.$file.' is not writable!</p>'; 
					return false;
				}
			}
		}
		
		return true;
	}
	
	function replace_order_detail(){
		$templates = array(
			'order.php',
		);
		
		foreach($templates as $template_item){
			$file = JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'views' . DS . 'orders' . DS . 'tmpl' . DS . $template_item;
			
			if (file_exists($file)) {				
				if (is_writable($file)) {
					$content = file_get_contents($file);
					
					if (strpos($content, '// include myparcel js file') !== false) { // remove old replace if already existed
						$content = preg_replace('/\/\/ include myparcel js file[\s\S]+?AdminUIHelper\:\:startAdminArea/', 'AdminUIHelper::startAdminArea', $content);
					}
					
					$target_str = 'AdminUIHelper::startAdminArea';
					$target_replace = '// include myparcel js file'."\r\n";
					$target_replace .= '$document = JFactory::getDocument();'."\r\n";
					$target_replace .= '$document->addScriptDeclaration(\'var myparcel_base = "\'.JURI::base().\'"\');'."\r\n";
					$target_replace .= '$document->addStyleSheet(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/sweetalert/sweetalert.css\');'."\r\n\r\n";
					$target_replace .= '$document->addScript(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/sweetalert/sweetalert.min.js\');'."\r\n\r\n";
					$target_replace .= '$document->addScript(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/myparcel.js\');'."\r\n\r\n";
					$target_replace .= '$document->addScript(JURI::base().\'components/com_virtuemart_myparcelnl/template/js/myparcel_order_item.js\');'."\r\n\r\n";
					$target_replace .= 'AdminUIHelper::startAdminArea';
					
					$content = str_replace($target_str, $target_replace, $content);
					
					file_put_contents($file, $content);
				}else{
					echo '<p>'.$file.' is not writable!</p>'; 
					return false;
				}
			}
		}
		
		return true;
	}
}