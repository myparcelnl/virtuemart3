<?php defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class MyparcelViewConfiguration extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;
	
	function display($tpl = null)
	{
		// Assign data to the view
		$this->pageTitle = JText::_('Myparcel NL Configurations');
		$this->configs = getMyparcelConfig();
		$this->isSSL = isSSL();
        $this->paperFormat = getPaperFormat();
		$this->addToolbar();
		
		// assets
		$document = JFactory::getDocument();
		JHtml::_('bootstrap.framework');
		$document->addStyleSheet($this->baseurl.'/templates/isis/css/template.css');
		$document->addScript($this->baseurl.'/components/com_virtuemart_myparcelnl/template/js/script.js');

		// Display the view
		return parent::display($tpl);
	}
	
	function addToolbar(){
		JToolbarHelper::title($this->pageTitle, 'cog');
	}
	
}