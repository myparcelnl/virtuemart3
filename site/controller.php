<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controller library
jimport('joomla.application.component.controller');

/**
 * Myparcel Component Controller
 */
class MyparcelController extends JControllerLegacy
{
    function save_pg_address()
    {
        $action = isset($_GET['task']) ? $_GET['task'] : '';
        $db =& JFactory::getDBO();

        if ($action == 'save_pg_address') {

            $check_if_table = $db->setQuery("SHOW TABLES LIKE 'orders_myparcel_pg_address'");
            $check_if_table->execute();

            if ($check_if_table->getNumRows() == 0)
            {
                $check_if_table->setQuery("
                CREATE TABLE IF NOT EXISTS `orders_myparcel_pg_address` (
							`pg_address_id` int(11) NOT NULL AUTO_INCREMENT,
							`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							`street` varchar(255) COLLATE utf8_unicode_ci NULL,
							`house_number` varchar(255) COLLATE utf8_unicode_ci NULL,
							`number_addition` varchar(255) COLLATE utf8_unicode_ci NULL,
							`postcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
							`town` varchar(255) COLLATE utf8_unicode_ci NULL,
							PRIMARY KEY (`pg_address_id`));
                ");
                $check_if_table->execute();

            }
            /*if (version_compare(phpversion(), '5.4.0', '<')) {
                if (session_id() == '') session_start();
            } else {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }
            }*/

            $company_name = $_GET['pg_extra_name'];
            $street = $_GET['pg_extra_street'];
            $house_number = $_GET['pg_extra_house_number'];
            $number_addition = $_GET['pg_extra_number_addition'];
            $postcode = $_GET['pg_extra_postcode'];
            $town = $_GET['pg_extra_town'];

            $pg_address_query = $db->setQuery(
                sprintf("
					SELECT * FROM `orders_myparcel_pg_address` WHERE (`name` = '%s' AND `postcode`='%s')
				",
                    $company_name,
                    $postcode
                )
            );
            $pg_address_query->execute();

            if ($pg_address_query->getNumRows() == 0) {
                $pg_address_query = $db->setQuery(
                    sprintf("
					INSERT INTO `orders_myparcel_pg_address`
					(`name`, `street`, `house_number`, `number_addition`, `postcode`, `town`)
					VALUES ('%s', '%s', '%s', '%s', '%s', '%s');
				",
                        $company_name,
                        $street,
                        $house_number,
                        $number_addition,
                        $postcode,
                        $town
                    )
                );
                $pg_address_query->execute();
            }
        }
    }
}