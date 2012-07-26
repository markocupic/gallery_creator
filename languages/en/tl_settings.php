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



//Legends
$GLOBALS['TL_LANG']['tl_settings']['gallery_creator_legend']     = 'Gallery-Creator-settings';


//Fields
$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_opacity']  = array('Watermark opacity');
$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_valign']  = array('Watermark: vertical position');
$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_halign']  = array('Watermark: horizontal position');
$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_path'] = array('Add a watermark');
$GLOBALS['TL_LANG']['tl_settings']['gc_disable_backend_edit_protection'] = array('Remove Backend Album Protection', 'If you choose this setting, you will enable "album-non-owners" deleting and editing albums in the backend.');
$GLOBALS['TL_LANG']['tl_settings']['gc_album_import_copy_files'] = array('Do a copy of each imported file in the "gallery_creator_albums-directory" when importing images', 'If you choose this setting, the system will do copy of each image when importing images from the server.');



?>