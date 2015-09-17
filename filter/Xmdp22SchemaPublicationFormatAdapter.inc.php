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
		
		import('plugins.metadata.xmdp22.schema.Pc14NameSchema');
		$pc = new MetadataDescription('plugins.metadata.xmdp22.schema.Pc14NameSchema', ASSOC_TYPE_AUTHOR);
		
		// Title
		$this->_addLocalizedElements($description, 'dc:title', $monograph->getTitle(null));
		
		// Creator
		
 		$authors = $monograph->getAuthors();
 		foreach($authors as $author) {
 			$authorName = $author->getFullName(true);
// 			$affiliation = $author->getLocalizedAffiliation();
// 			if (!empty($affiliation)) {
// 				$authorName .= '; ' . $affiliation;
// 			}
 			$pc->addStatement('pc:person/pc:name[@type="nameUsedByThePerson"]/pc:foreName', $author->getFirstName());
 			$pc->addStatement('pc:person/pc:name[@type="nameUsedByThePerson"]/pc:surName', $author->getLastName());
 			//$description->addStatement('dc:creator', $pc);
 			$this->_addElementsWrapper($description, 'dc:creator', $pc);
 		}
		
		// Subject
		$subjects = array_merge_recursive(
				(array) $monograph->getDiscipline(null),
				(array) $monograph->getSubject(null),
				(array) $monograph->getSubjectClass(null));
		$this->_addLocalizedElements($description, 'dc:subject', $subjects);
		
		// Table of Contents
		
		// Abstract
		$this->_addLocalizedElements($description, 'dcterms:abstract', $monograph->getAbstract(null));
		
		// Publisher
		$publisherInstitution = $press->getSetting('publisherInstitution');
		if (!empty($publisherInstitution)) {
			$publishers = array($press->getPrimaryLocale() => $publisherInstitution);
		} else {
			$publishers = $press->getName(null); // Default
		}
		$this->_addLocalizedElements($description, 'dc:publisher', $publishers);
		
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
		$this->_addElementsWrapper($description, 'dcterms:issued', date('Y', strtotime($monograph->getDatePublished())));
		
		// Type
		$types = array_merge_recursive(
				array(AppLocale::getLocale() => __('rt.metadata.pkp.dctype')),
				(array) $monograph->getType(null)
		);
		$this->_addLocalizedElements($description, 'dc:type', $types);
		
		// Format
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$entryKeys = $onixCodelistItemDao->getCodes('List7'); // List7 is for object formats
		if ($publicationFormat->getEntryKey()) {
			$formatName = $entryKeys[$publicationFormat->getEntryKey()];
			$this->_addElementsWrapper($description, 'dc:format', $formatName);
		}
		
		// Identifier: URL
		if (is_a($monograph, 'PublishedMonograph')) {
			$this->_addElementsWrapper($description, 'dc:identifier', Request::url($press->getPath(), 'catalog', 'book', array($monograph->getId())));
		}
		
		// Identifier: others
		$identificationCodeFactory = $publicationFormat->getIdentificationCodes();
		while ($identificationCode = $identificationCodeFactory->next()) {
			$this->_addElementsWrapper($description, 'dc:identifier', $identificationCode->getNameForONIXCode());
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
		
		// Relation
		
		// Coverage
		$coverage = array_merge_recursive(
				(array) $monograph->getCoverageGeo(null),
				(array) $monograph->getCoverageChron(null),
				(array) $monograph->getCoverageSample(null));
		$this->_addLocalizedElements($description, 'dc:coverage', $coverage);
		
		// Rights
		$salesRightsFactory = $publicationFormat->getSalesRights();
		while ($salesRight = $salesRightsFactory->next()) {
			$this->_addElementsWrapper($description, 'dc:rights', $salesRight->getNameForONIXCode());
		}
		
 		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$availableFiles = array_filter(
				$submissionFileDao->getLatestRevisions($monograph->getId()),
				create_function('$a', 'return $a->getViewable() && $a->getDirectSalesPrice() !== null && $a->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT;')
		);

		$fileCounter = 0;
		foreach ($availableFiles as $availableFile) {
			if ($availableFile->getAssocId() == $publicationFormat->getId()) {
				// File number
				$fileCounter++;
				$this->_addElementsWrapper($description, 'ddb:fileNumber', $fileCounter);
				
				// File Properties
				// $availableFile->getServerFileName()
				// $availableFile->getNiceFileSize()
				
				// Transfer
				$this->_addElementsWrapper($description, 'ddb:transfer', Request::url($press->getPath(), 
						'catalog', 'download', array($monograph->getId(), $publicationFormat->getId(), $availableFile->getFileIdAndRevision())));
				break; // use first file as default for prototype
			}
		};
		
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
	
	function _addElementsWrapper(&$description, $propertyName, $value) {
		if ($value) {
			$description->addStatement($propertyName, $value);
		}
	}
}
?>
