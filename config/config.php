<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2012 Leo Feyer
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
 * Front end content element
 */
array_insert($GLOBALS['TL_CTE'], 2, array('ce_type_gallery_creator' => array('gallery_creator' => 'ContentDisplayGallery')));

/**
 * Front end module
 */
array_insert($GLOBALS['FE_MOD'], 2, array('module_type_gallery_creator' => array('gallery_creator' => 'ModuleDisplayGallery')));

/**
 * Back end module
 */
if (TL_MODE == 'BE')
{
       // Jumploader bypass
       if ($_GET['mode'] == 'fileupload' && isset($_FILES['file']) && $_GET['rt'] != '')
       {
              $_POST['REQUEST_TOKEN'] = $_GET['rt'];
              $_POST['FORM_SUBMIT'] = $_GET['tl_upload'];
       }

       $GLOBALS['BE_MOD']['content']['gallery_creator'] = array(
              'icon' => 'system/modules/gallery_creator/assets/images/picture.png',
              'tables' => array(
                     'tl_gallery_creator_albums',
                     'tl_gallery_creator_pictures'
              )
       );

       // revise table script
       if(count($_GET) == 2 && $_GET['do'] == 'gallery_creator' && $_GET['ref']){
              $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/gallery_creator/assets/js/gallery_creator_be_revise_table.js';
       }

       $GLOBALS['TL_CSS'][] = 'system/modules/gallery_creator/assets/css/gallery_creator_be.css';
}
