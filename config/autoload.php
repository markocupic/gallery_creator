<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
    'Markocupic',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // Modules
    'Markocupic\GalleryCreator\ModuleGalleryCreatorList' => 'system/modules/gallery_creator/modules/ModuleGalleryCreatorList.php',
    'Markocupic\GalleryCreator\ModuleGalleryCreatorReader' => 'system/modules/gallery_creator/modules/ModuleGalleryCreatorReader.php',

    // Classes
    'Markocupic\GalleryCreator\Albums' => 'system/modules/gallery_creator/classes/Albums.php',
    'Markocupic\GalleryCreator\MigrationKit' => 'system/modules/gallery_creator/classes/MigrationKit.php',

    // Models
    'Contao\GalleryCreatorPicturesModel' => 'system/modules/gallery_creator/models/GalleryCreatorPicturesModel.php',
    'Contao\GalleryCreatorAlbumsModel' => 'system/modules/gallery_creator/models/GalleryCreatorAlbumsModel.php',
    'Contao\GalleryCreatorGalleriesModel' => 'system/modules/gallery_creator/models/GalleryCreatorGalleriesModel.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'mod_gallery_creator_list' => 'system/modules/gallery_creator/templates',
    'be_gc_html5_uploader' => 'system/modules/gallery_creator/templates',
    'mod_gallery_creator_reader' => 'system/modules/gallery_creator/templates',
));
