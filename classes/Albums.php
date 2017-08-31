<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace GalleryCreator;


/**
 * Provide methods to get all events of a certain period from the database.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
abstract class Albums extends \Module
{
	/**
	 * Current URL
	 * @var string
	 */
	protected $strUrl;

	/**
	 * Current events
	 * @var array
	 */
	protected $arrAlbums = array();


	/**
	 * Current events
	 * @var array
	 */
	protected $arrPictures = array();

	/**
	 * @var string
	 */
	protected $defaultThumb = 'system/modules/gallery_creator/assets/images/image_not_found.jpg';


	/**
	 * Get all albums
	 *
	 * @param array $arrGalleries
	 * @param integer $intStart
	 * @param integer $intEnd
	 *
	 * @return array
	 */
	protected function getAllAlbums($arrGalleries)
	{
		if (!is_array($arrGalleries))
		{
			return array();
		}

		$this->arrAlbums = array();

		// Get albums from allowed archives
		$ids = implode(',', $arrGalleries);
		$strSorting = $this->gc_sorting == '' || $this->gc_sorting_direction == '' ? 'date DESC' : $this->gc_sorting . ' ' . $this->gc_sorting_direction;
		$objAlbums = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid IN(' . $ids . ') AND published=? ORDER BY ' . $strSorting)->execute('1');

		if ($objAlbums === null)
		{
			return array();
		}

		while ($objAlbums->next())
		{

			$objAlbum = \GalleryCreatorAlbumsModel::findByPk($objAlbums->id);
			if ($objAlbum !== null)
			{
				$strUrl = $this->strUrl;
				$objGallery = \GalleryCreatorGalleriesModel::findByPk($objAlbum->pid);

				// Get the current "jumpTo" page
				if ($objGallery !== null && $objGallery->jumpTo && ($objTarget = $objGallery->getRelated('jumpTo')) !== null)
				{
					/** @var \PageModel $objTarget */
					$strUrl = $objTarget->getFrontendUrl((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/albums/%s');
				}
				$this->addAlbum($objAlbum, $strUrl, $objAlbums->pid);
			}
		}


		// HOOK: modify the result set
		if (isset($GLOBALS['TL_HOOKS']['getAllGalleries']) && is_array($GLOBALS['TL_HOOKS']['getAllGalleries']))
		{
			foreach ($GLOBALS['TL_HOOKS']['getAllGalleries'] as $callback)
			{
				$this->import($callback[0]);
				$this->arrEvents = $this->{$callback[0]}->{$callback[1]}($this->arrAlbums, $arrGalleries, $this);
			}
		}

		return $this->arrAlbums;
	}


	/**
	 * @param $objAlbum
	 * @param $strUrl
	 * @param $galleryId
	 */
	protected function addAlbum($objAlbum, $strUrl, $galleryId)
	{
		global $objPage;
		// Get the page model
		$objPageModel = \PageModel::findByPk($objPage->id);

		$objPics = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=? AND published=?')->execute($objAlbum->id, '1');

		//Array Thumbnailbreite
		$arrSize = unserialize($this->gc_size_albumlisting);

		$href = null;
		if (TL_MODE == 'FE')
		{
			//generate the url as a formated string
			$href = $this->generateAlbumUrl($objAlbum, $strUrl);
		}

		$arrPreviewThumb = $this->getAlbumPreviewThumb($objAlbum->id);
		$strImageSrc = $arrPreviewThumb['path'];

		//Generate the thumbnails and the picture element
		try
		{
			$thumbSrc = \Image::create($strImageSrc, $arrSize)->executeResize()->getResizedPath();
			$picture = \Picture::create($strImageSrc, $arrSize)->getTemplateData();

			if ($thumbSrc !== $strImageSrc)
			{
				new \File(rawurldecode($thumbSrc), true);
			}
		} catch (\Exception $e)
		{
			\System::log('Image "' . $strImageSrc . '" could not be processed: ' . $e->getMessage(), __METHOD__, TL_ERROR);
		}

		$picture['alt'] = specialchars($objAlbum->name);
		$picture['title'] = specialchars($objAlbum->name);

		// CSS class
		$strCSSClass = $objPics->numRows < 1 ? ' empty-album' : '';

		$arrAlbum = array(
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
			'event_date' => \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date),
			//[string] Event-Location
			'event_location' => specialchars($objAlbum->event_location),
			//[string] Albumname
			'name' => specialchars($objAlbum->name),
			//[string] Albumalias (=Verzeichnisname)
			'alias' => $objAlbum->alias,
			//[string] Albumkommentar
			'comment' => $objPage->outputFormat == 'xhtml' ? \StringUtil::toXhtml(nl2br_xhtml($objAlbum->comment)) : \StringUtil::toHtml5(nl2br_html5($objAlbum->comment)),
			'caption' => $objPage->outputFormat == 'xhtml' ? \StringUtil::toXhtml(nl2br_xhtml($objAlbum->comment)) : \StringUtil::toHtml5(nl2br_html5($objAlbum->comment)),
			//[int] Albumbesucher (Anzahl Klicks)
			'visitors' => $objAlbum->visitors,
			//[string] Link zur Detailansicht
			'href' => $href,
			//[string] Inhalt fuer das title Attribut
			'title' => $objAlbum->name . ' [' . ($objPics->numRows ? $objPics->numRows . ' ' . $GLOBALS['TL_LANG']['gallery_creator']['pictures'] : '') . ($this->gc_hierarchicalOutput && $objSubAlbums->countSubalbums > 0 ? ' ' . $GLOBALS['TL_LANG']['gallery_creator']['contains'] . ' ' . $objSubAlbums->countSubalbums . '  ' . $GLOBALS['TL_LANG']['gallery_creator']['subalbums'] . ']' : ']'),
			//[int] Anzahl Bilder im Album
			'count' => $objPics->numRows,
			//[string] alt Attribut fuer das Vorschaubild
			'alt' => $arrPreviewThumb['name'],
			//[string] Pfad zum Originalbild
			'src' => TL_FILES_URL . $arrPreviewThumb['path'],
			//[string] Pfad zum Thumbnail
			'thumb_src' => TL_FILES_URL . \Image::get($arrPreviewThumb['path'], $arrSize[0], $arrSize[1], $arrSize[2]),
			//[int] article id
			'insert_article_pre' => $objAlbum->insert_article_pre ? $objAlbum->insert_article_pre : null,
			//[int] article id
			'insert_article_post' => $objAlbum->insert_article_post ? $objAlbum->insert_article_post : null,
			//[string] css-Classname
			'class' => 'thumb',
			//[int] Thumbnailgrösse
			'size' => $arrSize,
			//[string] javascript-Aufruf
			'thumbMouseover' => $this->gc_activateThumbSlider ? "objGalleryCreator.initThumbSlide(this," . $objAlbum->id . "," . $objPics->numRows . ");" : "",
			//[array] picture
			'picture' => $picture,
			//[string] cssClass
			'cssClass' => trim($strCSSClass),
		);

		// Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_albums erweitert wurde
		$objAlbum = \GalleryCreatorAlbumsModel::findByPk($intAlbumId);
		if ($objAlbum !== null)
		{
			$arrAlbum = array_merge($objAlbum->row(), $arrAlbum);
		}
		$this->arrAlbums[] = $arrAlbum;
	}

	/**
	 * @param $objPicture
	 * @param $galleryId
	 * @return null
	 */
	protected function addPicture($objPicture, $galleryId)
	{

		global $objPage;

		$hasCustomThumb = false;


		$defaultThumbSRC = $this->defaultThumb;
		if (\Config::get('gc_error404_thumb') !== '')
		{
			$objFile = \FilesModel::findByUuid(\Config::get('gc_error404_thumb'));
			if ($objFile !== null)
			{
				if (\Validator::isUuid(\Config::get('gc_error404_thumb')))
				{
					if (is_file(TL_ROOT . '/' . $objFile->path))
					{
						$defaultThumbSRC = $objFile->path;
					}
				}
			}
		}

		// Get the page model
		$objPageModel = \PageModel::findByPk($objPage->id);


		//Alle Informationen zum Album in ein array packen
		$objAlbum = $objPicture->getRelated('pid');
		if ($objAlbum === null)
		{
			return;
		}

		$arrAlbumInfo = $objAlbum->row();

		//Bild-Besitzer
		$objOwner = \UserModel::findByPk($objPicture->owner);
		if ($objOwner === null)
		{
			$imageOwner = '';
		}
		else
		{
			$imageOwner = $objOwner->name;
		}
		$arrMeta = array();
		$objFileModel = \FilesModel::findByUuid($objPicture->uuid);
		if ($objFileModel == null)
		{
			$strImageSrc = $defaultThumbSRC;
		}
		else
		{
			$strImageSrc = $objFileModel->path;
			if (!is_file(TL_ROOT . '/' . $strImageSrc))
			{
				// Fallback to the default thumb
				$strImageSrc = $defaultThumbSRC;
			}

			//meta
			$arrMeta = $this->getMetaData($objFileModel->meta, $objPage->language);
			// Use the file name as title if none is given
			if ($arrMeta['title'] == '')
			{
				$arrMeta['title'] = specialchars($objFileModel->name);
			}
		}


		// get thumb dimensions
		$arrSize = unserialize($this->gc_size_detailview);

		//Generate the thumbnails and the picture element
		try
		{
			$thumbSrc = \Image::create($strImageSrc, $arrSize)->executeResize()->getResizedPath();
			// overwrite $thumbSrc if there is a valid custom thumb
			if ($objPicture->addCustomThumb && !empty($objPicture->customThumb))
			{
				$customThumbModel = \FilesModel::findByUuid($objPicture->customThumb);
				if ($customThumbModel !== null)
				{
					if (is_file(TL_ROOT . '/' . $customThumbModel->path))
					{
						$objFileCustomThumb = new \File($customThumbModel->path, true);
						if ($objFileCustomThumb->isGdImage)
						{
							$thumbSrc = \Image::create($objFileCustomThumb->path, $arrSize)->executeResize()->getResizedPath();
							$hasCustomThumb = true;
						}
					}
				}
			}
			$thumbPath = $hasCustomThumb ? $objFileCustomThumb->path : $strImageSrc;
			$picture = \Picture::create($thumbPath, $arrSize)->getTemplateData();

		} catch (\Exception $e)
		{
			\System::log('Image "' . $strImageSrc . '" could not be processed: ' . $e->getMessage(), __METHOD__, TL_ERROR);
			$thumbSrc = '';
			$picture = array('img' => array('src' => '', 'srcset' => ''), 'sources' => array());
		}

		$picture['alt'] = $objPicture->title != '' ? specialchars($objPicture->title) : specialchars($arrMeta['title']);
		$picture['title'] = $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(\StringUtil::toXhtml($objPicture->comment)) : specialchars(\StringUtil::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']);


		$objFileThumb = new \File(rawurldecode($thumbSrc));
		$arrSize[0] = $objFileThumb->width;
		$arrSize[1] = $objFileThumb->height;
		$arrFile["thumb_width"] = $objFileThumb->width;
		$arrFile["thumb_height"] = $objFileThumb->height;

		// get some image params
		if (is_file(TL_ROOT . '/' . $strImageSrc))
		{
			$objFileImage = new \File($strImageSrc);
			if (!$objFileImage->isGdImage)
			{
				return null;
			}
			$arrFile["path"] = $objFileImage->path;
			$arrFile["basename"] = $objFileImage->basename;
			// filename without extension
			$arrFile["filename"] = $objFileImage->filename;
			$arrFile["extension"] = $objFileImage->extension;
			$arrFile["dirname"] = $objFileImage->dirname;
			$arrFile["image_width"] = $objFileImage->width;
			$arrFile["image_height"] = $objFileImage->height;
		}
		else
		{
			return null;
		}


		//exif
		if ($GLOBALS['TL_CONFIG']['gc_read_exif'])
		{
			try
			{
				$exif = is_callable('exif_read_data') && TL_MODE == 'FE' ? exif_read_data($strImageSrc) : array('info' => "The function 'exif_read_data()' is not available on this server.");
			} catch (Exception $e)
			{
				$exif = array('info' => "The function 'exif_read_data()' is not available on this server.");
			}
		}
		else
		{
			$exif = array('info' => "The function 'exif_read_data()' has not been activated in the Contao backend settings.");
		}

		//video-integration
		$strMediaSrc = trim($objPicture->socialMediaSRC) != "" ? trim($objPicture->socialMediaSRC) : "";
		if (\Validator::isUuid($objPicture->localMediaSRC))
		{
			//get path of a local Media
			$objMovieFile = \FilesModel::findById($objPicture->localMediaSRC);
			$strMediaSrc = $objMovieFile !== null ? $objMovieFile->path : $strMediaSrc;
		}
		$href = null;
		if (TL_MODE == 'FE' && $this->gc_fullsize)
		{
			$href = $strMediaSrc != "" ? $strMediaSrc : \System::urlEncode($strImageSrc);
		}

		//cssID
		$cssID = deserialize($objPicture->cssID, true);

		// build the array
		$arrPicture = array(
			'id' => $objPicture->id,
			//[int] pid parent Album-Id
			'pid' => $objPicture->pid,
			//[int] das Datum, welches fuer das Bild gesetzt werden soll (= in der Regel das Upload-Datum)
			'date' => $objPicture->date,
			//[int] id des Albumbesitzers
			'owner' => $objPicture->owner,
			//Name des Erstellers
			'owners_name' => $imageOwner,
			//[int] album_id oder pid
			'album_id' => $objPicture->pid,
			//[string] name (basename/filename of the file)
			'name' => specialchars($arrFile["basename"]),
			//[string] filename without extension
			'filename' => $arrFile["filename"],
			//[string] Pfad zur Datei
			'uuid' => $objPicture->uuid,
			// uuid of the image
			'path' => $arrFile["path"],
			//[string] basename similar to name
			'basename' => $arrFile["basename"],
			//[string] dirname
			'dirname' => $arrFile["dirname"],
			//[string] file-extension
			'extension' => $arrFile["extension"],
			//[string] alt-attribut
			'alt' => $objPicture->title != '' ? specialchars($objPicture->title) : specialchars($arrMeta['title']),
			//[string] title-attribut
			'title' => $objPicture->title != '' ? specialchars($objPicture->title) : specialchars($arrMeta['title']),
			//[string] Bildkommentar oder Bildbeschreibung
			'comment' => $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(\StringUtil::toXhtml($objPicture->comment)) : specialchars(\StringUtil::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']),
			'caption' => $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(\StringUtil::toXhtml($objPicture->comment)) : specialchars(\StringUtil::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']),
			//[string] path to media (video, picture, sound...)
			'href' => TL_FILES_URL . $href,
			// single image url
			'single_image_url' => $objPageModel->getFrontendUrl(($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/albums/') . \Input::get('albums') . '/img/' . $arrFile["filename"], $objPage->language),
			//[string] path to the image,
			'image_src' => $arrFile["path"],
			//[string] path to the other selected media
			'media_src' => $strMediaSrc,
			//[string] path to a media on a social-media-plattform
			'socialMediaSRC' => $objPicture->socialMediaSRC,
			//[string] path to a media stored on the webserver
			'localMediaSRC' => $objPicture->localMediaSRC,
			//[string] Pfad zu einem benutzerdefinierten Thumbnail
			'addCustomThumb' => $objPicture->addCustomThumb,
			//[string] Thumbnailquelle
			'thumb_src' => isset($thumbSrc) ? TL_FILES_URL . $thumbSrc : '',
			//[array] Thumbnail-Ausmasse Array $arrSize[Breite, Hoehe, Methode]
			'size' => $arrSize,
			//[int] thumb-width in px
			'thumb_width' => $arrFile["thumb_width"],
			//[int] thumb-height in px
			'thumb_height' => $arrFile["thumb_height"],
			//[int] image-width in px
			'image_width' => $arrFile["image_width"],
			//[int] image-height in px
			'image_height' => $arrFile["image_height"],
			//[int] das rel oder data-lightbox Attribut fuer das Anzeigen der Bilder in der Lightbox
			'lightbox' => $objPage->outputFormat == 'xhtml' ? 'rel="lightbox[lb' . $objPicture->pid . ']"' : 'data-lightbox="lb' . $objPicture->pid . '"',
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
			//[array] Array mit Bildinfos aus den meta-Angaben der Datei, gespeichert in tl_files.meta
			'metaData' => $arrMeta,
			//[string] css-ID des Bildcontainers
			'cssID' => $cssID[0] != '' ? $cssID[0] : '',
			//[string] css-Klasse des Bildcontainers
			'cssClass' => $cssID[1] != '' ? $cssID[1] : '',
			//[bool] true, wenn es sich um ein Bild handelt, das nicht in files/gallery_creator_albums/albumname gespeichert ist
			'externalFile' => $objPicture->externalFile,
			// [array] picture
			'picture' => $picture
		);

		//Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_pictures erweitert wurde
		$arrPicture = array_merge($objPicture->row(), $arrPicture);

		$this->arrPictures[$objPicture->id] = $arrPicture;

	}


	/**
	 * Generate a URL and return it as string
	 *
	 * @param \GalleryCreatorAlbumsModel $objEvent
	 * @param string $strUrl
	 *
	 * @return string
	 */
	protected function generateAlbumUrl($objAlbum, $strUrl)
	{
		switch ($objAlbum->source)
		{
			// Link to an external page
			case 'external':
				if (substr($objAlbum->url, 0, 7) == 'mailto:')
				{
					return \StringUtil::encodeEmail($objAlbum->url);
				}
				else
				{
					return ampersand($objAlbum->url);
				}
				break;

			// Link to an internal page
			case 'internal':
				if (($objTarget = $objAlbum->getRelated('jumpTo')) !== null)
				{
					/** @var \PageModel $objTarget */
					return ampersand($objTarget->getFrontendUrl());
				}
				break;

			// Link to an article
			case 'article':
				if (($objArticle = \ArticleModel::findByPk($objAlbum->articleId, array('eager' => true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null)
				{
					/** @var \PageModel $objPid */
					return ampersand($objPid->getFrontendUrl('/articles/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
				}
				break;
		}

		// Link to the default page
		return ampersand(sprintf($strUrl, ((!\Config::get('disableAlias') && $objAlbum->alias != '') ? $objAlbum->alias : $objAlbum->id)));
	}

	/**
	 * Returns the path to the preview-thumbnail of an album
	 * @param $intAlbumId
	 * @return array
	 */
	protected function getAlbumPreviewThumb($intAlbumId)
	{

		$thumbSRC = $this->defaultThumb;

		// Check for an alternate thumbnail
		if (\Config::get('gc_error404_thumb') !== '')
		{
			$objFile = \FilesModel::findByUuid(\Config::get('gc_error404_thumb'));
			if ($objFile !== null)
			{
				if (\Validator::isUuid(\Config::get('gc_error404_thumb')))
				{
					if (is_file(TL_ROOT . '/' . $objFile->path))
					{
						$thumbSRC = $objFile->path;
					}
				}
			}
		}

		// Predefine thumb
		$arrThumb = array(
			'name' => basename($thumbSRC),
			'path' => $thumbSRC
		);

		$objAlb = \GalleryCreatorAlbumsModel::findByPk($intAlbumId);
		if ($objAlb->thumb !== null)
		{
			$objPreviewThumb = \GalleryCreatorPicturesModel::findByPk($objAlb->thumb);
		}
		else
		{
			$objPreviewThumb = \GalleryCreatorPicturesModel::findOneByPid($intAlbumId);
		}

		if ($objPreviewThumb !== null)
		{
			$oFile = \FilesModel::findByUuid($objPreviewThumb->uuid);
			if ($oFile !== null)
			{
				if (is_file(TL_ROOT . '/' . $oFile->path))
				{
					$arrThumb = array(
						'name' => basename($oFile->path),
						'path' => $oFile->path
					);
				}
			}
		}

		return $arrThumb;
	}

	/**
	 * Sort out protected archives
	 *
	 * @param array $arrCalendars
	 *
	 * @return array
	 */
	protected function sortOutProtected($arrGalleries)
	{
		if (BE_USER_LOGGED_IN || !is_array($arrGalleries) || empty($arrGalleries))
		{
			return $arrGalleries;
		}

		$this->import('FrontendUser', 'User');
		$objGallery = \GalleryCreatorGalleriesModel::findMultipleByIds($arrGalleries);
		$arrCalendars = array();

		if ($objGallery !== null)
		{
			while ($objGallery->next())
			{
				if ($objGallery->protected)
				{
					if (!FE_USER_LOGGED_IN)
					{
						continue;
					}

					$groups = deserialize($objGallery->groups);

					if (!is_array($groups) || empty($groups) || count(array_intersect($groups, $this->User->groups)) < 1)
					{
						continue;
					}
				}

				$arrCalendars[] = $objGallery->id;
			}
		}

		return $arrCalendars;
	}

	/**
	 * Set the template-vars to the template object for the selected album
	 * @param $intAlbumId
	 */
	protected function getAlbumTemplateVars($objAlbum)
	{

		global $objPage;

		// add meta tags to the page object
		if (TL_MODE == 'FE' && $this->viewMode == 'detail_view')
		{
			$objPage->description = $objAlbum->description != '' ? specialchars($objAlbum->description) : $objPage->description;
			$GLOBALS['TL_KEYWORDS'] = ltrim($GLOBALS['TL_KEYWORDS'] . ',' . specialchars($objAlbum->keywords), ',');
		}

		//store all album-data in the array
		foreach ($objAlbum->row() as $k => $v)
		{
			$this->Template->arrAlbumdata = $objAlbum->row();
		}

		// store the data of the current album in the session
		//$_SESSION['gallery_creator']['CURRENT_ALBUM'] = $this->Template->arrAlbumdata;
		//der back-Link
		//$this->Template->backLink = $this->generateBackLink($intAlbumId);
		//Der dem Bild uebergeordnete Albumname
		$this->Template->Albumname = $objAlbum->name;
		// Albumbesucher (Anzahl Klicks)
		$this->Template->visitors = $objAlbum->vistors;
		//Der Kommentar zum gewaehlten Album
		$this->Template->albumComment = $objPage->outputFormat == 'xhtml' ? \StringUtil::toXhtml($objAlbum->comment) : \StringUtil::toHtml5($objAlbum->comment);
		// In der Detailansicht kann optional ein Artikel vor dem Album hinzugefuegt werden
		$this->Template->insertArticlePre = $objAlbum->insert_article_pre ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_pre) : null;
		// In der Detailansicht kann optional ein Artikel nach dem Album hinzugefuegt werden
		$this->Template->insertArticlePost = $objAlbum->insert_article_post ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_post) : null;
		//Das Event-Datum des Albums als unix-timestamp
		$this->Template->eventTstamp = $objAlbum->date;
		//Das Event-Datum des Albums formatiert
		$this->Template->eventDate = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date);
		//Abstaende
		$this->Template->imagemargin = $this->generateMargin(deserialize($this->gc_imagemargin), 'margin');
		//Anzahl Spalten pro Reihe
		$this->Template->colsPerRow = $this->gc_rows == "" ? 4 : $this->gc_rows;

		$this->Template->objModule = $this;
	}

	/**
	 * initCounter
	 *
	 * @param integer
	 * @return string
	 */
	protected function initCounter($objAlbum)
	{

		if (preg_match('/bot|sp[iy]der|crawler|lib(?:cur|www)|search|archive/i', $_SERVER['HTTP_USER_AGENT']))
		{
			// do not count spiders/bots
			return;
		}

		if (TL_MODE == 'FE')
		{

			if (strpos($objAlbum->visitors_details, $_SERVER['REMOTE_ADDR']))
			{
				// return if the visitor is allready registered
				return;
			}


			$arrVisitors = deserialize($objAlbum->visitors_details, true);
			// keep visiors data in the db unless 50 other users have visited the album
			if (count($arrVisitors) == 50)
			{
				// slice the last position
				$arrVisitors = array_slice($arrVisitors, 0, count($arrVisitors) - 1);
			}


			//build up the array
			$newVisitor = array(
				$_SERVER['REMOTE_ADDR'] => array(
					'ip' => $_SERVER['REMOTE_ADDR'],
					'pid' => $intAlbumId,
					'user_agent' => $_SERVER['HTTP_USER_AGENT'],
					'tstamp' => time(),
					'url' => \Environment::get('request'),
				)
			);

			if (!empty($arrVisitors))
			{
				// insert the element to the beginning of the array
				array_unshift($arrVisitors, $newVisitor);
			}
			else
			{
				$arrVisitors[] = array($_SERVER['REMOTE_ADDR'] => $newVisitor);
			}

			// update database
			$objAlbum->visitors = $objAlbum->visitors += 1;
			$objAlbum->visitors_details = serialize($arrVisitors);
			$objAlbum->save();

		}
	}


}
