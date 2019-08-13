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

/**
 * Myparcel helper.
 */
class MyparcelHelper
{
	/**
	 * Configure the Linkbar.
	 */
	public static function getView()
	{
		$request = @$_REQUEST;

		if(@$request['option'] == 'com_virtuemart_myparcelnl'){
			return @$request['view'];
		}

		return '';
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_virtuemart_myparcelnl';

		$actions = array(
			/*'core.admin', 'core.manage'*/
		);

		/*foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}*/

		return $result;
	}
}
