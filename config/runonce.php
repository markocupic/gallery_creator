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
 * Run in a custom namespace, so the class can be replaced
 */

namespace MCupic\GalleryCreator;

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
    /**
     * add uuids to tl_gallery_creator_pictures version added in 4.8.0
     */
    public static function addUuids()
    {
        // add field
        if (!\Database::getInstance()->fieldExists('uuid', 'tl_gallery_creator_pictures'))
        {
            \Database::getInstance()->query("ALTER TABLE `tl_gallery_creator_pictures` ADD `uuid` BINARY(16) NULL");
        }
        $objPicture = \Database::getInstance()->execute("SELECT * FROM tl_gallery_creator_pictures WHERE uuid IS NULL");
        while ($objPicture->next())
        {
            if ($objPicture->path == '')
            {
                continue;
            }
            if (is_file(TL_ROOT . '/' . $objPicture->path))
            {
                $filesModel = \Dbafs::addResource($objPicture->path);
                if ($filesModel !== null)
                {
                    if (\Validator::isUuid($filesModel->uuid))
                    {
                        $objUpd = \GalleryCreatorPicturesModel::findByPk($objPicture->id);
                        $objUpd->uuid = $filesModel->uuid;
                        $objUpd->save();
                        $_SESSION["TL_CONFIRM"][] = "Added a valid file-uuid into tl_gallery_creator_pictures.uuid ID " . $objPicture->id . ". Please check if all the galleries are running properly.";
                    }
                }
            }
            elseif ($objPicture->name != '' && is_file(TL_ROOT . '/' . $objPicture->path . '/' . $objPicture->name))
            {
                $filesModel = \Dbafs::addResource($objPicture->path . '/' . $objPicture->name);
                if ($filesModel !== null)
                {
                    if (\Validator::isUuid($filesModel->uuid))
                    {
                        $objUpd = \GalleryCreatorPicturesModel::findByPk($objPicture->id);
                        $objUpd->uuid = $filesModel->uuid;
                        $objUpd->save();
                        $_SESSION["TL_CONFIRM"][] = "Added a valid file-uuid into tl_gallery_creator_pictures.uuid ID " . $objPicture->id . ". Please check if all the galleries are running properly.";
                    }
                }
            }
            else
            {
                continue;
            }
        }
    }
}


GalleryCreatorRunonce::addUuids();
