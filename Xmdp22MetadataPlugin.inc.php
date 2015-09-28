<?php

/**
 * @file plugins/metadata/xmdp22/Xmdp22MetadataPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Xmdp22MetadataPlugin
 * @ingroup plugins_metadata_xmdp22
 *
 * @brief XMetaDissPlus 2.2 metadata plugin
 */

import('lib.pkp.classes.plugins.MetadataPlugin');

class Xmdp22MetadataPlugin extends MetadataPlugin {
	/**
	 * Constructor
	 */
	function Xmdp22MetadataPlugin() {
		parent::MetadataPlugin();
	}

	//
	// Override protected template methods from Plugin
	//
	/**
	* @copydoc Plugin::getName()
	*/
	function getName() {
		return 'Xmdp22MetadataPlugin';
	}

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	function getDisplayName() {
		return __('plugins.metadata.xmdp22.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	function getDescription() {
		return __('plugins.metadata.xmdp22.description');
	}
	
	/**
	 * @see Plugin::getTemplatePath($inCore)
	 */
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates/';
	}
	
	/**
	 * @see PKPPlugin::getManagementVerbs()
	 */
	function getManagementVerbs() {
		return array(array('settings', __('manager.plugins.settings')));
	}
	
	/**
	 * Define management link actions for the settings verb.
	 * @return LinkAction
	 */
	function getManagementVerbLinkAction($request, $verb) {
		$router = $request->getRouter();
	
		list($verbName, $verbLocalized) = $verb;
	
		if ($verbName === 'settings') {
			import('lib.pkp.classes.linkAction.request.AjaxLegacyPluginModal');
			$actionRequest = new AjaxLegacyPluginModal(
					$router->url($request, null, null, 'plugin', null, array('verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'metadata')),
					$this->getDisplayName()
			);
			return new LinkAction($verbName, $actionRequest, $verbLocalized, null);
		}
	
		return null;
	}
	
	/**
	 * @see PKPPlugin::manage()
	 */
	function manage($verb, $args, &$message, &$messageParams, &$pluginModalContent = null) {
		$request = $this->getRequest();
		$templateManager = TemplateManager::getManager($request);
		$templateManager->register_function('plugin_url', array(&$this, 'smartyPluginUrl'));
		switch ($verb) {
			case 'settings':
				$press = $request->getPress();
	
				$settingsFormName = $this->getSettingsFormName();
				$settingsFormNameParts = explode('.', $settingsFormName);
				$settingsFormClassName = array_pop($settingsFormNameParts);
				$this->import($settingsFormName);
				$form = new $settingsFormClassName($this, $press->getId());
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						$message = NOTIFICATION_TYPE_SUCCESS;
						$messageParams = array('contents' => __('plugins.metadata.xmdp22.manager.settings.settingsUpdated'));
						return false;
					} else {
						$pluginModalContent = $form->fetch($request);
					}
				} else {
					$form->initData();
					$pluginModalContent = $form->fetch($request);
				}
				return false;
			default:
				// Unknown management verb
				assert(false);
				return false;
		}
	}

	/**
	 * @see PubIdPlugin::getSettingsFormName()
	 */
	function getSettingsFormName() {
		return 'form.Xmdp22SettingsForm';
	}
	
	/**
	 * Access settings.
	 * @param string $fieldName
	 * @param int $pressId
	 */
	function getData($fieldName, $pressId) {
		$fieldName = str_replace(":", "_", $fieldName);
		return $this->getSetting($pressId, $fieldName);
	}
}

?>
