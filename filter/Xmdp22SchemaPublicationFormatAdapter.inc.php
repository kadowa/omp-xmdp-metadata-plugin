<?php

/**
 * @file plugins/metadata/dc11/filter/Xmdp22SchemaPublicationFormatAdapter.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Xmdp22SchemaPublicationFormatAdapter
 * @ingroup plugins_metadata_xmdp22_filter
 * @see PublicationFormat
 *
 * @brief Adapter that injects/extracts XMetaDissPlus schema compliant meta-data
 * into/from a PublicationFormat object.
 */


import('lib.pkp.classes.metadata.MetadataDataObjectAdapter');
import('plugins.metadata.xmdp22.schema.Pc14NameSchema');
import('plugins.metadata.xmdp22.schema.CC21InstitutionSchema');

class Xmdp22SchemaPublicationFormatAdapter extends MetadataDataObjectAdapter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function Xmdp22SchemaPublicationFormatAdapter(&$filterGroup) {
		parent::MetadataDataObjectAdapter($filterGroup);
	}


	//
	// Implement template methods from Filter
	//
	/**
	 * @see Filter::getClassName()
	 */
	function getClassName() {
		return 'plugins.metadata.xmdp22.filter.Xmdp22SchemaPublicationFormatAdapter';
	}


	//
	// Implement template methods from MetadataDataObjectAdapter
	//
	/**
	 * @see MetadataDataObjectAdapter::injectMetadataIntoDataObject()
	 * @param $description MetadataDescription
	 * @param $publicationFormat PublicationFormat
	 * @param $authorClassName string the application specific author class name
	 */
	function &injectMetadataIntoDataObject(&$description, &$publicationFormat, $authorClassName) {
		// Not implemented
		assert(false);
	}

	/**
	 * @see MetadataDataObjectAdapter::extractMetadataFromDataObject()
	 * @param $publicationFormat PublicationFormat
	 * @return MetadataDescription
	 */
	function extractMetadataFromDataObject($publicationFormat) {
		assert(is_a($publicationFormat, 'PublicationFormat'));

		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);

		// Retrieve data that belongs to the publication format.
		$oaiDao = DAORegistry::getDAO('OAIDAO'); /* @var $oaiDao OAIDAO */
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$monograph = $publishedMonographDao->getById($publicationFormat->getMonographId());
		$chapters = $chapterDao->getChapters($monograph->getId());
		$series = $oaiDao->getSeries($monograph->getSeriesId()); /* @var $series Series */
		$press = $oaiDao->getPress($monograph->getPressId());
		
		$description = $this->instantiateMetadataDescription();

		// Title
		$this->_addLocalizedElements($description, 'dc:title[@xsi:type="ddb:titleISO639-2"]', $monograph->getTitle(null));
		
		// Creator
		
 		$authors = $monograph->getAuthors();
 		foreach($authors as $author) {
			
			$pc = new MetadataDescription('plugins.metadata.xmdp22.schema.Pc14NameSchema', ASSOC_TYPE_AUTHOR);
			
 			$this->_checkForContentAndAddElement($pc, 'pc:person/pc:name[@xsi:type="nameUsedByThePerson"]/pc:foreName', $author->getFirstName());
 			$this->_checkForContentAndAddElement($pc, 'pc:person/pc:name[@xsi:type="nameUsedByThePerson"]/pc:surName', $author->getLastName());

 			$this->_checkForContentAndAddElement($description, 'dc:creator[@xsi:type="pc:MetaPers"]', $pc);
 		}
 		
		// Subject
		$subjects = array_merge_recursive(
				(array) $monograph->getDiscipline(null),
				(array) $monograph->getSubject(null),
				(array) $monograph->getSubjectClass(null));
		$this->_addLocalizedElements($description, 'dc:subject[@xsi:type="xMetaDiss:noScheme"]', $subjects);
		
		// Table of Contents
		
		// Abstract
		$this->_addLocalizedElements($description, 'dcterms:abstract[@xsi:type="ddb:contentISO639-2"]', $monograph->getAbstract(null));
		
		// Publisher
		$publisherInstitution = $press->getSetting('publisherInstitution');
		if (!empty($publisherInstitution)) {
			$publishers = array($press->getPrimaryLocale() => $publisherInstitution);
		} else {
			$publishers = $press->getName(null); // Default
		}
		
		// FIXME: Press name as fallback
		// FIXME: Where to get the place?
		$cc = new MetadataDescription('plugins.metadata.xmdp22.schema.CC21InstitutionSchema', ASSOC_TYPE_PRESS);
		$this->_checkForContentAndAddElement($cc, 'cc:universityOrInstitution/cc:name', $press->getName());
		$this->_checkForContentAndAddElement($cc, 'cc:universityOrInstitution/cc:place', $press->getData("address"));
		$this->_checkForContentAndAddElement($cc, 'cc:address', $press->getData("address"));
		
		$this->_checkForContentAndAddElement($description, 'dc:publisher[@xsi:type="cc:Publisher"]', $cc);
		
		// Contributor
		$contributors = $monograph->getSponsor(null);
		if (is_array($contributors)) {
			foreach ($contributors as $locale => $contributor) {
				$contributors[$locale] = array_map('trim', explode(';', $contributor));
			}
			$this->_addLocalizedElements($description, 'dc:contributor', $contributors);
		}
		
		// Date submitted
		//$description->addStatement('dcterms:dateSubmitted', date('Y', strtotime($monograph->getDateSubmitted())));
		
		// Issued
		$this->_checkForContentAndAddElement($description, 'dcterms:issued[@xsi:type="dcterms:W3CDTF"]', date('Y-m-d', strtotime($monograph->getDatePublished())));
		
		// Type
		$types = array_merge_recursive(
				array_map('lcfirst', array(AppLocale::getLocale() => __('rt.metadata.pkp.dctype'))),
				array_map('lcfirst', (array) $monograph->getType(null))
		);
		$this->_addLocalizedElements($description, 'dc:type[@xsi:type="dini:PublType"]', $types);
		
		// Format
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$entryKeys = $onixCodelistItemDao->getCodes('List7'); // List7 is for object formats
		if ($publicationFormat->getEntryKey()) {
			$formatName = $entryKeys[$publicationFormat->getEntryKey()];
			$this->_checkForContentAndAddElement($description, 'dc:format', $formatName);
		}
		
		// Identifier: DOI
		$pubIdPlugins = PluginRegistry::loadCategory('pubIds', true);
		if ( array_key_exists('DOIPubIdPlugin', $pubIdPlugins) ) {
			$doi = $pubIdPlugins['DOIPubIdPlugin']->getPubId($publicationFormat);
		//	if (is_a($monograph, 'PublishedMonograph')) {
			$description->addStatement('dc:identifier[@xsi:type="doi"]', $doi);
		//	}
		}
		
		// Source (press title and pages)
		$sources = $press->getName(null);
		$pages = $monograph->getPages();
		if (!empty($pages)) $pages = '; ' . $pages;
		foreach ($sources as $locale => $source) {
			$sources[$locale] .= '; ';
			$sources[$locale] .=  $pages;
		}
		$this->_addLocalizedElements($description, 'dc:source', $sources);
		
		// Language
		// TODO: make language obligatory
		$language = $monograph->getLanguage();
		if (!$language) {
			$language = AppLocale::get3LetterFrom2LetterIsoLanguage(substr($press->getPrimaryLocale(), 0, 2));
		} else {
			$language = AppLocale::get3LetterFrom2LetterIsoLanguage($language);
		}
		$this->_checkForContentAndAddElement($description, 'dc:language[@xsi:type="dcterms:ISO639-2"]', $language);
		
		// Relation
		
		// Coverage
		$coverage = array_merge_recursive(
				(array) $monograph->getCoverageGeo(null),
				(array) $monograph->getCoverageChron(null),
				(array) $monograph->getCoverageSample(null));
		$this->_addLocalizedElements($description, 'dc:coverage[@xsi:type="ddb:encoding" @ddb:Scheme="None"]', $coverage);
		
		// Rights
		$salesRightsFactory = $publicationFormat->getSalesRights();
		while ($salesRight = $salesRightsFactory->next()) {
			$this->_checkForContentAndAddElement($description, 'dc:rights', $salesRight->getNameForONIXCode());
		}
		
 		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$availableFiles = array_filter(
				$submissionFileDao->getLatestRevisions($monograph->getId()),
				create_function('$a', 'return $a->getViewable() && $a->getDirectSalesPrice() !== null && $a->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT;')
		);

		// File stuff
		// TODO: container / select full document
		$files = array();
		foreach ($availableFiles as $availableFile) {
			if ($availableFile->getAssocId() == $publicationFormat->getId()) {
				// Collect all files that belong to this publication format
				$files[] = $availableFile;
			};
		};
		
		// FIXME: use first file as default for prototype
		if ( $files ) {
			$transferFile = $files[0];
			$files = array( $transferFile );
		}
		
		// Number of files in container
		$this->_checkForContentAndAddElement($description, 'ddb:fileNumber', sizeof($files));
		
		// File Properties
		// FIXME: make attribute configurable, add data:
		// * ddb:fileName = $availableFile->getServerFileName()
		// * ddb:fileSize = $availableFile->getFileSize()
		foreach ($files as $file) {
			$description->addStatement('ddb:fileProperties', '');
		};
		
		// Transfer
		if ( isset($transferFile) ) {
			$this->_checkForContentAndAddElement($description, 'ddb:transfer[@ddb:type="dcterms:URI"]', Request::url($press->getPath(),
					'catalog', 'download', array($monograph->getId(), $publicationFormat->getId(), $transferFile->getFileIdAndRevision())));
		}
		
		// Contact ID
		// TODO: make configurable via settings
		$description->addStatement('ddb:contact[@ddb:contactID="F6000-0201"]', '');
		
		// Additional identifiers
		$this->_checkForContentAndAddElement($description, 'ddb:identifier[@ddb:type="URL"]', Request::url($press->getPath(), 'catalog', 'book', array($monograph->getId())));
		
		// Rights
		// TODO: make configurable via settings
		$description->addStatement('ddb:rights[@ddb:kind="free"]', '');
		
		Hookregistry::call('Xmdp22SchemaPublicationFormatAdapter::extractMetadataFromDataObject', array(&$this, $monograph, $press, &$description));

		return $description;
	}

	/**
	 * @see MetadataDataObjectAdapter::getDataObjectMetadataFieldNames()
	 * @param $translated boolean
	 */
	function getDataObjectMetadataFieldNames($translated = true) {
		return array();
	}


	//
	// Private helper methods
	//
	/**
	 * Add an array of localized values to the given description.
	 * @param $description MetadataDescription
	 * @param $propertyName string
	 * @param $localizedValues array
	 */
	function _addLocalizedElements(&$description, $propertyName, $localizedValues) {
		foreach(stripAssocArray((array) $localizedValues) as $locale => $values) {
			if (is_scalar($values)) $values = array($values);
			foreach($values as $value) {
					if ($value) {
						$description->addStatement($propertyName, $value, $locale);
					}
				unset($value);
			}
		}
	}
	
	function _checkForContentAndAddElement(&$description, $propertyName, $value) {
		if ($value) {
			$description->addStatement($propertyName, $value);
		}
	}
}


if(false === function_exists('lcfirst'))
{
	/**
	 * Make a string's first character lowercase
	 *
	 * @param string $str
	 * @return string the resulting string.
	 */
	function lcfirst( $str ) {
		$str[0] = strtolower($str[0]);
		return (string)$str;
	}
}

?>
