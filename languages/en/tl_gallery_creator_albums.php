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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['album_info'] = 'albuminformations';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['protection'] = 'protect album';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['upload_settings'] = 'image settings';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['insert_article'] = 'insert articles before or after the album';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['uploader_legend'] = 'uploader';


/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['id'] = array('album-ID');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['alias'] = array('albumalias', 'The Albumalias defines although the album-foldername.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['published'] = array('publish Album');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['date'] = array('date of creation');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['displ_alb_in_this_ce'] = array('Display this album in the following articles which are containing "gallery_creator" content-elements?');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owner'] = array('albumowner');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['event_location'] = array('event-location');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['name'] = array('albumname');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owners_name'] = array('albumowner');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['comment'] = array('album-comment');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['thumb'] = array('preview-thumb', 'Which image should be displayed in the album preview?');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['protected'] = array('protect album');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['groups'] = array('allowed frontend-groups');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['insert_article_pre'] = array('insert an article optionally before the album','Insert the id of the article that you optionally like have displayed in the detail view.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['insert_article_post'] = array('insert an article optionally after the album','Insert the id of the article that you optionally like have displayed in the detail view.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['fileupload'] = array('File Upload', 'Browse your local computer and select the files you want to upload to the server.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['uploader'] = array('Uploader', 'Please choose the uploader.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_resolution'] = array('image width','During the upload process the image resolution will be scaled to the selected value.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_quality'] = array('image quality/compression','During the upload process the image will be compressed. (1000 = best quality)');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['preserve_filename'] = array('preserve the original filename','Otherwise the filename will be automatically generated.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['visitors'] = array('Number of visitors');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['visitors_details'] = array('Visitors details (ip, browser type, etc.)');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['sortBy'] = array('Order by', 'Please choose the sort order.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['clean_db'] = array('Clean the Database', 'Tidy up the database from old entries');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['new']    = array('new album', 'Create a new album.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['edit']   = array('edit album', 'Edit album with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['delete'] = array('delete album', 'Delete album with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['toggle'] = array('Publish/unpublish album','Publish/unpublish album ID %s');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['upload_images'] = array('uplaod images', 'Upload images to album with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['import_images'] = array('copy images from directory on the server', 'copy images from directory on the server into the album with ID %s');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['cut'] = array('move album', 'move album with ID %s');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['pasteafter'] = array('Paste after', 'Paste after album ID %s');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['pasteinto']  = array('Paste into', 'Paste into album ID %s');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['reference']['displ_alb_in_this_ce'] = 'CE with id=%s in article "%s" on page "%s"';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['reference']['no_scaling'] = 'Do not scale images during the upload process.';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['name_asc'] = 'File name (ascending)';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['name_desc'] = 'File name (descending)';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['date_asc'] = 'Date (ascending)';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['date_desc'] = 'Date (descending)';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['custom'] = 'Custom order';