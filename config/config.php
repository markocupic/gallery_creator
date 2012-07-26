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



if (TL_MODE == 'FE')
{
	//add the urlKeywords for folderurl-extension 
	$GLOBALS['URL_KEYWORDS'][] = 'vars';
}

	

//CSS for the frontend-output
$GLOBALS['TL_CSS'][]  = 'system/modules/gallery_creator/html/gallery_creator_fe.css';

/*



* -------------------------------------------------------------------------
* FRONT END MODULES
* -------------------------------------------------------------------------
*
* List all fontend modules and their class names.
*/



/**
 * Front end modules
 */
array_insert($GLOBALS['FE_MOD'], 0, array
(
	'gallery_creator' => array
	(
		'gallery_creator' => 'ModuleDisplayGallery'
	)
)
);



/*
* -------------------------------------------------------------------------
* FRONT END CONTENT ELEMENT
* -------------------------------------------------------------------------
*
*/


/**
 * Front end content element
 */



// add content element
array_insert($GLOBALS['TL_CTE'], 2, array

(
	'images' => array
	(
		'gallery_creator'     => 'ContentDisplayGallery'
	)
));



/**
 * -------------------------------------------------------------------------
 * BACK END MODULES
 * -------------------------------------------------------------------------
 */



if (TL_MODE == 'BE')
{
	//since version 2.11 Contao runs with one token per session
	if ($_GET['do']=='gallery_creator' && $_GET['mode']=='fileupload' )
	{
		if (version_compare(VERSION, '2.11', '<')) {
			//This config file is read before the system is initialized, so it's possible to bypass the token check
			// JumpLoader runs with one token per Session. For this reason its necessary to bypass the request-token-check
			// Allows to bypass the request-token-check if a known token is passed in
			if ( count($_GET)==6 && strlen($_GET['request_token']) && in_array($_GET['request_token'], $_SESSION['REQUEST_TOKEN']['BE']) )
			{
				if (!defined('BYPASS_TOKEN_CHECK')) define('BYPASS_TOKEN_CHECK', true);
			}
		}else{
			if( count($_GET)== 6 && strlen($_GET['request_token']) && $_SESSION['REQUEST_TOKEN'] == $_GET['request_token'] )
			{
				if (!defined('BYPASS_TOKEN_CHECK')) define('BYPASS_TOKEN_CHECK', true);
			}
		}
	}


	$GLOBALS['BE_MOD']['content']['gallery_creator'] = array
	(
		'icon' 		=> 'system/modules/gallery_creator/html/photo.png',
		'tables' 	=> array('tl_gallery_creator_albums','tl_gallery_creator_pictures')
	);

	$GLOBALS['TL_CSS'][]  = 	'system/modules/gallery_creator/html/gallery_creator_be.css';
}



?>