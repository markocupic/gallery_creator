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

class ModuleDisplayGallery extends DisplayGallery
{
	/**
	 * Parse the template
	 * @return string
	 */
	public function generate()
	{
		$this->moduleType = 'fmd';
		return parent::generate();
	}

	/**
	 * Generate module
	 */
	protected function compile()
	{

		// ein eigenes Template nutzen
		if (TL_MODE == 'FE' && $this->gc_template != '')
		{
			$this->Template->style = count($this->arrStyle) ? implode(' ', $this->arrStyle) : '';
			$this->Template->cssID = strlen($this->cssID[0]) ? ' id="' . $this->cssID[0] . '"' : '';
			$this->Template->class = trim('mod_' . $this->type . ' ' . $this->cssID[1]);
		}

		//Weiterleitung bei nur 1 veroeffentlichten Album
		if (!$this->Input->get('vars') && $this->gc_redirectSingleAlb)
		{
			$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE published=?')->execute('1');
			if ($objAlbum->numRows === 1)
			{
				$this->Input->setGet('vars', $objAlbum->alias);
			}
		}

		if ($this->Input->get('vars'))
		{
			$this->Albumalias = $this->Input->get('vars');

			//Authentifizierung bei vor Zugriff geschuetzten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
			$this->feUserAuthentication($this->Albumalias);

			//gcmode muss vorerst noch beibehalten werden, da ansonsten alte, eigene templates nicht mehr funktionieren
			$this->Input->setGet('gcmode', 'overview');

			//jw_iamgerotator
			if (strstr($this->Input->get('vars'), 'jw_imagerotator'))
			{
				$get_gcalb = explode('.', $this->Input->get('vars'));
				// Albumalias aus Request ziehen
				$this->Albumalias = trim($get_gcalb[0]);
				$this->Input->setGet('gcmode', 'jw_imagerotator');
			}
			// Die AlbumId aus dem AlbumAlias extrahieren
			$objAlbum = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE alias=?')->execute($this->Albumalias);
			$this->AlbumId = $objAlbum->id;

		}
		//moduleType ist f�r die Ajax-Anwendungen von Bedeutung
		$this->Template->moduleType = $this->moduleType;

		switch ($this->Input->get('gcmode')) {

			default :
				$arrAlbums = array();

				//Pagination Einstellungen
				$limit = $this->gc_AlbumsPerPage;
				if ($limit > 0)
				{
					$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
					$offset = ($page - 1) * $limit;
					// Anzahl Alben
					if ($this->gc_hierarchicalOutput)
					{
						$objTotal = $this->Database->prepare('SELECT COUNT(id) as itemsTotal FROM tl_gallery_creator_albums WHERE published=? AND pid=? GROUP BY ?')->execute('1', '0', 'id');
					}
					else
					{
						$objTotal = $this->Database->prepare('SELECT COUNT(id) as itemsTotal FROM tl_gallery_creator_albums WHERE published=? GROUP BY ?')->execute('1', 'id');
					}

					$itemsTotal = $objTotal->itemsTotal;
					// Pagination Menu hinzufuegen
					$objPagination = new Pagination($itemsTotal, $limit);
					$this->Template->pagination = $objPagination->generate("\n ");
				}

				// Get all published albums
				if ($this->gc_hierarchicalOutput)
				{
					$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE published=? AND pid=? ORDER BY sorting ASC');
					if ($limit > 0)
						$objAlbum->limit($limit, $offset);
					$objAlbum = $objAlbum->execute('1', '0');
				}
				else
				{
					$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE published=? ORDER BY sorting ASC');
					if ($limit > 0)
						$objAlbum->limit($limit, $offset);
					$objAlbum = $objAlbum->execute('1');
				}
				//Album-array
				while ($objAlbum->next())
				{
					$arrAlbums[$objAlbum->id] = $this->getAlbumInformationArray($objAlbum->id, $this->gc_size_albumlist, 'fmd');
				}
				$this->Template->imagemargin = $this->generateMargin(unserialize($this->gc_imagemargin));
				$this->Template->arrAlbums = $arrAlbums;
				$this->getAlbumTemplateVars($objAlbum->id, 'fmd');
				break;

			case 'overview' :
				//Array mit allfaelligen Unteralben generieren
				if ($this->gc_hierarchicalOutput)
				{
					$objSubAlbums = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid=? AND published=? ORDER BY sorting ASC')->execute($this->AlbumId, '1');
					$arrSubalbums = array();
					while ($objSubAlbums->next())
					{
						$arrSubalbum = $this->getAlbumInformationArray($objSubAlbums->id, $this->gc_size_albumlist, 'fmd');
						array_push($arrSubalbums, $arrSubalbum);
					}
					$this->Template->subalbums = count($arrSubalbums) ? $arrSubalbums : NULL;
				}

				//Pagination Einstellungen
				$limit = $this->gc_ThumbsPerPage;
				if ($limit > 0)
				{
					$page = $this->Input->get('page') ? $this->Input->get('page') : 1;
					$offset = ($page - 1) * $limit;
					// Anzahl Alben
					$objTotal = $this->Database->prepare('SELECT COUNT(id) as itemsTotal FROM tl_gallery_creator_pictures WHERE published=? AND pid=?')->execute('1', $this->AlbumId);
					$itemsTotal = $objTotal->itemsTotal;
					// Pagination Menu hinzufuegen
					$objPagination = new Pagination($itemsTotal, $limit);
					$this->Template->pagination = $objPagination->generate("\n ");
				}

				$objPictures = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=?  AND pid=? ORDER BY sorting');
				if ($limit > 0)
					$objPictures->limit($limit, $offset);
				$objPictures = $objPictures->execute('1', $this->AlbumId);

				$arrPictures = array();
				while ($objPictures->next())
				{
					//Picture-array
					$arrPictures[$objPictures->id] = $this->getPictureInformationArray($objPictures->id, $this->gc_size_detailview, 'fmd');
				}
				//Bildarray als Template Variable
				$this->Template->arrPictures = $arrPictures;

				//generierte Fehlermeldungen
				if ($error)
					$this->Template->error = $error;

				//weitere Template Variablen erstellen
				$this->getAlbumTemplateVars($this->AlbumId, 'fmd');
				break;

			case 'jw_imagerotator' :
				header("content-type:text/xml;charset=utf-8");
				echo $this->getJwImagerotatorXml($this->Albumalias);
				exit ;
				break;
		}//end switch
	}

}
?>