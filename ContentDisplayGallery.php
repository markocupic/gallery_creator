<?php
if (!defined('TL_ROOT'))
	die('You can not access this file directly!');

/**
 *
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

class ContentDisplayGallery extends DisplayGallery
{

	/**
	 * Set the template
	 * @return string
	 */
	public function generate()
	{
		$this->moduleType = 'cte';

		if ($this->Input->get('vars'))
		{
			$arrGetRequest = explode('.', $this->Input->get('vars'));
			//Id des Inhaltselements wird bei mehreren Gc-Inhaltselementen auf einer Seite aus dem Get-Request gezogen
			$this->ContentElementId = $this->countGcContentElementsOnPage() > 1 ? trim($arrGetRequest[0]) : $this->id;

			//Falls mehrere gc Inhaltselemente auf einer Seite eingesetzt wurden, wird in der Detailansicht nur dasjenige Album angezeigt, das auch wirklich gewaehlt wurde.
			if ($this->id != $this->ContentElementId)
			{
				//inaktive Inhaltselemente im detailview nicht parsen und leeren String zurueck geben
				return '';
			}
		}
		return parent::generate();
	}

	/**
	 * Generate module
	 */
	protected function compile()
	{
		//Request verarbeiten
		$this->evalRequestVars();

		if (!is_array(deserialize($this->gc_publish_albums)) && !$this->gc_publish_all_albums)
			return;
		if ($this->gc_publish_all_albums)
		{
			//wenn alle vorhandenen Alben angezeigt werden sollen
			$arrSelectedAlb = $this->listAllAlbums();
		}
		else
		{
			//wenn nur ausgewählte Alben angezeigt werden sollen
			$arrSelectedAlb = deserialize($this->gc_publish_albums);
		}

		switch ($this->Input->get('gcmode')) {
			default :
				//Abbruch: Wenn keine Alben angewaehlt wurden, kann auch nichts gezeigt werden
				if (count($arrSelectedAlb) < 1)
					return;

				//Pagination Settings
				$limit = $this->gc_AlbumsPerPage;
				if ($limit > 0)
				{
					$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
					$offset = ($page - 1) * $limit;
					//Anzahl Albums
					$itemsTotal = count($arrSelectedAlb);
					//Pagination Menu erstellen
					$objPagination = new Pagination($itemsTotal, $limit);
					$this->Template->pagination = $objPagination->generate("\n ");
				}

				if ($limit == '0')
				{
					$limit = count($arrSelectedAlb);
					$offset = 0;
				}

				$arrAlbums = array();
				for ($i = $offset; $i < $offset + $limit; $i++)
				{
					$currAlbumId = $arrSelectedAlb[$i];
					$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=? AND published=?')->execute($currAlbumId, '1');
					//Wenn Album inexistent oder nicht veroeffentlicht->Abbruch
					if (!$objAlbum->numRows)
						continue;

					$objPics = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid = ? AND published=?')->execute($objAlbum->id, '1');
					//Wenn keine oder oder nur nicht veröffentlichte Bilder vorhanden->Abbruch
					if (!$objPics->numRows)
						continue;
					if ($objPics->numRows > 0)
					{
						$arrAlbums[$objAlbum->id] = $this->getAlbumInformationArray($objAlbum->id, $this->gc_size_albumlist, 'cte');
					}
				}
				$this->Template->imagemargin = $this->generateMargin(unserialize($this->imagemargin));
				$this->Template->arrAlbums = $arrAlbums;
				$this->getAlbumTemplateVars($objAlbum->id, 'cte');
				break;

			case 'overview' :
				//for security reasons...
				if (!in_array($this->AlbumId, $arrSelectedAlb))
				{
					$error[] = "Gallery with alias " . $this->AlbumAlias . " is not available or you have not enough permission to watch it!!!";
				}

				//Pagination Settings
				$limit = $this->gc_ThumbsPerPage;
				if ($limit > 0)
				{
					$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
					$offset = ($page - 1) * $limit;
					//Anzahl Alben
					$objTotal = $this->Database->prepare('SELECT COUNT(id) as itemsTotal FROM tl_gallery_creator_pictures WHERE published=? AND pid=? GROUP BY ?')->execute('1', $this->AlbumId, 'id');
					$itemsTotal = $objTotal->itemsTotal;
					//Pagination Menu erstellen
					$objPagination = new Pagination($itemsTotal, $limit);
					$this->Template->pagination = $objPagination->generate("\n ");
				}
				//sorting
				$str_sorting = $this->gc_picture_sorting == '' || $this->gc_picture_sorting_direction == '' ? 'sorting ASC' : $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;

				$objPictures = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY ' . $str_sorting);
				if ($limit > 0)
				{
					$objPictures->limit($limit, $offset);
				}
				$objPictures = $objPictures->execute('1', $this->AlbumId);

				$arrPictures = array();

				while ($objPictures->next())
				{
					//Bildarray fuellen
					$arrPictures[$objPictures->id] = $this->getPictureInformationArray($objPictures->id, $this->gc_size_detailview, 'cte');
				}

				//Bildarray als Template Variable
				$this->Template->arrPictures = $arrPictures;

				//generierte Fehlermeldungen
				if ($error)
				{
					$this->Template->error = $error;
				}
				//weitere Template Variablen erstellen
				$this->getAlbumTemplateVars($this->AlbumId, 'cte');
				break;

			case 'jw_imagerotator' :
				header("content-type:text/xml;charset=utf-8");
				echo $this->getJwImagerotatorXml($this->Albumalias);
				exit ;
				break;
		}//end switch
	}

	/**
	 * Gibt ein Array mit der id aller Alben in der gewählten Reihenfolge zurück
	 * @return array
	 */
	protected function listAllAlbums()
	{
		$objContent = $this->Database->prepare('SELECT gc_sorting, gc_sorting_direction FROM tl_content WHERE id=?')->execute($this->id);

		$strSorting = $objContent->gc_sorting == '' || $objContent->gc_sorting_direction == '' ? 'date DESC' : $objContent->gc_sorting . ' ' . $objContent->gc_sorting_direction;

		$objGca = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_albums WHERE published=? ORDER BY ' . $strSorting)->execute('1');

		$arrAlb = array();
		while ($objGca->next())
		{
			$arrAlb[] = $objGca->id;
		}

		return $arrAlb;
	}

}
?>