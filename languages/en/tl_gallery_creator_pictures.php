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
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['media_integration'] = 'Embed a movie or a sound';

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['id'] = array('image-ID');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['published'] = array('publish image');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['date'] = array('date of creation');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['owner'] = array('image-owner');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['name'] = array('filename');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['addCustomThumb'] = array('add a custom thumbnail');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['customThumb'] = array('custom thumbnail.');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['title'] = array('image title');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['comment'] = array('image-comment');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['sorting'] = array('sort sequence');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['filename'] = array('filename');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['path'] = array('path');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['socialMediaSRC'] = array('Embed movies/sounds located on a social-media-plattform', 'Add the full path to the source:  http://www.youtube.com/watch?v=kVdVTVR-j0Q');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['localMediaSRC'] = array('Embed movies/sounds located on the contao-file-system');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['cssID'] = array('CSS ID/class', 'Here you can set an ID and one or more classes for this picture.');

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['edit'] = array('edit image settings', 'Edit image with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['delete'] = array('delete image', 'Delete image with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['cut'] = array('change order', 'Move image with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['paste'] = array('paste below', 'Paste below image with ID %s.');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['imagerotate'] = array('rotate image', 'Rotate image by 90°.');
$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['jumpLoader'] = array('upload images', 'upload images');

?>