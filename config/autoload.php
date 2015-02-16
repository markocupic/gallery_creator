<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */

//HowTo: https://community.contao.org/de/showthread.php?39985-Das-nutzen-von-Namespaces&p=258501&viewfull=1#post258501

/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'MCupic',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Classes
	'MCupic\GalleryCreator\GcHelpers'             => 'system/modules/gallery_creator/classes/GcHelpers.php',
	'MCupic\GalleryCreator\DisplayGallery'        => 'system/modules/gallery_creator/classes/DisplayGallery.php',

	// Models
	'MCupic\GalleryCreatorPicturesModel'          => 'system/modules/gallery_creator/models/GalleryCreatorPicturesModel.php',
	'MCupic\GalleryCreatorAlbumsModel'            => 'system/modules/gallery_creator/models/GalleryCreatorAlbumsModel.php',

	// Modules
	'MCupic\GalleryCreator\ModuleDisplayGallery'  => 'system/modules/gallery_creator/modules/ModuleDisplayGallery.php',

	// Elements
	'MCupic\GalleryCreator\ContentDisplayGallery' => 'system/modules/gallery_creator/elements/ContentDisplayGallery.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_gc_default'        => 'system/modules/gallery_creator/templates',
	'be_gc_html5_uploader' => 'system/modules/gallery_creator/templates',
	'be_gc_jumploader'     => 'system/modules/gallery_creator/templates',
));
