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
}

?>
