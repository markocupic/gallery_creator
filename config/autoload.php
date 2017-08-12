<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @license LGPL-3.0+
 */


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
	// Src
	'MCupic\GalleryCreator\GcHelpers'                 => 'system/modules/gallery_creator/src/classes/GcHelpers.php',
	'MCupic\GalleryCreator\GalleryCreator'            => 'system/modules/gallery_creator/src/classes/GalleryCreator.php',
	'MCupic\GalleryCreatorPicturesModel'              => 'system/modules/gallery_creator/src/models/GalleryCreatorPicturesModel.php',
	'MCupic\GalleryCreatorAlbumsModel'                => 'system/modules/gallery_creator/src/models/GalleryCreatorAlbumsModel.php',
	'MCupic\GalleryCreator\ModuleGalleryCreator'      => 'system/modules/gallery_creator/src/modules/ModuleGalleryCreator.php',
	'MCupic\GalleryCreator\ContentGalleryCreatorNews' => 'system/modules/gallery_creator/src/elements/ContentGalleryCreatorNews.php',
	'MCupic\GalleryCreator\ContentGalleryCreator'     => 'system/modules/gallery_creator/src/elements/ContentGalleryCreator.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_gc_default'        => 'system/modules/gallery_creator/templates',
	'be_gc_html5_uploader' => 'system/modules/gallery_creator/templates',
	'be_gc_jumploader'     => 'system/modules/gallery_creator/templates',
	'ce_gc_news_default'   => 'system/modules/gallery_creator/templates',
));
