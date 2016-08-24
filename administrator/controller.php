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

class MyparcelController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JControllerLegacy		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/myparcel.php';

		$view		= JFactory::getApplication()->input->getCmd('view', 'configs');
        JFactory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}
	
	function save(){      
		$manodata11 = JRequest::get( 'POST' );
	       
		$model = & $this->getModel('config');
		$mano_plugin_status=$model->mysave($manodata11);
	       
		$redirectTo = JRoute::_('index.php?option='.JRequest::getVar('option').'&task=display');
		$this->setRedirect($redirectTo, 'Data Saved!'.(($mano_plugin_status)?'':' Disable and enable myParcel to apply changes to frontend.'));               
	}
}
