<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Marko Cupic 2010 
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch 
 * @package    gallery_creator 
 * @license    GNU/LGPL 
 * @filesource
 */



/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['album_info'] = 'albuminformations';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['protection'] = 'protect album';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['upload_settings'] = 'image setings';
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['uploader'] = 'java-uploader';

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
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_resolution'] = array('image width','During the upload process the image resolution will be scaled to the selected value.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_quality'] = array('image quality/compression','During the upload process the image will be compressed. (1000 = best quality)');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['preserve_filename'] = array('preserve the original filename','Otherwise the filename will be automatically generated.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['new']    = array('new album', 'Create a new album.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['edit']   = array('edit album', 'Edit album with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['delete'] = array('delete album', 'Delete album with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['upload_images'] = array('uplaod images', 'Upload images to album with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['import_images'] = array('copy images from directory on the server', 'copy images from directory on the server into the album with ID %s');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['cut'] = array('move album', 'move album with ID %s');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['pasteafter'] = array('Paste after', 'Paste after album ID %s');
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['pasteinto']  = array('Paste into', 'Paste into album ID %s');

/**
 * References
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['reference']['displ_alb_in_this_ce'] = 'CE with id=%s in article "%s" on page "%s"';


?>