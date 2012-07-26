<?php
if (!defined('TL_ROOT'))
	die('You can not access this file directly!');

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

class GcHelpers extends System
{

	public function __construct()
	{
		$this->import('Database');
	}


	/**
	 * Hilfsmethode
	 * gibt ein Array mit den Unteralben eines Albums zurück
	 * @param integer
	 * @param integer
	 * @return array
	 */
	public function getAllSubalbums($parentId, $loop = 0)
	{
		if ($loop === 0)
			$this->childElements = array();
		$mysql = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE pid=?')->execute($parentId);
		$arrSubAlbums = array();
		while ($mysql->next())
		{
			$arrSubAlbums[] = $mysql->id;
			$this->getAllSubalbums($mysql->id, $loop++);
		}
		if (count($arrSubAlbums))
			$this->childElements = array_merge($this->childElements, $arrSubAlbums);
		return $this->childElements;
	}


	/**
	 * Hilfsmethode
	 * gibt ein Array mit allen Angaben des Parent-Albums zurueck
	 * @param integer
	 * @return array
	 */
	public function getParentAlbum($AlbumId)
	{
		$objAlbPid = $this->Database->prepare('SELECT pid FROM tl_gallery_creator_albums WHERE id=?')->execute($AlbumId);
		$parentAlb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($objAlbPid->pid);

		if ($parentAlb->numRows == 0)
			return NULL;
		$arrParentAlbum = $parentAlb->fetchAllAssoc();
		return $arrParentAlbum[0];
	}


	public function outputFrontendTemplate($strContent, $strTemplate)
	{
		if (0 === strpos($strTemplate, 'ce_gc') || strpos($strTemplate, 'ce_gc'))
		{
			$strContent = "
<div>
	<h1>" . $GLOBALS['TL_LANG']['gallery_creator']['fe_authentification_error'][0] . "</h1>
	<p>" . $GLOBALS['TL_LANG']['gallery_creator']['fe_authentification_error'][1] . "</p>
</div>
			";
		}
		return $strContent;
	}


}
?>