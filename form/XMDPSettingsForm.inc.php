<?php

/**
 * @file plugins/metadata/xmdp/form/XMDPSettingsForm.inc.php
 *
 * Copyright (c) 2015 Heidelberg University
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XMDPSettingsForm
 * @ingroup plugins_metadata_xmdp22
 *
 * @brief Form for press managers to setup DOI plugin
 */


import('lib.pkp.classes.form.Form');

class XMDPSettingsForm extends Form {

	//
	// Private properties
	//
	/** @var integer */
	var $_pressId;

	/**
	 * Get the press ID.
	 * @return integer
	 */
	function _getPressId() {
		return $this->_pressId;
	}

	/** @var Xmdp22MetadataPlugin */
	var $_plugin;

	/**
	 * Get the plugin.
	 * @return Xmdp22MetadataPlugin
	 */
	function &_getPlugin() {
		return $this->_plugin;
	}


	//
	// Constructor
	//
	/**
	 * Constructor
	 * @param $plugin DOIPubIdPlugin
	 * @param $pressId integer
	 */
	function XMDPSettingsForm(&$plugin, $pressId) {
		$this->_pressId = $pressId;
		$this->_plugin =& $plugin;
		
		parent::Form($plugin->getTemplatePath() . 'settingsForm.tpl');

		$this->addCheck(new FormValidator($this, 'cc_place' ,'required', 'plugins.metadata.xmdp22.manager.settings.cc.place.required'));
		$this->addCheck(new FormValidator($this, 'cc_address' ,'required', 'plugins.metadata.xmdp22.manager.settings.cc.address.required'));
		$this->addCheck(new FormValidatorRegExp($this, 'ddb_contactID', 'optional', 'plugins.metadata.xmdp22.manager.settings.ddb.contactID.pattern', '/^F[0-9][0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]$/'));
		$this->addCheck(new FormValidator($this, 'ddb_kind' ,'required', 'plugins.metadata.xmdp22.manager.settings.ddb.kind.required'));

		$this->addCheck(new FormValidatorPost($this));

		$this->setData('pluginName', $plugin->getName());
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData() {
		$pressId = $this->_getPressId();
		$plugin =& $this->_getPlugin();
		
		$this->setData("ddbKindOptions", array(
				"free" => "free", 
				"domain" => "domain", 
				"blocked" => "blocked", 
				"unkown" => "unknown"
				)
		);
		
		foreach($this->_getFormFields() as $fieldName => $fieldType) {
			$this->setData($fieldName, $plugin->getSetting($pressId, $fieldName));
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array_keys($this->_getFormFields()));
	}

	/**
	 * @see Form::execute()
	 */
	function execute() {
		$plugin =& $this->_getPlugin();
		$pressId = $this->_getPressId();
		foreach($this->_getFormFields() as $fieldName => $fieldType) {
			$plugin->updateSetting($pressId, $fieldName, $this->getData($fieldName), $fieldType);
		}
	}


	//
	// Private helper methods
	//
	function _getFormFields() {
		return array(
			'cc_place' => 'string',
			'cc_address' => 'string',
			'ddb_contactID' => 'string',
			'ddb_kind' => 'string',
		);
	}
}

?>
