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
 * Class GalleryCreatorRunonce
 *
 * Provide methods regarding gallery_creator albums.
 *
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class GalleryCreatorRunonce
{

	/**
	 * add uuids to tl_gallery_creator_pictures version added in 4.8.0
	 */
	public static function addUuids()
	{

		// add field
		if(!\Database::getInstance()->fieldExists('uuid', 'tl_gallery_creator_pictures'))
		{
			\Database::getInstance()->query("ALTER TABLE `tl_gallery_creator_pictures` ADD `uuid` BINARY(16) NULL");
		}

		$objDB = \Database::getInstance()->execute("SELECT * FROM tl_gallery_creator_pictures WHERE uuid IS NULL");
		while($objDB->next())
		{
			if($objDB->path == '')
			{
				continue;
			}

			if(is_file(TL_ROOT . '/' . $objDB->path))
			{
				\Dbafs::addResource($objDB->path);
			}
			else
			{
				continue;
			}

			$oFile = new \File($objDB->path);
			$oFile->close();
			$fileModel = $oFile->getModel();
			if(\Validator::isUuid($fileModel->uuid))
			{
				\Database::getInstance()->prepare("UPDATE tl_gallery_creator_pictures SET uuid=? WHERE id=?")->execute($fileModel->uuid, $objDB->id);
				$_SESSION["TL_CONFIRM"][] = "Added a valid file-uuid into tl_gallery_creator_pictures.uuid ID " . $objDB->id . ". Please check if all the galleries are running properly.";
			}
		}
	}
}


\GalleryCreatorRunonce::addUuids();