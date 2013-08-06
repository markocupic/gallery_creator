<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2013 Leo Feyer
 *
 * @package Gallery_creator
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'GalleryCreator',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Elements
	'GalleryCreator\ContentDisplayGallery'       => 'system/modules/gallery_creator/elements/ContentDisplayGallery.php',

	// Classes
	'GalleryCreator\DisplayGallery'              => 'system/modules/gallery_creator/classes/DisplayGallery.php',
	'GalleryCreator\GcHelpers'                   => 'system/modules/gallery_creator/classes/GcHelpers.php',

	// Models
	'GalleryCreator\GalleryCreatorAlbumsModel'   => 'system/modules/gallery_creator/models/GalleryCreatorAlbumsModel.php',
	'GalleryCreator\GalleryCreatorPicturesModel' => 'system/modules/gallery_creator/models/GalleryCreatorPicturesModel.php',

	// Modules
	'GalleryCreator\ModuleDisplayGallery'        => 'system/modules/gallery_creator/modules/ModuleDisplayGallery.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'ce_gc_default'    => 'system/modules/gallery_creator/templates',
	'be_gc_jumploader' => 'system/modules/gallery_creator/templates',
));
