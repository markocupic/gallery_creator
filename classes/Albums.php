<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Markocupic\GalleryCreator;


use Contao\Input;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\GalleryCreatorGalleriesModel;
use Contao\GalleryCreatorAlbumsModel;
use Contao\GalleryCreatorPicturesModel;
use Contao\FilesModel;
use Contao\Dbafs;
use Contao\FileUpload;
use Contao\Message;
use Contao\BackendUser;
use Contao\Database;
use Contao\Frontend;
use Contao\PageModel;
use Contao\Image;
use Contao\Picture;
use Contao\File;
use Contao\System;
use Contao\Date;
use Contao\StringUtil;
use Contao\Validator;
use Contao\UserModel;
use Contao\ArticleModel;


/**
 * Class Albums
 * @package GalleryCreator
 */
class Albums extends Frontend
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

            $objAlbum = GalleryCreatorAlbumsModel::findByPk($objAlbums->id);
            if ($objAlbum !== null)
            {
                $strUrl = $this->strUrl;
                $objGallery = GalleryCreatorGalleriesModel::findByPk($objAlbum->pid);

                // Get the current "jumpTo" page
                if ($objGallery !== null && $objGallery->jumpTo && ($objTarget = $objGallery->getRelated('jumpTo')) !== null)
                {
                    /** @var PageModel $objTarget */
                    $strUrl = $objTarget->getFrontendUrl((Config::get('useAutoItem') && !Config::get('disableAlias')) ? '/%s' : '/albums/%s');
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
        $objPageModel = PageModel::findByPk($objPage->id);

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
            $thumbSrc = Image::create($strImageSrc, $arrSize)->executeResize()->getResizedPath();
            $picture = Picture::create($strImageSrc, $arrSize)->getTemplateData();

            if ($thumbSrc !== $strImageSrc)
            {
                new File(rawurldecode($thumbSrc), true);
            }
        } catch (\Exception $e)
        {
            System::log('Image "' . $strImageSrc . '" could not be processed: ' . $e->getMessage(), __METHOD__, TL_ERROR);
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
            'event_date' => Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date),
            //[string] Event-Location
            'event_location' => specialchars($objAlbum->event_location),
            //[string] Albumname
            'name' => specialchars($objAlbum->name),
            //[string] Albumalias (=Verzeichnisname)
            'alias' => $objAlbum->alias,
            //[string] Albumkommentar
            'comment' => $objPage->outputFormat == 'xhtml' ? StringUtil::toXhtml(nl2br_xhtml($objAlbum->comment)) : StringUtil::toHtml5(nl2br_html5($objAlbum->comment)),
            'caption' => $objPage->outputFormat == 'xhtml' ? StringUtil::toXhtml(nl2br_xhtml($objAlbum->comment)) : StringUtil::toHtml5(nl2br_html5($objAlbum->comment)),
            //[int] Albumbesucher (Anzahl Klicks)
            'visitors' => $objAlbum->visitors,
            //[string] Link zur Detailansicht
            'href' => $href,
            //[string] Inhalt fuer das title Attribut
            'title' => sprintf('%s [%s %s]', $objAlbum->name, $objPics->numRows, $GLOBALS['TL_LANG']['gallery_creator']['pictures']),
			//[int] Anzahl Bilder im Album
			'count' => $objPics->numRows,
			//[string] alt Attribut fuer das Vorschaubild
			'alt' => $arrPreviewThumb['name'],
			//[string] Pfad zum Originalbild
			'src' => TL_FILES_URL . $arrPreviewThumb['path'],
			//[string] Pfad zum Thumbnail
			'thumb_src' => TL_FILES_URL . Image::get($arrPreviewThumb['path'], $arrSize[0], $arrSize[1], $arrSize[2]),
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
		$objAlbum = GalleryCreatorAlbumsModel::findByPk($objAlbum->id);
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
        if (Config::get('gc_error404_thumb') !== '')
        {
            $objFile = FilesModel::findByUuid(Config::get('gc_error404_thumb'));
            if ($objFile !== null)
            {
                if (Validator::isUuid(Config::get('gc_error404_thumb')))
                {
                    if (is_file(TL_ROOT . '/' . $objFile->path))
                    {
                        $defaultThumbSRC = $objFile->path;
                    }
                }
            }
        }

        // Get the page model
        $objPageModel = PageModel::findByPk($objPage->id);


        //Alle Informationen zum Album in ein array packen
        $objAlbum = $objPicture->getRelated('pid');
        if ($objAlbum === null)
        {
            return;
        }

        $arrAlbumInfo = $objAlbum->row();

        //Bild-Besitzer
        $objOwner = UserModel::findByPk($objPicture->owner);
        if ($objOwner === null)
        {
            $imageOwner = '';
        }
        else
        {
            $imageOwner = $objOwner->name;
        }
        $arrMeta = array();
        $objFileModel = FilesModel::findByUuid($objPicture->uuid);
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
            $thumbSrc = Image::create($strImageSrc, $arrSize)->executeResize()->getResizedPath();
            // overwrite $thumbSrc if there is a valid custom thumb
            if ($objPicture->addCustomThumb && !empty($objPicture->customThumb))
            {
                $customThumbModel = FilesModel::findByUuid($objPicture->customThumb);
                if ($customThumbModel !== null)
                {
                    if (is_file(TL_ROOT . '/' . $customThumbModel->path))
                    {
                        $objFileCustomThumb = new File($customThumbModel->path, true);
                        if ($objFileCustomThumb->isGdImage)
                        {
                            $thumbSrc = Image::create($objFileCustomThumb->path, $arrSize)->executeResize()->getResizedPath();
                            $hasCustomThumb = true;
                        }
                    }
                }
            }
            $thumbPath = $hasCustomThumb ? $objFileCustomThumb->path : $strImageSrc;
            $picture = Picture::create($thumbPath, $arrSize)->getTemplateData();

        } catch (\Exception $e)
        {
            System::log('Image "' . $strImageSrc . '" could not be processed: ' . $e->getMessage(), __METHOD__, TL_ERROR);
            $thumbSrc = '';
            $picture = array('img' => array('src' => '', 'srcset' => ''), 'sources' => array());
        }

        $picture['alt'] = $objPicture->title != '' ? specialchars($objPicture->title) : specialchars($arrMeta['title']);
        $picture['title'] = $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(StringUtil::toXhtml($objPicture->comment)) : specialchars(StringUtil::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']);


        $objFileThumb = new File(rawurldecode($thumbSrc));
        $arrSize[0] = $objFileThumb->width;
        $arrSize[1] = $objFileThumb->height;
        $arrFile["thumb_width"] = $objFileThumb->width;
        $arrFile["thumb_height"] = $objFileThumb->height;

        // get some image params
        if (is_file(TL_ROOT . '/' . $strImageSrc))
        {
            $objFileImage = new File($strImageSrc);
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
            } catch (\Exception $e)
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
        if (Validator::isUuid($objPicture->localMediaSRC))
        {
            //get path of a local Media
            $objMovieFile = FilesModel::findById($objPicture->localMediaSRC);
            $strMediaSrc = $objMovieFile !== null ? $objMovieFile->path : $strMediaSrc;
        }
        $href = null;
        if (TL_MODE == 'FE' && $this->gc_fullsize)
        {
            $href = $strMediaSrc != "" ? $strMediaSrc : System::urlEncode($strImageSrc);
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
            'comment' => $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(StringUtil::toXhtml($objPicture->comment)) : specialchars(StringUtil::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']),
            'caption' => $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(StringUtil::toXhtml($objPicture->comment)) : specialchars(StringUtil::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']),
            //[string] path to media (video, picture, sound...)
            'href' => TL_FILES_URL . $href,
            // single image url
            'single_image_url' => $objPageModel->getFrontendUrl(($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/albums/') . Input::get('albums') . '/img/' . $arrFile["filename"], $objPage->language),
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
     * @param GalleryCreatorAlbumsModel $objEvent
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
                    return StringUtil::encodeEmail($objAlbum->url);
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
                    /** @var PageModel $objTarget */
                    return ampersand($objTarget->getFrontendUrl());
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = ArticleModel::findByPk($objAlbum->articleId, array('eager' => true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null)
                {
                    /** @var PageModel $objPid */
                    return ampersand($objPid->getFrontendUrl('/articles/' . ((!Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
                }
                break;
        }

        // Link to the default page
        return ampersand(sprintf($strUrl, ((!Config::get('disableAlias') && $objAlbum->alias != '') ? $objAlbum->alias : $objAlbum->id)));
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
        if (Config::get('gc_error404_thumb') !== '')
        {
            $objFile = FilesModel::findByUuid(Config::get('gc_error404_thumb'));
            if ($objFile !== null)
            {
                if (Validator::isUuid(Config::get('gc_error404_thumb')))
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

        $objAlb = GalleryCreatorAlbumsModel::findByPk($intAlbumId);
        if ($objAlb->thumb !== null)
        {
            $objPreviewThumb = GalleryCreatorPicturesModel::findByPk($objAlb->thumb);
        }
        else
        {
            $objPreviewThumb = GalleryCreatorPicturesModel::findOneByPid($intAlbumId);
        }

        if ($objPreviewThumb !== null)
        {
            $oFile = FilesModel::findByUuid($objPreviewThumb->uuid);
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
        $objGallery = GalleryCreatorGalleriesModel::findMultipleByIds($arrGalleries);
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
        $this->Template->albumComment = $objPage->outputFormat == 'xhtml' ? StringUtil::toXhtml($objAlbum->comment) : StringUtil::toHtml5($objAlbum->comment);
        // In der Detailansicht kann optional ein Artikel vor dem Album hinzugefuegt werden
        $this->Template->insertArticlePre = $objAlbum->insert_article_pre ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_pre) : null;
        // In der Detailansicht kann optional ein Artikel nach dem Album hinzugefuegt werden
        $this->Template->insertArticlePost = $objAlbum->insert_article_post ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_post) : null;
        //Das Event-Datum des Albums als unix-timestamp
        $this->Template->eventTstamp = $objAlbum->date;
        //Das Event-Datum des Albums formatiert
        $this->Template->eventDate = Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date);
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
                    'pid' => $objAlbum->id,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'tstamp' => time(),
                    'url' => Environment::get('request'),
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

    /**
     * Insert a new entry in tl_gallery_creator_pictures
     *
     * @param integer
     * @param string
     * $intAlbumId - albumId
     * $strFilepath - filepath -> files/gallery_creator_albums/albumalias/filename.jpg
     * @return bool
     */
    public static function createNewImage($intAlbumId, $strFilepath)
    {
        // Get the file-object
        $objFile = new File($strFilepath);
        if (!$objFile->isGdImage)
        {
            return false;
        }

        // Get the album-object
        $objAlbum = GalleryCreatorAlbumsModel::findById($intAlbumId);

        // Get the assigned album directory
        $objFolder = FilesModel::findByUuid($objAlbum->assignedDir);
        $assignedDir = null;
        if ($objFolder !== null)
        {
            if (is_dir(TL_ROOT . '/' . $objFolder->path))
            {
                $assignedDir = $objFolder->path;
            }
        }
        if ($assignedDir == null)
        {
            die('Aborted Script, because there is no upload directory assigned to the Album with ID ' . $intAlbumId);
        }

        // Check if the file is stored in the album-directory or if it is stored in an external directory
        $blnExternalFile = false;
        if (Input::get('importFromFilesystem'))
        {
            $blnExternalFile = strstr($objFile->dirname, $assignedDir) ? false : true;
        }

        // Get the album object and the alias
        $strAlbumAlias = $objAlbum->alias;
        // Db insert
        $objImg = new GalleryCreatorPicturesModel();
        $objImg->tstamp = time();
        $objImg->pid = $objAlbum->id;
        $objImg->externalFile = $blnExternalFile ? "1" : "";
        $objImg->save();

        if ($objImg->id)
        {
            $insertId = $objImg->id;
            // Get the next sorting index
            $objImg_2 = Database::getInstance()
                ->prepare('SELECT MAX(sorting)+10 AS maximum FROM tl_gallery_creator_pictures WHERE pid=?')
                ->execute($objAlbum->id);
            $sorting = $objImg_2->maximum;

            // If filename should be generated
            if (!$objAlbum->preserve_filename && $blnExternalFile === false)
            {
                $newFilepath = sprintf('%s/alb%s_img%s.%s', $assignedDir, $objAlbum->id, $insertId,
                    $objFile->extension);
                $objFile->renameTo($newFilepath);
            }


            if (is_file(TL_ROOT . '/' . $objFile->path))
            {
                // Get the userId
                $userId = '0';
                if (TL_MODE == 'BE')
                {
                    $userId = BackendUser::getInstance()->id;
                }

                // The album-owner is automaticaly the image owner, if the image was uploaded by a by a frontend user
                if (TL_MODE == 'FE')
                {
                    $userId = $objAlbum->owner;
                }

                // Get the FilesModel
                $objFileModel = FilesModel::findByPath($objFile->path);

                // Finally save the new image in tl_gallery_creator_pictures
                $objPicture = GalleryCreatorPicturesModel::findByPk($insertId);
                $objPicture->uuid = $objFileModel->uuid;
                $objPicture->owner = $userId;
                $objPicture->date = $objAlbum->date;
                $objPicture->sorting = $sorting;
                $objPicture->save();

                System::log('A new version of tl_gallery_creator_pictures ID ' . $insertId . ' has been created',
                    __METHOD__, TL_GENERAL);

                // Check for a valid preview-thumb for the album
                $objAlbum = GalleryCreatorAlbumsModel::findByAlias($strAlbumAlias);
                if ($objAlbum !== null)
                {
                    if ($objAlbum->thumb == "")
                    {
                        $objAlbum->thumb = $insertId;
                        $objAlbum->save();
                    }
                }

                // GalleryCreatorImagePostInsert - HOOK
                if (isset($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']) && is_array($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert'] as $callback)
                    {
                        $objClass = self::importStatic($callback[0]);
                        $objClass->$callback[1]($insertId);
                    }
                }

                return true;
            }
            else
            {
                if ($blnExternalFile === true)
                {
                    $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file'],
                        $strFilepath);
                }
                else
                {
                    $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['uploadError'], $strFilepath);
                }
                System::log('Unable to create the new image in: ' . $strFilepath . '!', __METHOD__, TL_ERROR);
            }

        }

        return false;
    }

    /**
     * Move uploaded file to the album directory
     *
     * @param $intAlbumId
     * @param string $strName
     * @return array
     */
    public static function fileupload($intAlbumId, $strName = 'file')
    {

        $blnIsError = false;

        // Get the album object
        $objAlb = GalleryCreatorAlbumsModel::findById($intAlbumId);
        if ($objAlb === null)
        {
            $blnIsError = true;
            Message::addError('Album with ID ' . $intAlbumId . ' does not exist.');
        }

        // Check for a valid upload directory
        $objUploadDir = FilesModel::findByUuid($objAlb->assignedDir);
        if ($objUploadDir === null || !is_dir(TL_ROOT . '/' . $objUploadDir->path))
        {
            $blnIsError = true;
            Message::addError('No upload directory defined in the album settings!');
        }

        // Check if there are some files in $_FILES
        if (!is_array($_FILES[$strName]))
        {
            $blnIsError = true;
            Message::addError('No Files selected for the uploader.');
        }

        if ($blnIsError)
        {
            return array();
        }


        // Do not overwrite files of the same filename
        $intCount = count($_FILES[$strName]['name']);
        for ($i = 0; $i < $intCount; $i++)
        {
            if (strlen($_FILES[$strName]['name'][$i]))
            {
                // Generate unique filename
                $_FILES[$strName]['name'][$i] = basename(self::generateUniqueFilename($objUploadDir->path . '/' . $_FILES[$strName]['name'][$i]));
            }
        }

        // Resize image if feature is enabled
        if (Input::post('img_resolution') > 1)
        {
            Config::set('imageWidth', Input::post('img_resolution'));
            Config::set('jpgQuality', Input::post('img_quality'));
        }
        else
        {
            Config::set('maxImageWidth', 999999999);
        }

        // Call the Contao FileUpload class
        $objUpload = new FileUpload();
        $objUpload->setName($strName);
        $arrUpload = $objUpload->uploadTo($objUploadDir->path);

        foreach ($arrUpload as $strFileSrc)
        {
            // Store file in tl_files
            Dbafs::addResource($strFileSrc);
        }

        return $arrUpload;
    }

    /**
     * generate a unique filepath for a new picture
     * @param $strFilename
     * @return bool|string
     * @throws Exception
     */
    public static function generateUniqueFilename($strFilename)
    {

        $strFilename = strip_tags($strFilename);
        $strFilename = utf8_romanize($strFilename);
        $strFilename = str_replace('"', '', $strFilename);
        $strFilename = str_replace(' ', '_', $strFilename);

        if (preg_match('/\.$/', $strFilename))
        {
            throw new Exception($GLOBALS['TL_LANG']['ERR']['invalidName']);
        }
        $pathinfo = pathinfo($strFilename);
        $extension = $pathinfo['extension'];
        $basename = basename($strFilename, '.' . $extension);
        $dirname = dirname($strFilename);

        // Falls Datei schon existiert, wird hinten eine Zahl mit fuehrenden Nullen angehaengt -> filename0001.jpg
        $i = 0;
        $isUnique = false;
        do
        {
            $i++;
            if (!file_exists(TL_ROOT . '/' . $dirname . '/' . $basename . '.' . $extension))
            {
                //exit loop when filename is unique
                return $dirname . '/' . $basename . '.' . $extension;
            }
            else
            {
                if ($i != 1)
                {
                    $filename = substr($basename, 0, -5);
                }
                else
                {
                    $filename = $basename;
                }
                $suffix = str_pad($i, 4, '0', STR_PAD_LEFT);

                // Integer mit fuehrenden Nullen an den Dateinamen anhaengen ->filename0001.jpg
                $basename = $filename . '_' . $suffix;

                // Break after 100 loops
                if ($i == 100)
                {
                    return $dirname . '/' . md5($basename . microtime()) . '.' . $extension;
                }
            }
        } while ($isUnique === false);

        return false;
    }

    /**
     * generate the jumploader applet
     * @param string $uploader
     * @return string
     */
    public static function generateUploader($uploader = 'be_gc_html5_uploader')
    {

        //create the template object
        $objTemplate = new BackendTemplate($uploader);


        // maxFileSize
        $objTemplate->maxFileSize = $GLOBALS['TL_CONFIG']['maxFileSize'];

        // $_FILES['file']
        $objTemplate->strName = 'file';

        // parse the jumloader view and return it
        return $objTemplate->parse();
    }


    /**
     * @param string
     * @param integer
     * @return bool
     * $src - path to the filesource (f.ex: files/folder/img.jpeg)
     * angle - the rotation angle is interpreted as the number of degrees to rotate the image anticlockwise.
     * angle shall be 0,90,180,270
     */
    public static function imageRotate($src, $angle)
    {
        $src = html_entity_decode($src);

        if (!file_exists(TL_ROOT . '/' . $src))
        {
            Message::addError(sprintf('File "%s" not found.', $src));
            return false;
        }

        $objFile = new File($src);
        if (!$objFile->isGdImage)
        {
            Message::addError(sprintf('File "%s" could not be rotated because it is not an image.', $src));
            return false;
        }

        if (!function_exists('imagerotate'))
        {
            Message::addError(sprintf('PHP function "%s" is not installed.', 'imagerotate'));
            return false;
        }

        $source = imagecreatefromjpeg(TL_ROOT . '/' . $src);

        //rotate
        $imgTmp = imagerotate($source, $angle, 0);

        // Output
        imagejpeg($imgTmp, TL_ROOT . '/' . $src);
        imagedestroy($source);
        return true;


    }

    /**
     * @param integer
     * @param string
     * Import images from a folder
     */
    public static function importFromFilesystem($intAlbumId, $strMultiSRC)
    {

        $images = array();

        $objFilesModel = FilesModel::findMultipleByUuids(explode(',', $strMultiSRC));
        if ($objFilesModel === null)
        {
            return;
        }

        while ($objFilesModel->next())
        {

            // Continue if the file has been processed or does not exist
            if (isset($images[$objFilesModel->path]) || !file_exists(TL_ROOT . '/' . $objFilesModel->path))
            {
                continue;
            }

            // If item is a file, then store it in the array
            if ($objFilesModel->type == 'file')
            {
                $objFile = new File($objFilesModel->path);
                if ($objFile->isGdImage)
                {
                    $images[$objFile->path] = array(
                        'uuid' => $objFilesModel->uuid,
                        'basename' => $objFile->basename,
                        'path' => $objFile->path
                    );
                }
            }
            else
            {
                // If it is a directory, then store its files in the array
                $objSubfilesModel = FilesModel::findMultipleFilesByFolder($objFilesModel->path);
                if ($objSubfilesModel === null)
                {
                    continue;
                }

                while ($objSubfilesModel->next())
                {

                    // Skip subfolders
                    if ($objSubfilesModel->type == 'folder' || !is_file(TL_ROOT . '/' . $objSubfilesModel->path))
                    {
                        continue;
                    }

                    $objFile = new File($objSubfilesModel->path);
                    if ($objFile->isGdImage)
                    {
                        $images[$objFile->path] = array(
                            'uuid' => $objSubfilesModel->uuid,
                            'basename' => $objFile->basename,
                            'path' => $objFile->path
                        );
                    }
                }
            }
        }

        if (count($images))
        {
            $arrPictures = array(
                'uuid' => array(),
                'path' => array(),
                'basename' => array()
            );

            $objPictures = Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=?')->execute($intAlbumId);
            $arrPictures['uuid'] = $objPictures->fetchEach('uuid');
            $arrPictures['path'] = $objPictures->fetchEach('path');
            foreach ($arrPictures['path'] as $path)
            {
                $arrPictures['basename'][] = basename($path);
            }

            $objAlb = GalleryCreatorAlbumsModel::findById($intAlbumId);
            foreach ($images as $image)
            {
                // Prevent duplicate entries
                if (in_array($image['uuid'], $arrPictures['uuid']))
                {
                    continue;
                }

                // Prevent duplicate entries
                if (in_array($image['basename'], $arrPictures['basename']))
                {
                    continue;
                }

                Input::setGet('importFromFilesystem', 'true');
                if ($GLOBALS['TL_CONFIG']['gc_album_import_copy_files'])
                {

                    $strSource = $image['path'];

                    // Get the album upload directory
                    $objFolderModel = FilesModel::findByUuid($objAlb->assignedDir);
                    $errMsg = 'Aborted import process, because there is no upload folder assigned to the album with ID ' . $objAlb->id . '.';
                    if ($objFolderModel === null)
                    {
                        die($errMsg);
                    }
                    if ($objFolderModel->type != 'folder')
                    {
                        die($errMsg);
                    }
                    if (!is_dir(TL_ROOT . '/' . $objFolderModel->path))
                    {
                        die($errMsg);
                    }

                    $strDestination = self::generateUniqueFilename($objFolderModel->path . '/' . basename($strSource));
                    if (is_file(TL_ROOT . '/' . $strSource))
                    {
                        // Copy image to the upload folder
                        $objFile = new File($strSource);
                        $objFile->copyTo($strDestination);
                        Dbafs::addResource($strSource);
                    }

                    self::createNewImage($objAlb->id, $strDestination);
                }
                else
                {
                    self::createNewImage($objAlb->id, $image['path']);
                }
            }
        }
    }

    /**
     * Add albums to the indexer
     *
     * @param array   $arrPages
     * @param integer $intRoot
     * @param boolean $blnIsSitemap
     *
     * @return array
     */
    public function getSearchablePages($arrPages, $intRoot=0, $blnIsSitemap=false)
    {
        $arrRoot = array();

        if ($intRoot > 0)
        {
            $arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');
        }

        $arrProcessed = array();
        $time = Date::floorToMinute();

        // Get all galleries
        $objGallery = GalleryCreatorGalleriesModel::findByProtected('');

        // Walk through each gallery
        if ($objGallery !== null)
        {
            while ($objGallery->next())
            {
                // Skip galleries without target page
                if (!$objGallery->jumpTo)
                {
                    continue;
                }

                // Skip galleries outside the root nodes
                if (!empty($arrRoot) && !in_array($objGallery->jumpTo, $arrRoot))
                {
                    continue;
                }

                // Get the URL of the jumpTo page
                if (!isset($arrProcessed[$objGallery->jumpTo]))
                {
                    $objParent = PageModel::findWithDetails($objGallery->jumpTo);

                    // The target page does not exist
                    if ($objParent === null)
                    {
                        continue;
                    }

                    // The target page has not been published (see #5520)
                    if (!$objParent->published || ($objParent->start != '' && $objParent->start > $time) || ($objParent->stop != '' && $objParent->stop <= ($time + 60)))
                    {
                        continue;
                    }

                    if ($blnIsSitemap)
                    {
                        // The target page is protected (see #8416)
                        if ($objParent->protected)
                        {
                            continue;
                        }

                        // The target page is exempt from the sitemap (see #6418)
                        if ($objParent->sitemap == 'map_never')
                        {
                            continue;
                        }
                    }

                    // Generate the URL
                    $arrProcessed[$objGallery->jumpTo] = $objParent->getAbsoluteUrl((Config::get('useAutoItem') && !Config::get('disableAlias')) ?  '/%s' : '/albums/%s');
                }

                $strUrl = $arrProcessed[$objGallery->jumpTo];

                // Get the items
                $objAlbums = GalleryCreatorAlbumsModel::findPublishedDefaultByPid($objGallery->id);

                if ($objAlbums !== null)
                {
                    while ($objAlbums->next())
                    {
                        $arrPages[] = sprintf($strUrl, (($objAlbums->alias != '' && !Config::get('disableAlias')) ? $objAlbums->alias : $objAlbums->id));
                    }
                }
            }
        }

        return $arrPages;
    }


}
