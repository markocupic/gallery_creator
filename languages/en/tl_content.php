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
 * module config
 */

//Legends
$GLOBALS['TL_LANG']['tl_content']['miscellaneous_legend']  			= 'Miscellaneous settings';
$GLOBALS['TL_LANG']['tl_content']['sorting']  						= 'Album listing sorting options';
$GLOBALS['TL_LANG']['tl_content']['picture_sorting']  				= 'Picture sorting options';
$GLOBALS['TL_LANG']['tl_content']['thumb_legend']  					= 'Thumbnail settings';



//Fields
$GLOBALS['TL_LANG']['tl_content']['gc_rows'] = array('Thumbnails per row', 'Select the number of thumbnails per row. (0=As much as possible)');
$GLOBALS['TL_LANG']['tl_content']['gc_publish_albums'] = array('Publish these albums only', 'Selected albums will be displayed in the frontend.');
$GLOBALS['TL_LANG']['tl_content']['gc_publish_all_albums'] = array('Publish all given albums in the frontend');
$GLOBALS['TL_LANG']['tl_content']['gc_template']   = array('Gallery template', 'Select a personal gallery template.');
$GLOBALS['TL_LANG']['tl_content']['gc_activateThumbSlider'] = array('Activate Ajax-Thumb-Slider', 'Activate Ajax-Thumb-Slider on mouseover in the album listing?');
$GLOBALS['TL_LANG']['tl_content']['gc_redirectSingleAlb'] = array('Redirection in case of a single album', 'Should be automatically redirected to the detail-view, in case of single-album-choice?');
$GLOBALS['TL_LANG']['tl_content']['gc_AlbumsPerPage'] = array('Items per page in the albumlisting', 'The number of items per page in the albumlisting. Set to 0 to disable pagination.');
$GLOBALS['TL_LANG']['tl_content']['gc_ThumbsPerPage'] = array('Thumbs per page in the detailview', 'The number of thumbnails per page in the detailview. Set to 0 to disable pagination.');
$GLOBALS['TL_LANG']['tl_content']['gc_sorting'] = array('Album sorting', 'According to which field the albums should be sorted?');
$GLOBALS['TL_LANG']['tl_content']['gc_sorting_direction'] = array('Sort sequence','DESC: descending, ASC: ascending');
$GLOBALS['TL_LANG']['tl_content']['gc_picture_sorting'] = array('Picture sorting', 'According to which field the pictures in a single album should be sorted?');
$GLOBALS['TL_LANG']['tl_content']['gc_picture_sorting_direction'] = array('Sort sequence','DESC: descending, ASC: ascending');
$GLOBALS['TL_LANG']['tl_content']['gc_size_detailview'] = array('Detailview: Thumbnail width and height', 'Here you can set the image dimensions and the resize mode.');
$GLOBALS['TL_LANG']['tl_content']['gc_size_albumlist'] = array('Albumlist: Thumbnail width and height', 'Here you can set the image dimensions and the resize mode.');
$GLOBALS['TL_LANG']['tl_content']['gc_fullsize']     = array('Full-size view/new window', 'Open the full-size image in a lightbox or the link in a new browser window.');


?>