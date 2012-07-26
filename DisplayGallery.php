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

//Hilfsklasse einbinden
require_once (TL_ROOT . '/system/modules/gallery_creator/helpers/GcHelpers.php');

/**
 * Class DisplayGallery
 *
 * Parent class for gallery_creator modules.
 * @copyright  Marko Cupic 2010
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    gallery_creator
 */

abstract class DisplayGallery extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_gc_default';

	/**
	 * path to the default thumbnail, if no thumb was found
	 * @var string
	 */
	protected $defaultThumb = 'system/modules/gallery_creator/html/no_image_available.jpg';

	/**
	 * Parse the template
	 * @return string
	 */
	public function generate()
	{
		$this->GcHelpers = new GcHelpers;
		global $objPage;
		$this->objPage = $objPage;

		//assigning the frontend template
		$this->strTemplate = $this->gc_template != "" ? $this->gc_template : $this->strTemplate;

		//do some default-settings for the thumb-size if no settings are done in the module-/content-settings
		$this->checkThumbSizeSettings();
		return parent::generate();
	}


	/**
	 * do some default-settings for the thumb-size if no settings are done in the module-/content-settings
	 */
	protected function checkThumbSizeSettings()
	{
		if ($this->gc_size_albumlist == "")
			$this->gc_size_albumlist = serialize(array(
				"110",
				"110",
				"crop"
			));
		if ($this->gc_size_detailview == "")
			$this->gc_size_detailview = serialize(array(
				"110",
				"110",
				"crop"
			));
	}


	/**
	 * Hilfsmethode
	 * Gibt die Anzahl der Gallery_Creator Inhaltselemente auf einer Seite zurück
	 * @return integer
	 */
	protected function countGcContentElementsOnPage()
	{
		if (!$this->objPage)
		{
			global $objPage;
			$this->objPage = $objPage;
		}
		//kontrollieren, ob Weiterleitung zu overwiev moeglich ist
		//Keine Weiterleitung moeglich, bei mehreren aktivierten GALLERY_CREATOR Inhaltselementen im selben Artikel
		$objArticlesOfCurrentPage = $this->Database->prepare('SELECT id FROM tl_article WHERE pid=? AND published=?')->execute($this->objPage->id, 1);

		$arrArticlesOfCurrentPage = array();
		while ($objArticlesOfCurrentPage->next())
		{
			$arrArticlesOfCurrentPage[] = (int)$objArticlesOfCurrentPage->id;
		}

		$gcElementCounter = 0;
		$objCE = $this->Database->prepare('SELECT pid FROM tl_content WHERE type=?')->execute($this->type);
		while ($objCE->next())
		{
			if (in_array($objCE->pid, $arrArticlesOfCurrentPage))
			{
				$gcElementCounter += 1;
			}
		}
		return $gcElementCounter;
	}


	/**
	 * creates a thumbnail
	 * @param string
	 * @param array
	 * @return string
	 */
	protected function createThumbnail($file, $size)
	{
		$objFile = new File($file);
		if ($objFile->isGdImage)
		{
			return $this->getImage($file, $size[0], $size[1], $size[2]);
		}
		else
		{
			return false;
		}
	}


	/**
	 * Hilfsmethode
	 * Ueberprueft, ob bei nur einem ausgewaehlten Album direkt zur Thumnailuebersicht des Albums weitergeleitet werden soll.
	 * @return bool
	 */
	protected function doRedirectOnSingleAlbum()
	{
		if (TL_MODE == 'BE')
		{
			return false;
		}
		//wahr, wenn im gc-Inhaltselement nur 1 Album selektiert wurde
		$arrAlbId = deserialize($this->gc_publish_albums);
		if (count($arrAlbId) == 1)
		{
			$singleAlbum = true;
		}

		//wahr wenn: weniger als zwei gc Inhaltselemente auf aktueller Seite && Galerie enthaelt nur 1 Album && Weiterleitung in den Elementeinstellungen aktiviert ist
		if ($this->countGcContentElementsOnPage() < 2 && $singleAlbum && $this->gc_redirectSingleAlb)
		{
			return true;
		}
	}


	/**
	 * evaluate the request and extracts the album-id and the content-element-id
	 */
	public function evalRequestVars()
	{
		if ($this->gc_publish_all_albums != 1)
		{
			if (!unserialize($this->gc_publish_albums))
				return;
		}
		if ($this->Input->get('vars'))
		{
			$arrGetRequest = explode('.', $this->Input->get('vars'));
			//aktueller Albumalias
			$this->Albumalias = $this->countGcContentElementsOnPage() > 1 ? trim($arrGetRequest[1]) : trim($arrGetRequest[0]);

			//Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
			$this->feUserAuthentication($this->Albumalias);

			//fuer jw_imagerotator ajax-requests
			if (strstr($this->Input->get('vars'), 'jw_imagerotator'))
			{
				$this->Input->setGet('gcmode', 'jw_imagerotator');
				return;
			}
		}

		//wenn nur ein Album ausgewaehlt wurde und Weiterleitung in den Inhaltselementeinstellungen aktiviert wurde, wird weitergeleitet
		if ($this->doRedirectOnSingleAlbum())
		{
			$arrAlbId = unserialize($this->gc_publish_albums);
			$objAlbum = $this->Database->prepare("SELECT alias FROM tl_gallery_creator_albums WHERE id=?")->execute($arrAlbId[0]);

			//Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
			$this->feUserAuthentication($objAlbum->alias);

			$this->Input->setGet('vars', $this->id . '.' . $objAlbum->alias);
			$this->Albumalias = $objAlbum->alias;
			$this->Input->setGet('gcmode', 'overview');
		}

		//Request Variablen verarbeiten
		if ($this->Input->get('vars'))
		{
			// Die AlbumId des anzuzeigenden Albums aus der db ziehen extrahieren
			$objAlbum = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE alias=?')->execute($this->Albumalias);
			$this->AlbumId = $objAlbum->id;

			//gcmode muss vorerst noch beibehalten werden, da ansonsten alte eigene templates nicht mehr funktionieren
			$this->Input->setGet('gcmode', 'overview');
		}
	}


	/**
	 * Check if fe-user is allowed watching this album
	 * @param string
	 * @return bool
	 */
	protected function feUserAuthentication($Albumalias)
	{
		$objAlb = $this->Database->prepare('SELECT protected AS protected_album,groups FROM tl_gallery_creator_albums WHERE alias=?')->execute($Albumalias);
		if (!$objAlb->protected_album)
			return true;

		$this->import('FrontendUser', 'User');
		$groups = deserialize($objAlb->groups);
		if (!FE_USER_LOGGED_IN || !is_array($groups) || count($groups) < 1 || count(array_intersect($groups, $this->User->groups)) < 1)
		{
			//mit parseFrontendHook die Fehlermeldung ausgeben
			$GLOBALS['TL_HOOKS']['parseFrontendTemplate'][] = array(
				'GcHelpers',
				'outputFrontendTemplate'
			);
			//Fehlerkonstante definieren
			define('GALLERY_CREATOR_ALBUM_AUTHENTIFICATION_ERROR', true);
			return false;
		}
		return true;
	}


	/**
	 * responds to ajax-requests
	 * @access public
	 * @return string
	 */
	public function generateAjax()
	{
		//gibt ein Array mit allen Bildinformationen des Bildes mit der id imageId zurück
		if ($this->Input->get('isAjax') && $this->Input->get('getImage') && strlen($this->Input->get('imageId')))
		{
			$arrPicture = $this->getPictureInformationArray($this->Input->get('imageId'), NULL, $this->Input->get('action'));
			return json_encode($arrPicture);
			exit ;
		}

		//thumbslider der Albenübersicht
		if ($this->Input->get('isAjax') && $this->Input->get('thumbSlider'))
		{
			$this->checkThumbSizeSettings();
			$size = $this->gc_size_albumlist;
			$size = unserialize($size);

			$objAlbum = $this->Database->prepare('SELECT thumb,alias FROM tl_gallery_creator_albums WHERE id=?')->execute($this->Input->get('AlbumId'));
			//Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
			$this->feUserAuthentication($objAlbum->alias);
			if (GALLERY_CREATOR_ALBUM_AUTHENTIFICATION_ERROR === true)
				return;

			$objPictures = $this->Database->prepare('SELECT count(id) AS Anzahl FROM tl_gallery_creator_pictures WHERE published=? AND pid=? AND id!=?')->execute(1, $this->Input->get('AlbumId'), $objAlbum->thumb);
			if ($objPictures->Anzahl < 2)
			{
				return json_encode(array('thumbPath' => ''));
			}

			$limit = $this->Input->get('limit');
			$objPicture = $this->Database->prepare('SELECT name, path FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY id')->limit(1, $limit)->executeUncached(1, $this->Input->get('AlbumId'), $objAlbum->thumb);
			$jsonUrl = array(
				'thumbPath' => $this->getImage($objPicture->path . "/" . $objPicture->name, $size[0], $size[1], $size[2]),
				'eventId' => $this->Input->get('eventId')
			);

			echo json_encode($jsonUrl);
			exit ;
		}

		//Detailansicht nur mit Lightbox, für ce_gc_lightbox.tpl Template
		if ($this->Input->get('isAjax') && $this->Input->get('LightboxSlideshow') && $this->Input->get('albumId'))
		{
			//Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
			$objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')->execute($this->Input->get('albumId'));
			$this->feUserAuthentication($objAlbum->alias);
			if (GALLERY_CREATOR_ALBUM_AUTHENTIFICATION_ERROR === true)
				return;

			$json = "";
			$objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY id')->executeUncached(1, $this->Input->get('albumId'));
			while ($objPicture->next())
			{
				$href = $objPicture->path . "/" . $objPicture->name;
				$href = trim($objPicture->socialMediaSRC) != "" ? trim($objPicture->socialMediaSRC) : $href;
				$href = trim($objPicture->localMediaSRC) != "" ? trim($objPicture->localMediaSRC) : $href;

				$json .= $href . "###";
				$json .= $objPicture->comment . " ***";
			}
			$jsonUrl = array('arrImage' => $json);
			echo json_encode($jsonUrl);
			exit ;
		}
	}


	/**
	 * Generate the xml-output for jwImagerotator
	 * @param string
	 * @return string
	 */
	protected function getJwImagerotatorXml($albumalias)
	{
		$objAlbum = $this->Database->prepare('SELECT id, owners_name FROM tl_gallery_creator_albums WHERE alias=? and published=1')->execute($albumalias);
		$objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY sorting')->execute('1', $objAlbum->id);

		//playlist xml output
		$xml = "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
		$xml .= "<trackList>\n";
		while ($objPicture->next())
		{
			$caption = trim($objPicture->comment) != "" ? $objPicture->comment : $objPicture->name;
			$xml .= "\t<track>\n";
			$xml .= "\t\t<title>" . $caption . "</title>\n";
			//$xml .= "\t\t<author>" . $objAlbum->owners_name . "</author>\n";
			$href = $objPicture->path . "/" . $objPicture->name;
			/*imagerotator can't play movies*/
			//$href = trim($objPicture->socialMediaSRC) != "" ? trim($objPicture->socialMediaSRC) :  $href;
			//$href = trim($objPicture->localMediaSRC) != "" ? trim($objPicture->localMediaSRC) :  $href;
			$xml .= "\t\t<location>" . $href . "</location>\n";
			//$xml .= "\t\t<info>".$objPictures->comment."</info>\n";
			$xml .= "\t</track>\n";
		}
		$xml .= "</trackList>\n";
		$xml .= "</playlist>\n";
		return $xml;
	}


	/**
	 * Returns the path to the preview-thumbnail of an album
	 * @param integer
	 * @return string
	 */
	protected function getAlbumPreviewThumb($AlbumId)
	{
		$objAlb = $this->Database->prepare('SELECT thumb FROM tl_gallery_creator_albums WHERE id=?')->execute($AlbumId);
		if (!$objAlb->thumb)
		{
			return $this->defaultThumb;
		}

		$objPreviewThumb = $this->Database->prepare('SELECT path, name FROM tl_gallery_creator_pictures WHERE id=?')->execute($objAlb->thumb);
		$objFile = new File($objPreviewThumb->path . '/' . $objPreviewThumb->name);
		if ($objFile->isGdImage)
		{
			return $objPreviewThumb->path . '/' . $objPreviewThumb->name;
		}
		else
		{
			return $this->defaultThumb;
		}
	}


	/**
	 * Returns the information-array about an album
	 * @param integer
	 * @param array
	 * @param string
	 * @return array
	 */
	protected function getAlbumInformationArray($AlbumId, $size, $ContentType)
	{
		if ($ContentType != 'fmd' && $ContentType != 'cte')
		{
			$strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
			__error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
		}

		$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($AlbumId);
		//Anzahl Subalben ermitteln
		if ($this->gc_hierarchicalOutput)
		{
			$objSubAlbums = $this->Database->prepare('SELECT thumb, count(id) AS countSubalbums FROM tl_gallery_creator_albums WHERE published=? AND pid=? GROUP BY ?')->execute('1', $AlbumId, 'id');
		}
		$objPics = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=? AND published=?')->execute($objAlbum->id, '1');

		//Array Thumbnailbreite
		$size = unserialize($size);

		//exif-data
		try
		{
			$exif = is_callable('exif_read_data') && TL_MODE == 'FE' ? @exif_read_data($objPics->path . '/' . $objPics->name) : array('info' => "The function 'exif_read_data()' is not available on this server.");
		} catch(Exception $e)
		{
			echo $e->getMessage();
			$exif = array('info' => "The function 'exif_read_data()' is not available on this server.");
		}

		$arrFilterSearch = array('"');
		$arrFilterReplace = array('');
		$arrAlbum = array(
			//[int] Album-Id
			'id' => $objAlbum->id,
			//[int] pid parent Album-Id
			'pid' => $objAlbum->pid,
			//[int] Sortierindex
			'sorting' => $objAlbum->sorting,
			//[boolean] veroeffentlicht (true/false)
			'published' => $objAlbum->published,
			//[int] id des Albumbesitzers
			'owner' => $objAlbum->owner,
			//[string] Benutzername des Albumbesitzers
			'owners_name' => $objAlbum->owners_name,
			//[int] Zeitstempel der letzten Aenderung
			'tstamp' => $objAlbum->tstamp,
			//[int] Event-Unix-timestamp (unformatiert)
			'event_tstamp' => $objAlbum->date,
			'date' => $objAlbum->date,
			//[string] Event-Datum (formatiert)
			'event_date' => $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date),
			//[string] Event-Location
			'event_location' => $objAlbum->event_location,
			//[string] Albumname
			'name' => $objAlbum->name,
			//[string] Albumalias (=Verzeichnisname)
			'alias' => $objAlbum->alias,
			//[string] Albumkommentar
			'comment' => strlen($objAlbum->comment) ? str_replace($arrFilterSearch, $arrFilterReplace, $objAlbum->comment) : NULL,
			'caption' => strlen($objAlbum->comment) ? str_replace($arrFilterSearch, $arrFilterReplace, $objAlbum->comment) : NULL,
			//[string] Link zur Detailansicht
			'href' => TL_MODE == 'FE' ? $this->generateFrontendUrl($this->objPage->row(), '/vars/' . $objAlbum->alias) : NULL,
			//[string] Inhalt fuer das title Atttribut
			'title' => $objAlbum->name . ' [' . ($objPics->numRows ? $objPics->numRows . ' ' . $GLOBALS['TL_LANG']['gallery_creator']['pictures'] : '') . ($objSubAlbums->countSubalbums > 0 ? ' ' . $GLOBALS['TL_LANG']['gallery_creator']['contains'] . ' ' . $objSubAlbums->countSubalbums . '  ' . $GLOBALS['TL_LANG']['gallery_creator']['subalbums'] . ']' : ']'),
			//[int] Anzahl Bilder im Album
			'count' => $objPics->numRows,
			//[int] Anzahl Unteralben
			'count_subalbums' => count($this->GcHelpers->getAllSubalbums($objAlbum->id)),
			//[string] alt Atrribut fuer das Vorschaubild
			'alt' => $objPreviewThumb->name,
			//[string] Pfad zum Originalbild
			'src' => $this->getAlbumPreviewThumb($objAlbum->id),
			//[string] Pfad zum Thumbnail
			'thumb_src' => $this->getImage($this->getAlbumPreviewThumb($objAlbum->id), $size[0], $size[1], $size[2]),
			//[string] css-Classname
			'class' => 'thumb',
			//[int] Thumbnailbreite
			'size' => $size,
			//[array] array mit exif metatags
			'exif' => $exif,
			//[string] javascript-Aufruf
			'thumbMouseover' => $this->gc_activateThumbSlider ? "objGalleryCreator.initThumbSlide(this, " . $this->id . ", " . $objAlbum->id . ", " . $objPics->numRows . ", '" . strtolower($this->moduleType) . "');" : ""
		);

		if ($ContentType == 'cte')
		{
			if ($this->countGcContentElementsOnPage() > 1)
			{
				$arrAlbum['href'] = TL_MODE == 'FE' ? $this->generateFrontendUrl($this->objPage->row(), '/vars/' . $this->id . '.' . $objAlbum->alias) : NULL;
			}
		}

		//Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_albums erweitert wurde
		$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($AlbumId);
		foreach ($objAlbum->fetchAssoc() as $key => $value)
		{
			if (!array_key_exists($key, $arrAlbum))
			{
				$arrAlbum[$key] = $value;
			}
		}
		return $arrAlbum;
	}


	/**
	 * Returns the information-array about a picture
	 * @param integer
	 * @param array
	 * @param string
	 * @return array
	 */
	protected function getPictureInformationArray($PictureId, $size = NULL, $ContentType)
	{
		if ($ContentType != 'fmd' && $ContentType != 'cte')
		{
			$strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
			__error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
		}
		if ($this->Template)
		{
			$this->Template->elementType = strtolower($ContentType);
			$this->Template->elementId = $this->id;
		}

		$objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')->execute($PictureId);

		//Alle Informationen zum Album in ein array packen
		$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($objPicture->pid);
		$arrAlbumInfo = $objAlbum->fetchAssoc();

		//Bild-Besitzer
		$objOwner = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objPicture->owner);

		$imageSrc = $objPicture->path . '/' . $objPicture->name;

		if ($size)
		{
			//Thumbnailbreite
			$size = unserialize($size);
			//Thumbnails generieren
			$thumbSrc = $this->getImage($imageSrc, $size[0], $size[1], $size[2]);
			$objFile = new File($thumbSrc);
			$arrFile["thumb_width"] = $objFile->width;
			$arrFile["thumb_height"] = $objFile->height;
		}

		if (is_file(TL_ROOT . '/' . $objPicture->path . '/' . $objPicture->name))
		{
			$objFile = new File($objPicture->path . '/' . $objPicture->name);
			if (!$objFile->isGdImage)
				return;
			$arrFile["filename"] = $objFile->filename;
			$arrFile["basename"] = $objFile->basename;
			$arrFile["dirname"] = $objFile->dirname;
			$arrFile["extension"] = $objFile->extension;
			$arrFile["image_width"] = $objFile->width;
			$arrFile["image_height"] = $objFile->height;
		}
		else
		{
			return;
		}

		//check if there is a custom thumbnail selected
		if (is_file(TL_ROOT . '/' . $objPicture->customThumb))
		{
			$objFile = new File($objPicture->customThumb);
			if ($objFile->isGdImage)
			{
				$thumbSrc = $objPicture->customThumb;
			}
		}

		//exif
		try
		{
			$exif = is_callable('exif_read_data') && TL_MODE == 'FE' ? @exif_read_data($src) : array('info' => "The function 'exif_read_data()' is not available on this server.");
		} catch(Exception $e)
		{
			$exif = array('info' => "The function 'exif_read_data()' is not available on this server.");
		}

		//video-integration
		$mediaSrc = trim($objPicture->socialMediaSRC) != "" ? trim($objPicture->socialMediaSRC) : "";
		$mediaSrc = trim($objPicture->localMediaSRC) != "" ? trim($objPicture->localMediaSRC) : $mediaSrc;

		$href = NULL;
		if (TL_MODE == 'FE' && $this->gc_fullsize)
		{
			$href = $mediaSrc != "" ? $mediaSrc : $imageSrc;
		}

		//cssID
		$cssID = deserialize($objPicture->cssID, true);

		$arrFilterSearch = array('"');
		$arrFilterReplace = array('');
		$arrPicture = array(
			//[int] id picture_id
			'id' => $objPicture->id,
			//[int] pid parent Album-Id
			'pid' => $objPicture->pid,
			//[int] das Datum, welches fuer das Bild gesetzt werden soll (= in der Regel das Upload-Datum)
			'date' => $objPicture->date,
			//[int] id des Albumbesitzers
			'owner' => $objPicture->owner,
			//Name des Erstellers
			'owners_name' => $objOwner->name,
			//[int] album_id oder pid
			'album_id' => $objPicture->pid,
			//[string] name (basename of the file)
			'name' => $objPicture->name,
			//[string] filename without extension
			'filename' => $arrFile["filename"],
			//[string] Pfad zur Datei
			'path' => $objPicture->path,
			//[string] basename similar to name
			'basename' => $arrFile["basename"],
			//[string] dirname
			'dirname' => $arrFile["dirname"],
			//[string] file-extension
			'extension' => $arrFile["extension"],
			//[string] Bildtitel
			'title' => $objPicture->title,
			//[string] Bildkommentar oder Bildbeschreibung
			'comment' => str_replace($arrFilterSearch, $arrFilterReplace, $objPicture->comment),
			'caption' => str_replace($arrFilterSearch, $arrFilterReplace, $objPicture->comment),
			//[string] path to media (video, picture, sound...)
			'href' => $href,
			//path to the image
			'image_src' => $imageSrc,
			//path to the other selected media
			'media_src' => $mediaSrc,
			//[string] path to a media on a social-media-plattform
			'socialMediaSRC' => $objPicture->socialMediaSRC,
			//[string] path to a media stored on the webserver
			'localMediaSRC' => $objPicture->localMediaSRC,
			//[string] Pfad zu einem benutzerdefinierten Thumbnail
			'addCustomThumb' => $objPicture->addCustomThumb,
			//[string] Thumbnailquelle
			'thumb_src' => $thumbSrc,
			//[int] thumb-width in px
			'thumb_width' => $arrFile["thumb_width"],
			//[int] thumb-height in px
			'thumb_height' => $arrFile["thumb_height"],
			//[int] image-width in px
			'image_width' => $arrFile["image_width"],
			//[int] image-height in px
			'image_height' => $arrFile["image_height"],
			//[int] das rel oder data-lightbox Attribut fuer das Anzeigen der Bilder in der Lightbox
			'lightbox' => $this->objPage->outputFormat == 'xhtml' ? 'rel="lightbox[lb' . $objPicture->pid . ']"' : 'data-lightbox="lb' . $objPicture->pid . '"',
			//[array] Thumbnail-Ausmasse Array $size[Breite, Hoehe, Methode]
			'size' => $size,
			//[int] Zeitstempel der letzten Aenderung
			'tstamp' => $objPicture->tstamp,
			//[int] Sortierindex
			'sorting' => $objPicture->sorting,
			//[boolean] veroeffentlicht (true/false)
			'published' => $objPicture->published,
			//[array] Array mit exif metatags
			'exif' => $exif,
			//[array] Array mit allen Albuminformation (albumname, owners_name...)
			'albuminfo' => $arrAlbumInfo,
			//[array] Array mit Bildinfos aus Meta-Text-File
			'metaFile' => $this->getMetaText($objPicture->id),
			//[string] css-ID des Bildcontainers
			'cssID' => $cssID[0] != '' ? $cssID[0] : '',
			//[string] css-Klasse des Bildcontainers
			'cssClass' => $cssID[1] != '' ? $cssID[1] : '',
			//[bool] true, wenn es sich um ein Bild handelt, das nicht in tl_files/gallery_creator_albums/albumname gespeichert ist
			'externalFile' => $objPicture->externalFile,
		);

		//Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_pictures erweitert wurde
		$objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')->execute($PictureId);
		foreach ($objPicture->fetchAssoc() as $key => $value)
		{
			if (!array_key_exists($key, $arrPicture))
			{
				$arrPicture[$key] = $value;
			}
		}

		return $arrPicture;
	}


	/**
	 * gets the meta-text-informations of a file
	 * @param integer
	 * @return array
	 */
	public function getMetaText($PictureId)
	{
		$objPicture = $this->Database->prepare("SELECT path, name FROM tl_gallery_creator_pictures WHERE id=?")->execute($PictureId);

		$file = $objPicture->path . '/' . $objPicture->name;
		if (is_file(TL_ROOT . "/" . $file))
		{
			$objFile = new File($file);
			$this->parseMetaFile(dirname($file), false);
			$arrMeta = $this->arrMeta[$objFile->basename];

			if ($arrMeta[0] == '')
			{
				$arrMeta[0] = str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename));
			}

			if ($objFile->isGdImage)
			{
				$metaText = array(
					'name' => $objFile->basename,
					'singleSRC' => $file,
					'alt' => $arrMeta[0],
					'imageUrl' => $arrMeta[1],
					'caption' => $arrMeta[2]
				);
			}
			return $metaText;
		}

		return $objPicture->comment;
	}


	/**
	 * Sets the template-vars for an album
	 * @param integer
	 * @param string
	 */
	protected function getAlbumTemplateVars($AlbumId, $ContentType)
	{
		if ($ContentType != 'fmd' && $ContentType != 'cte')
		{
			$strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
			__error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
		}
		//wichtig fuer Ajax-Anwendungen
		$this->Template->elementType = strtolower($ContentType);
		$this->Template->elementId = $this->id;

		$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($AlbumId);
		//die FMD/CTE-id
		$this->Template->fmdId = $this->id;
		//der back-Link
		$this->Template->backLink = $this->generateBackLink($ContentType, $AlbumId);
		//Der dem Bild uebergeordnete Albumname
		$this->Template->Albumname = $objAlbum->name;
		//Der Kommentar zum gew�hlten Album
		$this->Template->albumComment = $objAlbum->comment != "" ? nl2br($objAlbum->comment) : NULL;
		//Das Event-Datum des Albums als unix-timestamp
		$this->Template->eventTstamp = $objAlbum->date;
		//Das Event-Datum des Albums formatiert
		$this->Template->eventDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date);
		//Abstaende
		$this->Template->imagemargin = $this->generateMargin(unserialize($this->gc_imagemargin));
		//Anzahl Spalten pro Reihe
		$this->Template->colsPerRow = $this->gc_rows == "" ? 4 : $this->gc_rows;
		//Pfad zur xml-Ausgabe fuer jw_imagerotator
		$this->Template->jw_imagerotator_path = TL_MODE == 'FE' ? $this->generateFrontendUrl($this->objPage->row(), '/vars/' . $objAlbum->alias . '.jw_imagerotator') : NULL;

		//Ein paar Unterschiede, wenn das Modul ein Inhaltselement darstellt
		if ($ContentType == 'cte')
		{
			//Abstaende
			$this->Template->imagemargin = $this->generateMargin(unserialize($this->imagemargin));

			//Pfad zur xml-Ausgabe fuer jw_imagerotator
			if ($this->countGcContentElementsOnPage() > 1)
			{
				$this->Template->jw_imagerotator_path = TL_MODE == 'FE' ? $this->generateFrontendUrl($this->objPage->row(), '/vars/' . $this->id . '.' . $objAlbum->alias . '.jw_imagerotator') : NULL;
			}
		}

		//Macht alle Albumangaben im Array $this->Template->allAlbums verfuegbar
		$objAlbums = $this->Database->execute('SELECT * FROM tl_gallery_creator_albums ORDER BY sorting');
		$this->Template->allAlbums = $objAlbums->fetchAllAssoc();

		//Macht alle Bilder im Array $this->Template->allPictures verfuegbar
		$objPictures = $this->Database->execute('SELECT * FROM tl_gallery_creator_pictures ORDER BY pid, sorting');
		$this->Template->allPictures = $objPictures->fetchAllAssoc();
	}


	/**
	 * Hilfsmethode
	 * generiert den back-Link
	 * @param string
	 * @param int
	 * @return string
	 */
	public function generateBackLink($ContentType, $AlbumId)
	{
		if (TL_MODE == 'BE')
		{
			return false;
		}

		if ($ContentType == 'cte')
		{
			//Nur, wenn nicht automatisch zu overview weitergeleitet wurde, wird der back Link angezeigt
			if ($this->doRedirectOnSingleAlbum())
			{
				return NULL;
			}
		}
		//generiert den Link zum Parent-Album
		if ($this->gc_hierarchicalOutput && $this->GcHelpers->getParentAlbum($AlbumId))
		{
			$arrParentAlbum = $this->GcHelpers->getParentAlbum($AlbumId);
			return $this->generateFrontendUrl($this->objPage->row(), "/vars/" . $arrParentAlbum["alias"]);
		}
		//generiert den Link zur Startuebersicht
		return $this->generateFrontendUrl($this->objPage->row(), "");
	}


}
?>