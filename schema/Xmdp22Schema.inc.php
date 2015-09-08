<?php

/**
 * @file plugins/metadata/xmdp22/schema/Xmdp22Schema.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Xmdp22Schema
 * @ingroup plugins_metadata_xmdp22_schema
 *
 * @brief OMP-specific implementation of the Xmdp22Schema.
 */

import('lib.pkp.classes.metadata.MetadataSchema');

class Xmdp22Schema extends MetadataSchema {
	/**
	 * Constructor
	 * @param $appSpecificAssocType integer
	 */
	function Xmdp22Schema($appSpecificAssocType = ASSOC_TYPE_PUBLICATION_FORMAT, $classname = 'plugins.metadata.xmdp22.schema.Xmdp22Schema') {
		// Configure the meta-data schema.
		parent::MetadataSchema(
				'xmdp-2.2',
				'xmdp',
				$classname,
				$appSpecificAssocType
		);

		$this->addProperty('dc:title', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:creator', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:subject', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:description', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:publisher', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:contributor', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:date', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:type', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:format', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:identifier', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:source', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:language', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:relation', METADATA_PROPERTY_TYPE_STRING, false, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:coverage', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
#		$this->addProperty('dc:rights', METADATA_PROPERTY_TYPE_STRING, true, METADATA_PROPERTY_CARDINALITY_MANY);
	}
}
?>
