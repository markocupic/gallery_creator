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
namespace Contao;

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
     * Find a published gallery from one or more calendars by its ID or alias
     *
     * @param mixed $varId      The numeric ID or alias name
     * @param array $arrPids    An array of calendar IDs
     * @param array $arrOptions An optional options array
     *
     * @return \GalleryCreatorAlbumsModel|null The model or null if there is no event
     */
    public static function findPublishedByParentAndIdOrAlias($varId, $arrPids, array $arrOptions=array())
    {
        if (!is_array($arrPids) || empty($arrPids))
        {
            return null;
        }

        $t = static::$strTable;
        $arrColumns = array("($t.id=? OR $t.alias=?) AND $t.pid IN(" . implode(',', array_map('intval', $arrPids)) . ")");

        if (!BE_USER_LOGGED_IN)
        {
            //$time = \Date::floorToMinute();
            //$arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.published='1'";
            $arrColumns[] = "$t.published='1'";
        }

        return static::findOneBy($arrColumns, array((is_numeric($varId) ? $varId : 0), $varId), $arrOptions);
    }

    /**
     * Find published albums with the default redirect target by their parent ID
     *
     * @param integer $intPid     The gallery ID
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|\GalleryCreatorAlbumsModel[]|\GalleryCreatorAlbumsModel|null A collection of models or null if there are no albums
     */
    public static function findPublishedDefaultByPid($intPid, array $arrOptions=array())
    {
        $t = static::$strTable;
        $arrColumns = array("$t.pid=? AND $t.source='default'");

        if (!BE_USER_LOGGED_IN)
        {
            $time = \Date::floorToMinute();
            $arrColumns[] = "$t.published='1'";
        }

        if (!isset($arrOptions['order']))
        {
            //$arrOptions['order']  = "$t.startTime DESC";
        }

        return static::findBy($arrColumns, $intPid, $arrOptions);
    }


}
