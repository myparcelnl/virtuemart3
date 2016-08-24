<?php
/**
 * @version     1.0.0
 * @package     com_myparcel
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Balticode <giedrius@balticode.com> - www.balticode.com
 */


// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHTML::_('script','system/multiselect.js',false,true);
// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_myparcel/assets/css/myparcel.css');

$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_myparcel');
$saveOrder	= $listOrder == 'a.ordering';
?>

<form action="<?php echo JRoute::_('index.php?option=com_myparcel&view=configs'); ?>" method="post" name="adminForm" id="adminForm">

	<table class="adminlist">

		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_myparcel');
			$canEdit	= $user->authorise('core.edit',			'com_myparcel');
			$canCheckin	= $user->authorise('core.manage',		'com_myparcel');
			$canChange	= $user->authorise('core.edit.state',	'com_myparcel');
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center" width="50" style="display: none;">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
                <?php if (isset($this->items[0]->state)) { ?>
				    <td class="center" width="50">
					    <?php echo JHtml::_('jgrid.published', $item->state, $i, 'configs.', $canChange, 'cb'); ?>
				    </td>
                <?php } ?>
				<td>
					myParcel
				</td>
				<td>
					Username: <input type="text" name="username11" value="<?php echo $item->my_name; ?>" />
				</td>
				<td>
					API key: <input size=40 type="text" name="api_key11" value="<?php echo $item->my_api_key; ?>" />
				</td>
				<td>
					Frontend plugin: <?php echo ($item->my_frontend_plugin)?'<input type="checkbox" name="frontend_plugin11" checked="checked" value="1" />':'<input type="checkbox" name="frontend_plugin11" value="1" />'; ?>
				</td>
    
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>