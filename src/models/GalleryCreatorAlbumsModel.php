<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2015 Leo Feyer
 *
 * @package Gallery Creator
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace MCupic;

/**
 * Reads and writes tl_gallery_creator_albums
 */
class GalleryCreatorAlbumsModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_gallery_creator_albums';


    /**
     * gibt ein Array mit allen Angaben des Parent-Albums zurueck
     *
     * @param integer
     * @return array
     */
    public static function getParentAlbum($AlbumId)
    {

        $objAlbPid = \Database::getInstance()->prepare('SELECT pid FROM tl_gallery_creator_albums WHERE id=?')
            ->execute($AlbumId);
        $parentAlb = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')
            ->execute($objAlbPid->pid);
        if ($parentAlb->numRows == 0)
        {
            return null;
        }
        $arrParentAlbum = $parentAlb->fetchAllAssoc();

        return $arrParentAlbum[0];
    }

    /**
     * Get the parent album trail as an array
     * @param $AlbumId
     * @return array
     */
    public static function getParentAlbums($AlbumId)
    {

        $arrParentAlbums = array();
        $objAlb = \GalleryCreatorAlbumsModel::findByPk($AlbumId);
        if ($objAlb !== null)
        {
            $pid = $objAlb->pid;
            while ($pid > 0)
            {
                $parentAlb = \GalleryCreatorAlbumsModel::findByPk($pid);
                if ($parentAlb !== null)
                {
                    $arrParentAlbums[] = $parentAlb->id;
                    $pid = $parentAlb->pid;
                }
            }
        }

        return $arrParentAlbums;
    }

    /**
     * @param $parentId
     * @param string $strSorting
     * @param null $iterationDepth
     * @return array
     */
    public static function getChildAlbums($parentId, $strSorting = '', $iterationDepth = null)
    {

        // get the iteration depth
        $iterationDepth = $iterationDepth === '' ? null : $iterationDepth;

        $arrSubAlbums = array();
        if ($strSorting == '')
        {
            $strSql = 'SELECT id FROM tl_gallery_creator_albums WHERE pid=? ORDER BY sorting';
        } else
        {
            $strSql = 'SELECT id FROM tl_gallery_creator_albums WHERE pid=? ORDER BY ' . $strSorting;
        }
        $objAlb = \Database::getInstance()->prepare($strSql)->execute($parentId);
        $depth = $iterationDepth !== null ? $iterationDepth - 1 : null;


        while ($objAlb->next())
        {
            if ($depth < 0 && $iterationDepth !== null)
            {
                return $arrSubAlbums;
            }
            $arrSubAlbums[] = $objAlb->id;
            $arrSubAlbums = array_merge($arrSubAlbums, self::getChildAlbums($objAlb->id, $strSorting, $depth));
        }

        return $arrSubAlbums;
    }

    /**
     * @param $id
     * @return bool
     */
    public static function hasChildAlbums($id)
    {
        $arrChilds = self::getChildAlbums($id);
        if (count($arrChilds) >= 1)
        {
            return true;
        } else
        {
            return false;
        }
    }
}
