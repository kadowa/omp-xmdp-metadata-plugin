<?php

/**
 * @file plugins/metadata/dc11/Dc11MetadataPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Dc11MetadataPlugin
 * @ingroup plugins_metadata_dc11
 *
 * @brief Dublic Core version 1.1 metadata plugin
 */

#import('lib.pkp.plugins.metadata.xmdp22.PKPXmdp22MetadataPlugin');
import('lib.pkp.classes.plugins.MetadataPlugin');

/* class Xmdp22MetadataPlugin extends PKPXmdp22MetadataPlugin {
	function Xmdp22MetadataPlugin() {
		parent::PKPXmdp22MetadataPlugin();
	}
} */

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
