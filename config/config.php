<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2015 Leo Feyer
 *
 * @package Gallery Creator
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Define Constants
 */
define('GALLERY_CREATOR_UPLOAD_PATH', $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums');


/**
 * Frontend modules
 */
array_insert($GLOBALS['FE_MOD'], 2, array('gallery_creator' => array('gallery_creator_list' => 'GalleryCreator\ModuleGalleryCreatorList')));
array_insert($GLOBALS['FE_MOD'], 2, array('gallery_creator' => array('gallery_creator_reader' => 'GalleryCreator\ModuleGalleryCreatorReader')));



/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['content']['gallery_creator'] = array(
    'icon' => 'system/modules/gallery_creator/assets/images/picture.png',
    'tables' => array(
        'tl_gallery_creator_galleries',
        'tl_gallery_creator_albums',
        'tl_gallery_creator_pictures'
    )
);

if (TL_MODE == 'BE')
{
    $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/gallery_creator/assets/js/gallery_creator_be.js';
    $GLOBALS['TL_CSS'][] = 'system/modules/gallery_creator/assets/css/gallery_creator_be.css';
}


// Migrate from v.5.0.0 to new version with tl_gallery_creator_galleries
if(TL_MODE == 'BE')
{
    $GLOBALS['TL_HOOKS']['reviseTable'][] = array('Markocupic\GalleryCreator\MigrationKit', 'migrate');
}


/**
 * Auto item parameter for the album detail page
 */
$GLOBALS['TL_AUTO_ITEM'][] = 'albums';



/**
 * Register hook to add album items to the indexer
 */
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('GalleryCreator\GalleryCreator', 'getSearchablePages');


/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'gallery_creator';
$GLOBALS['TL_PERMISSIONS'][] = 'gallery_creatorp';
