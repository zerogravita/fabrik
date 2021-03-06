<?php
/**
 * List Article update plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.article
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

/**
 * Add an action button to the list to enable update of content articles
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.article
 * @since       3.0
 */

class PlgFabrik_ListArticle extends PlgFabrik_List
{
	/**
	 * Button prefix
	 *
	 * @var string
	 */
	protected $buttonPrefix = 'article';

	/**
	 * Prep the button if needed
	 *
	 * @param   object  $params  plugin params
	 * @param   object  &$model  list model
	 * @param   array   &$args   arguements
	 *
	 * @return  bool;
	 */

	public function button($params, &$model, &$args)
	{
		parent::button($params, $model, $args);

		return true;
	}

	/**
	 * Get the parameter name that defines the plugins acl access
	 *
	 * @return  string
	 */

	protected function getAclParam()
	{
		return 'access';
	}

	/**
	 * Can the plug-in select list rows
	 *
	 * @return  bool
	 */

	public function canSelectRows()
	{
		return true;
	}

	/**
	 * Do the plug-in action
	 *
	 * @param   object  $params  plugin parameters
	 * @param   object  &$model  list model
	 * @param   array   $opts    custom options
	 *
	 * @return  bool
	 */

	public function process($params, &$model, $opts = array())
	{
		$input = JFactory::getApplication()->input;
		$ids = $input->get('ids', array(), 'array');
		$origRowId = $input->get('rowid');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');

		// Abstract verson of the form article plugin
		$articlePlugin = $pluginManager->getPlugin('article', 'form');

		$formModel = $model->getFormModel();
		$formParams = $formModel->getParams();
		$plugins = $formParams->get('plugins');

		foreach ($plugins as $c => $type)
		{
			if ($type === 'article')
			{
				// Set the abstract article plugin to have the correct parameters
				$pluginParams = $articlePlugin->setParams($formParams, $c);

				// Iterate over the records - load row & update articles
				foreach ($ids as $id)
				{
					$input->set('rowid', $id);
					$formModel->setRowId($id);
					$formModel->_formData = $formModel->getData();
					$articlePlugin->onAfterProcess($pluginParams, $formModel);
				}
			}
		}

		$input->set('rowid', $origRowId);

		return true;
	}

	/**
	 * Get the message generated in process()
	 *
	 * @param   int  $c  plugin render order
	 *
	 * @return  string
	 */

	public function process_result($c)
	{
		$input = JFactory::getApplication()->input;
		$ids = $input->get('ids', array(), 'array');

		return JText::sprintf('PLG_LIST_ARTICLES_UPDATED', count($ids));
	}

	/**
	 * Return the javascript to create an instance of the class defined in formJavascriptClass
	 *
	 * @param   object  $params  plugin parameters
	 * @param   object  $model   list model
	 * @param   array   $args    array [0] => string table's form id to contain plugin
	 *
	 * @return bool
	 */

	public function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		$opts = $this->getElementJSOptions($model);
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListArticle($opts)";

		return true;
	}
}
