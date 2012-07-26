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
 * error messages
 */

$GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file'] = 'The file "%s" doesn\'t exist on your server!';
$GLOBALS['TL_LANG']['ERR']['uploadError']         = 'The file "%s" could not been uploaded!';
$GLOBALS['TL_LANG']['ERR']['fileDontExist']       = 'The file "%s" does not exist!';
$GLOBALS['TL_LANG']['ERR']['fileNotReadable']     = 'The file "%s" ist not readable! Check access rights.';
$GLOBALS['TL_LANG']['ERR']['dirNotWriteable']     = 'The directory "%s" is not writeable! Check chmod settings!';


/**
 * frontend
 */

$GLOBALS['TL_LANG']['gallery_creator']['back_to_general_view'] = 'back to general view';
$GLOBALS['TL_LANG']['gallery_creator']['subalbums'] = 'subalbums';
$GLOBALS['TL_LANG']['gallery_creator']['pictures'] = 'pictures';
$GLOBALS['TL_LANG']['gallery_creator']['contains'] = 'contains';
$GLOBALS['TL_LANG']['gallery_creator']['fe_authentification_error'] = array('Authentification error','You tried to enter a protected album. Please log in as a frontend user or check your member-rights.');

?>