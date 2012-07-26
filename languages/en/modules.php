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
 * Back end modules
 */
$GLOBALS['TL_LANG']['MOD']['gallery_creator'] = array('Gallery Creator', 'Create and edit album-galleries.');


/**
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['webgallery'] = array('web galleries');
$GLOBALS['TL_LANG']['FMD']['gallery_creator'] = array('Gallery Creator', 'Present gallery_creator albums as a frontend module.');


/**
 * Front end content-elements
 */
$GLOBALS['TL_LANG']['CTE']['gallery_creator'] = array('Gallery Creator','Present gallery_creator albums as a frontend content element.');

?>