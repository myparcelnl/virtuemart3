<?php defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_virtuemart_myparcelnl')) 
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

if (!class_exists('MyparcelHelper')) 
	require_once(JPATH_ROOT .'/administrator/components/com_virtuemart_myparcelnl/helpers/myparcel.php');

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$_controller = MyparcelHelper::getView();
$_controller_file = JPATH_ROOT .'/administrator/components/com_virtuemart_myparcelnl/controllers/'.$_controller.'.php';

if($_controller != '' && file_exists($_controller_file))
	require_once $_controller_file;

$_class = 'MyparcelController'.ucfirst($_controller);

$app = JFactory::getApplication();
$input = JFactory::getApplication()->input;

if(!class_exists($_class)){
	if($_class == 'MyparcelController'){
        $app->redirect('index.php?option=com_virtuemart_myparcelnl&view=configuration');
    }
	vmError('Serious Error could not find controller '.$_class,'Serious error, could not find class');
	$app->enqueueMessage('Fatal Error in maincontroller admin.virtuemart.php: No controller given '.$_controller);
    $app->redirect('index.php?option=com_virtuemart_myparcelnl&view=configuration');
}
$controller = new $_class();
$controller->execute($input->getCmd('task', $_controller));
$controller->redirect();
