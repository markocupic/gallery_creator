<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
 *
 * @package Gallery_creator
 * @link    http://www.contao.org
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
ClassLoader::addClasses(array(
       // Helpers
       'GalleryCreator\GcHelpers'                       => 'system/modules/gallery_creator/classes/GcHelpers.php',
       'GalleryCreator\DisplayGallery'                  => 'system/modules/gallery_creator/classes/DisplayGallery.php',

       // Modules
       'GalleryCreator\ContentDisplayGallery'           => 'system/modules/gallery_creator/modules/ContentDisplayGallery.php',
       'GalleryCreator\ModuleDisplayGallery'            => 'system/modules/gallery_creator/modules/ModuleDisplayGallery.php',

       //Models
       'GalleryCreator\GalleryCreatorAlbumsModel'       => 'system/modules/gallery_creator/models/GalleryCreatorAlbumsModel.php',
       'GalleryCreator\GalleryCreatorPicturesModel'     => 'system/modules/gallery_creator/models/GalleryCreatorPicturesModel.php'
));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array(
       'be_gc_jumploader'          => 'system/modules/gallery_creator/templates',
       'ce_gc_default'             => 'system/modules/gallery_creator/templates',
       'ce_gc_jquery_galleria'     => 'system/modules/gallery_creator/templates',
       'ce_gc_jw_imagerotator'     => 'system/modules/gallery_creator/templates',
       'ce_gc_lightbox'            => 'system/modules/gallery_creator/templates'
));		
