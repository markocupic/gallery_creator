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
namespace GalleryCreator;

/**
 * Class DisplayGallery
 *
 * Provide methods regarding gallery_creator albums.
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
abstract class DisplayGallery extends \Module
{
    /**
     * Albumalias
     * @var string
     */
    protected $strAlbumalias;

    /**
     * Album-id
     * @var integer
     */
    protected $intAlbumId;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'ce_gc_default';

    /**
     * path to the default thumbnail, if no valid preview-thumb was found
     * @var string
     */
    protected $defaultThumb = 'system/modules/gallery_creator/assets/images/image_not_found.jpg';

    /**
     * true if page displays the detailview of an album
     * @var boolean
     */
    protected $DETAIL_VIEW = false;

    /**
     * Parse the template
     * @return string
     */
    public function generate()
    {

        // unset the Session
        unset($_SESSION['gallery_creator']['CURRENT_ALBUM']);

        // set the item from the auto_item parameter
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        if (strlen(\Input::get('items'))) {
            $this->DETAIL_VIEW = true;
        } else {

        }

        //assigning the frontend template
        $this->strTemplate = $this->gc_template != "" ? $this->gc_template : $this->strTemplate;

        //do some default-settings for the thumb-size if no settings are done in the module-/content-settings
        $this->checkThumbSizeSettings();

        // store the pagination variable page in the current session
        if (!\Input::get('items'))
            unset($_SESSION['gallery_creator']['PAGINATION']);
        if (\Input::get('page') && !$this->DETAIL_VIEW)
            $_SESSION['gallery_creator']['PAGINATION'] = \Input::get('page');

        return parent::generate();

    }

    /**
     * do some default-settings for the thumb-size if no settings are done in the module-/content-settings
     */
    protected function checkThumbSizeSettings()
    {
        if ($this->gc_size_albumlisting == "")
            $this->gc_size_albumlisting = serialize(array(
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
     * @param integer
     * @return integer
     */
    protected function countGcContentElementsOnPage($intPageId = null)
    {
        if ($intPageId) {
            $objPage = $this->Database->prepare('SELECT * FROM tl_page WHERE id=?')->execute($intPageId);
        } else {
            global $objPage;
        }

        //kontrollieren, ob Weiterleitung zu overwiev moeglich ist
        //Keine Weiterleitung moeglich, bei mehreren aktivierten GALLERY_CREATOR Inhaltselementen im selben Artikel
        $objArticlesOfCurrentPage = $this->Database->prepare('SELECT id FROM tl_article WHERE pid=? AND published=?')->execute($objPage->id, 1);

        $arrArticlesOfCurrentPage = array();
        while ($objArticlesOfCurrentPage->next()) {
            $arrArticlesOfCurrentPage[] = (int)$objArticlesOfCurrentPage->id;
        }

        $gcElementCounter = 0;
        $objCE = $this->Database->prepare('SELECT pid FROM tl_content WHERE type=? AND invisible=?')->execute('gallery_creator', 0);
        while ($objCE->next()) {
            if (in_array($objCE->pid, $arrArticlesOfCurrentPage)) {
                $gcElementCounter += 1;
            }
        }
        return $gcElementCounter;
    }

    /**
     * Hilfsmethode
     * Ueberprueft, ob bei nur einem ausgewaehlten Album direkt zur Thumnailuebersicht des Albums weitergeleitet werden soll.
     * @return bool
     */
    protected function doRedirectOnSingleAlbum()
    {
        if (TL_MODE == 'BE') {
            return false;
        }
        //if all albums are published
        $objAlb = $this->Database->prepare('SELECT count(id) AS countPublishedAlbums FROM tl_gallery_creator_albums WHERE published=?')->execute('1');
        if ($this->gc_publish_all_albums && $objAlb->countPublishedAlbums == 1) {
            $singleAlbum = true;
        }
        if ($this->gc_publish_all_albums && $objAlb->countPublishedAlbums > 1) {
            return false;
        }
        //wahr, wenn im gc-Inhaltselement nur 1 Album selektiert wurde
        $arrAlbId = deserialize($this->gc_publish_albums);
        if (count($arrAlbId) == 1) {
            $singleAlbum = true;
        }

        //wahr wenn: weniger als zwei gc Inhaltselemente auf aktueller Seite && Galerie enthaelt nur 1 Album && Weiterleitung in den Elementeinstellungen aktiviert ist
        if ($this->countGcContentElementsOnPage() == 1 && $singleAlbum && $this->gc_redirectSingleAlb) {
            return true;
        }
        return false;
    }

    /**
     * evaluate the request and extracts the album-id and the content-element-id
     */
    public function evalRequestVars()
    {
        if ($this->gc_publish_all_albums != 1) {
            if (!unserialize($this->gc_publish_albums)) {
                return;
            }
        }

        if (\Input::get('items')) {
            //aktueller Albumalias
            $this->strAlbumalias = \Input::get('items');

            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $this->feUserAuthentication($this->strAlbumalias);

            //fuer jw_imagerotator ajax-requests
            if (\Input::get('jw_imagerotator')) {
                return;
            }
        }

        //wenn nur ein Album ausgewaehlt wurde und Weiterleitung in den Inhaltselementeinstellungen aktiviert wurde, wird weitergeleitet
        if ($this->doRedirectOnSingleAlbum()) {
            $arrAlbId = unserialize($this->gc_publish_albums);
            if ($this->gc_publish_all_albums) {
                //if all albums are selected
                $objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE published=?')->execute('1');
            } else {
                $objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')->execute($arrAlbId[0]);
            }

            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $this->feUserAuthentication($objAlbum->alias);

            \Input::setGet('items', $objAlbum->alias);
            $this->strAlbumalias = $objAlbum->alias;
        }

        // Get the Album Id
        if (\Input::get('items')) {
            // Die AlbumId des anzuzeigenden Albums aus der db extrahieren
            $objAlbum = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE alias=?')->execute($this->strAlbumalias);
            $this->intAlbumId = $objAlbum->id;
            $this->DETAIL_VIEW = true;
        }
    }

    /**
     * Check if fe-user is allowed watching this album
     * @param string
     * @return bool
     */
    protected function feUserAuthentication($strAlbumalias)
    {
        if (TL_MODE == 'FE') {
            $objAlb = $this->Database->prepare('SELECT protected AS protected_album,groups FROM tl_gallery_creator_albums WHERE alias=?')->execute($strAlbumalias);
            if (!$objAlb->protected_album) {
                return true;
            }

            $this->import('FrontendUser', 'User');
            $groups = deserialize($objAlb->groups);

            if (!FE_USER_LOGGED_IN || !is_array($groups) || count($groups) < 1 || !array_intersect($groups, $this->User->groups)) {
                // abort script and display authentification error
                $strContent = sprintf("<div>\r\n<h1>%s</h1>\r\n<p>%s</p>\r\n</div>", $GLOBALS['TL_LANG']['gallery_creator']['fe_authentification_error'][0], $GLOBALS['TL_LANG']['gallery_creator']['fe_authentification_error'][1]);
                die($strContent);
            }
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
        if (\Input::get('isAjax') && \Input::get('getImage') && strlen(\Input::get('imageId'))) {
            $arrPicture = $this->getPictureInformationArray(\Input::get('imageId'), NULL, \Input::get('action'));
            return json_encode($arrPicture);
        }

        //thumbslider der Albenübersicht
        if (\Input::get('isAjax') && \Input::get('thumbSlider')) {
            $this->checkThumbSizeSettings();
            $arrSize = unserialize($this->gc_size_albumlisting);

            $objAlbum = $this->Database->prepare('SELECT thumb,alias FROM tl_gallery_creator_albums WHERE id=?')->execute(\Input::get('AlbumId'));
            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $this->feUserAuthentication($objAlbum->alias);
            if (GALLERY_CREATOR_ALBUM_AUTHENTIFICATION_ERROR === true)
                return false;

            $objPictures = $this->Database->prepare('SELECT count(id) AS Anzahl FROM tl_gallery_creator_pictures WHERE published=? AND pid=? AND id!=?')->execute(1, \Input::get('AlbumId'), $objAlbum->thumb);
            if ($objPictures->Anzahl < 2) {
                return json_encode(array('thumbPath' => ''));
            }

            $limit = \Input::get('limit');
            $objPicture = $this->Database->prepare('SELECT name, path FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY id')->limit(1, $limit)->executeUncached(1, \Input::get('AlbumId'), $objAlbum->thumb);
            $jsonUrl = array(
                'thumbPath' => \Image::get($objPicture->path, $arrSize[0], $arrSize[1], $arrSize[2]),
                'eventId' => \Input::get('eventId')
            );

            echo json_encode($jsonUrl);
            exit;
        }

        //Detailansicht nur mit Lightbox, für ce_gc_lightbox.tpl Template
        if (\Input::get('isAjax') && \Input::get('LightboxSlideshow') && \Input::get('albumId')) {
            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')->execute(\Input::get('albumId'));
            $this->feUserAuthentication($objAlbum->alias);
            if (GALLERY_CREATOR_ALBUM_AUTHENTIFICATION_ERROR === true)
                return false;

            $json = "";

            // sorting direction
            $ceType = \Input::get('action');
            if ($ceType == 'cte') {
                $sorting = $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;
            } else {
                $sorting = 'sorting DESC';
            }

            $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY ' . $sorting)->executeUncached(1, \Input::get('albumId'));
            while ($objPicture->next()) {
                $href = $objPicture->path;
                $href = trim($objPicture->socialMediaSRC) != "" ? trim($objPicture->socialMediaSRC) : $href;
                $href = trim($objPicture->localMediaSRC) != "" ? trim($objPicture->localMediaSRC) : $href;

                $json .= specialchars($href) . "###";
                $json .= specialchars($objPicture->comment) . " ***";
            }
            $jsonUrl = array('arrImage' => $json);
            echo json_encode($jsonUrl);
            exit;
        }
        return null;
    }

    /**
     * Generate the xml-output for jwImagerotator
     * @param string
     * @return string
     */
    protected function getJwImagerotatorXml($strAlbumalias)
    {
        $objAlbum = $this->Database->prepare('SELECT id, owners_name FROM tl_gallery_creator_albums WHERE alias=? and published=1')->execute($strAlbumalias);
        $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY sorting')->execute('1', $objAlbum->id);

        //playlist xml output
        $xml = "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
        $xml .= "<trackList>\n";
        while ($objPicture->next()) {
            $caption = trim($objPicture->comment) != "" ? $objPicture->comment : $objPicture->name;
            $xml .= "\t<track>\n";
            $xml .= "\t\t<title>" . specialchars($caption) . "</title>\n";
            $xml .= "\t\t<location>" . $objPicture->path . "</location>\n";
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
    protected function getAlbumPreviewThumb($intAlbumId)
    {
        $objAlb = $this->Database->prepare('SELECT thumb FROM tl_gallery_creator_albums WHERE id=?')->execute($intAlbumId);
        if ($objAlb->thumb) {
            $objPreviewThumb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')->execute($objAlb->thumb);
        } else {
            // Search for an other valid thumb in the album
            $objPreviewThumb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=?')->limit(1)->execute($intAlbumId);
        }
        $arrRow = $objPreviewThumb->fetchAssoc();

        // check if it is a valid image-file
        if (!is_file(TL_ROOT . '/' . $arrRow['path'])) {
            // Search for an other valid thumb in the album
            $objPreviewThumb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=?')->limit(1)->execute($intAlbumId);
            $arrRow = $objPreviewThumb->fetchAssoc();
            if (!is_file(TL_ROOT . '/' . $arrRow['path'])) {
                return array('name' => basename($this->defaultThumb), 'path' => $this->defaultThumb);
            }
        }

        $objFile = new \File($arrRow['path']);
        if ($objFile->isGdImage) {
            \System::urlEncode($arrRow['path']);
            return $arrRow;
        } else {
            return array('name' => basename($this->defaultThumb), 'path' => $this->defaultThumb);
        }
    }

    /**
     * Returns the information-array about an album
     * @param integer
     * @param array
     * @param string
     * @return array
     */
    protected function getAlbumInformationArray($intAlbumId, $strSize, $strContentType)
    {
        global $objPage;

        if ($strContentType != 'fmd' && $strContentType != 'cte') {
            $strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
            __error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
        }

        $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($intAlbumId);
        //Anzahl Subalben ermitteln
        if ($this->gc_hierarchicalOutput) {
            $objSubAlbums = $this->Database->prepare('SELECT thumb, count(id) AS countSubalbums FROM tl_gallery_creator_albums WHERE published=? AND pid=? GROUP BY ?')->execute('1', $intAlbumId, 'id');
        }
        $objPics = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=? AND published=?')->execute($objAlbum->id, '1');

        //Array Thumbnailbreite
        $arrSize = unserialize($strSize);

        //exif-data
        try {
            $exif = is_callable('exif_read_data') && TL_MODE == 'FE' ? @exif_read_data($objPics->path) : array('info' => "The function 'exif_read_data()' is not available on this server.");
        } catch (Exception $e) {
            echo $e->getMessage();
            $exif = array('info' => "The function 'exif_read_data()' is not available on this server.");
        }

        $href = null;
        if (TL_MODE == 'FE') {
            //generate the url as a formated string
            $href = $this->generateFrontendUrl($objPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/%s##ceId##' : '/items/%s##ceId##'), $objPage->language);
            //add the content-element-id if necessary
            $href = $strContentType == 'cte' && $this->countGcContentElementsOnPage() > 1 ? str_replace('##ceId##', '/ce/' . $this->id, $href) : str_replace('##ceId##', '', $href);
        }

        $arrPreviewThumb = $this->getAlbumPreviewThumb($objAlbum->id);

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
            'event_location' => specialchars($objAlbum->event_location),
            //[string] Albumname
            'name' => specialchars($objAlbum->name),
            //[string] Albumalias (=Verzeichnisname)
            'alias' => $objAlbum->alias,
            //[string] Albumkommentar
            'comment' => strlen($objAlbum->comment) ? specialchars($objAlbum->comment) : NULL,
            'caption' => strlen($objAlbum->comment) ? specialchars($objAlbum->comment) : NULL,
            //[int] Albumbesucher (Anzahl Klicks)
            'visitors' => $objAlbum->visitors,
            //[string] Link zur Detailansicht
            'href' => TL_MODE == 'FE' ? sprintf($href, $objAlbum->alias) : NULL,
            //[string] Inhalt fuer das title Attribut
            'title' => $objAlbum->name . ' [' . ($objPics->numRows ? $objPics->numRows . ' ' . $GLOBALS['TL_LANG']['gallery_creator']['pictures'] : '') . ($objSubAlbums->countSubalbums > 0 ? ' ' . $GLOBALS['TL_LANG']['gallery_creator']['contains'] . ' ' . $objSubAlbums->countSubalbums . '  ' . $GLOBALS['TL_LANG']['gallery_creator']['subalbums'] . ']' : ']'),
            //[int] Anzahl Bilder im Album
            'count' => $objPics->numRows,
            //[int] Anzahl Unteralben
            'count_subalbums' => count(GcHelpers::getAllSubalbums($objAlbum->id)),
            //[string] alt Attribut fuer das Vorschaubild
            'alt' => $arrPreviewThumb['name'],
            //[string] Pfad zum Originalbild
            'src' => $arrPreviewThumb['path'],
            //[string] Pfad zum Thumbnail
            'thumb_src' => \Image::get($arrPreviewThumb['path'], $arrSize[0], $arrSize[1], $arrSize[2]),
            //[int] article id
            'insert_article_pre' => $objAlbum->insert_article_pre ? $objAlbum->insert_article_pre : null,
            //[int] article id
            'insert_article_post' => $objAlbum->insert_article_post ? $objAlbum->insert_article_post : null,
            //[string] css-Classname
            'class' => 'thumb',
            //[int] Thumbnailgrösse
            'size' => $arrSize,
            //[array] array mit exif metatags
            'exif' => $exif,
            //[string] javascript-Aufruf
            'thumbMouseover' => $this->gc_activateThumbSlider ? "objGalleryCreator.initThumbSlide(this, " . $this->id . ", " . $objAlbum->id . ", " . $objPics->numRows . ", '" . strtolower($this->moduleType) . "');" : ""
        );

        //Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_albums erweitert wurde
        $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($intAlbumId);
        foreach ($objAlbum->fetchAssoc() as $key => $value) {
            if (!array_key_exists($key, $arrAlbum)) {
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
    protected function getPictureInformationArray($intPictureId, $strSize = NULL, $strContentType)
    {
        global $objPage;

        if ($strContentType != 'fmd' && $strContentType != 'cte') {
            $strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
            __error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
        }
        if ($this->Template) {
            $this->Template->elementType = strtolower($strContentType);
            $this->Template->elementId = $this->id;
        }

        $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')->execute($intPictureId);

        //Alle Informationen zum Album in ein array packen
        $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($objPicture->pid);
        $arrAlbumInfo = $objAlbum->fetchAssoc();

        //Bild-Besitzer
        $objOwner = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objPicture->owner);

        $strImageSrc = $objPicture->path;
        //Wenn Datei nicht mehr vorhanden, auf default-thumb zurueckgreifen
        if (!is_file(TL_ROOT . '/' . $objPicture->path)) {
            $strImageSrc = $this->defaultThumb;
        }

        if ($strSize) {
            //Thumbnailbreite
            $arrSize = unserialize($strSize);
            //Thumbnails generieren

            $thumbSrc = \Image::get($strImageSrc, $arrSize[0], $arrSize[1], $arrSize[2]);
            // die($thumbSrc);
            $objFile = new \File($thumbSrc);
            $arrSize[0] = $objFile->width;
            $arrSize[1] = $objFile->height;
            $arrFile["thumb_width"] = $objFile->width;
            $arrFile["thumb_height"] = $objFile->height;
        }

        $strImageSrc = $objPicture->path;
        //Wenn Datei nicht mehr vorhanden, auf default-thumb zurueckgreifen
        if (!is_file(TL_ROOT . '/' . $objPicture->path)) {
            $strImageSrc = $this->defaultThumb;
        }

        if (is_file(TL_ROOT . '/' . $strImageSrc)) {
            $objFile = new \File($strImageSrc);
            if (!$objFile->isGdImage)
                return null;

            $arrFile["filename"] = $objFile->filename;
            $arrFile["basename"] = $objFile->basename;
            $arrFile["dirname"] = $objFile->dirname;
            $arrFile["extension"] = $objFile->extension;
            $arrFile["image_width"] = $objFile->width;
            $arrFile["image_height"] = $objFile->height;
        } else {
            return null;
        }


        //check if there is a custom thumbnail selected
        if ($objPicture->customThumb > 0) {
            $objFile = \FilesModel::findByPk($objPicture->customThumb);
            if ($objFile !== null) {
                if (is_file(TL_ROOT . '/' . $objFile->path)) {
                    $objFile = new \File($objFile->path);
                    if ($objFile->isGdImage) {
                        $thumbSrc = \Image::get($objFile->path, $arrSize[0], $arrSize[1], $arrSize[2]);
                        $objFile = new \File($thumbSrc);
                        $arrSize[0] = $objFile->width;
                        $arrSize[1] = $objFile->height;
                        $arrFile["thumb_width"] = $objFile->width;
                        $arrFile["thumb_height"] = $objFile->height;
                    }
                }
            }
        }

        //exif
        try {
            $exif = is_callable('exif_read_data') && TL_MODE == 'FE' ? @exif_read_data($objPicture->path) : array('info' => "The function 'exif_read_data()' is not available on this server.");
        } catch (Exception $e) {
            $exif = array('info' => "The function 'exif_read_data()' is not available on this server.");
        }
        //video-integration
        $strMediaSrc = trim($objPicture->socialMediaSRC) != "" ? trim($objPicture->socialMediaSRC) : "";
        if (is_int((int)$objPicture->localMediaSRC)) {
            //get path of a local Media
            $objMovieFile = \FilesModel::findById($objPicture->localMediaSRC);
            $strMediaSrc = is_object($objMovieFile) ? $objMovieFile->path : $strMediaSrc;
        }

        $href = null;
        if (TL_MODE == 'FE' && $this->gc_fullsize) {
            $href = $strMediaSrc != "" ? $strMediaSrc : \System::urlEncode($strImageSrc);
        }

        //cssID
        $cssID = deserialize($objPicture->cssID, true);

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
            //[string] name (basename/filename of the file)
            'name' => specialchars($objPicture->name),
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
            //[string] alt-attribut
            'alt' => specialchars($objPicture->title ? $objPicture->title : $objPicture->name),
            //[string] title-attribut
            'title' => specialchars($objPicture->title),
            //[string] Bildkommentar oder Bildbeschreibung
            'comment' => specialchars($objPicture->comment),
            'caption' => specialchars($objPicture->comment),
            //[string] path to media (video, picture, sound...)
            'href' => $href,
            //path to the image
            'image_src' => $strImageSrc,
            //path to the other selected media
            'media_src' => $strMediaSrc,
            //[string] path to a media on a social-media-plattform
            'socialMediaSRC' => $objPicture->socialMediaSRC,
            //[string] path to a media stored on the webserver
            'localMediaSRC' => $objPicture->localMediaSRC,
            //[string] Pfad zu einem benutzerdefinierten Thumbnail
            'addCustomThumb' => $objPicture->addCustomThumb,
            //[string] Thumbnailquelle
            'thumb_src' => $thumbSrc,
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
            'metaData' => $this->getMetaContent($objPicture->id),
            //[string] css-ID des Bildcontainers
            'cssID' => $cssID[0] != '' ? $cssID[0] : '',
            //[string] css-Klasse des Bildcontainers
            'cssClass' => $cssID[1] != '' ? $cssID[1] : '',
            //[bool] true, wenn es sich um ein Bild handelt, das nicht in files/gallery_creator_albums/albumname gespeichert ist
            'externalFile' => $objPicture->externalFile,
        );

        //Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_pictures erweitert wurde
        $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')->execute($intPictureId);
        foreach ($objPicture->fetchAssoc() as $key => $value) {
            if (!array_key_exists($key, $arrPicture)) {
                $arrPicture[$key] = $value;
            }
        }

        return $arrPicture;
    }

    /**
     * gets the meta-text-informations from tl_files.meta
     * @param integer
     * @return array
     */
    public function getMetaContent($intPictureId)
    {
        global $objPage;

        $objPicture = \GalleryCreatorPicturesModel::findById($intPictureId);
        $objFiles = \FilesModel::findByPath($objPicture->path);
        if (!is_object($objFiles))
            return null;

        $objFile = new \File($objPicture->path);
        if (!$objFile->isGdImage)
            return null;

        $arrMeta = $this->getMetaData($objFiles->meta, $objPage->language);

        // Use the file name as title if none is given
        if ($arrMeta['title'] == '') {
            $arrMeta['title'] = specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFile->filename)));
        }

        return $arrMeta;
    }

    /**
     * Sets the template-vars for the selected album
     * @param integer
     * @param string
     */
    protected function getAlbumTemplateVars($intAlbumId, $strContentType)
    {
        global $objPage;
        if ($strContentType != 'fmd' && $strContentType != 'cte') {
            $strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
            __error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
        }
        //wichtig fuer Ajax-Anwendungen
        $this->Template->elementType = strtolower($strContentType);
        $this->Template->elementId = $this->id;

        // Load the current album from db
        $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($intAlbumId);

        // Overwrite the page description
        if (TL_MODE == 'FE') {
            $objPage->description = $objPage->description . ': ' . $objAlbum->alias;
        }

        //store all album-data in the array
        $this->Template->arrAlbumdata = $objAlbum->fetchAssoc();

        // store the data of the current album in the session
        $_SESSION['gallery_creator']['CURRENT_ALBUM'] = $this->Template->arrAlbumdata;

        //die FMD/CTE-id
        $this->Template->fmdId = $this->id;
        //der back-Link
        $this->Template->backLink = $this->generateBackLink($strContentType, $intAlbumId);
        //Der dem Bild uebergeordnete Albumname
        $this->Template->Albumname = $objAlbum->name;
        // Albumbesucher (Anzahl Klicks)
        $this->Template->visitors = $objAlbum->vistors;
        //Der Kommentar zum gewaehlten Album
        $this->Template->albumComment = $objAlbum->comment != "" ? nl2br($objAlbum->comment) : NULL;
        // In der Detailansicht kann optional ein Artikel vor dem Album hinzugefuegt werden
        $this->Template->insertArticlePre = $objAlbum->insert_article_pre ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_pre) : null;
        // In der Detailansicht kann optional ein Artikel nach dem Album hinzugefuegt werden
        $this->Template->insertArticlePost = $objAlbum->insert_article_post ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_post) : null;
        //Das Event-Datum des Albums als unix-timestamp
        $this->Template->eventTstamp = $objAlbum->date;
        //Das Event-Datum des Albums formatiert
        $this->Template->eventDate = $this->parseDate($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date);
        //Abstaende
        $this->Template->imagemargin = $this->DETAIL_VIEW === true ? $this->generateMargin(unserialize($this->gc_imagemargin_detailview)) : $this->generateMargin(unserialize($this->gc_imagemargin_albumlisting));
        //Anzahl Spalten pro Reihe
        $this->Template->colsPerRow = $this->gc_rows == "" ? 4 : $this->gc_rows;
        //Pfad zur xml-Ausgabe fuer jw_imagerotator
        $this->Template->jw_imagerotator_path = TL_MODE == 'FE' ? $this->generateFrontendUrl($objPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $objAlbum->alias . '/jw_imagerotator/true') : NULL;
        //Inhaltselement Id anhaengen wenn es sich um ein Inhaltselement handelt
        if ($strContentType == 'cte') {
            //Pfad zur xml-Ausgabe fuer jw_imagerotator
            if ($this->countGcContentElementsOnPage() > 1) {
                $this->Template->jw_imagerotator_path = TL_MODE == 'FE' ? $this->Template->jw_imagerotator_path . '/ce/' . $this->id : NULL;
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
    public function generateBackLink($strContentType, $intAlbumId)
    {
        global $objPage;

        if (TL_MODE == 'BE') {
            return false;
        }

        if ($strContentType == 'cte') {
            //Nur, wenn nicht automatisch zu overview weitergeleitet wurde, wird der back Link angezeigt
            if ($this->doRedirectOnSingleAlbum())
                return NULL;
        }

        //generiert den Link zum Parent-Album
        if ($this->gc_hierarchicalOutput && GcHelpers::getParentAlbum($intAlbumId)) {
            $arrParentAlbum = GcHelpers::getParentAlbum($intAlbumId);
            return $this->generateFrontendUrl($objPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $arrParentAlbum["alias"]);
        }

        //generiert den Link zur Startuebersicht unter Beruecksichtigung der pagination
        $url = $this->generateFrontendUrl($objPage->row(), '');
        $url .= isset($_SESSION['gallery_creator']['PAGINATION']) ? '?page=' . $_SESSION['gallery_creator']['PAGINATION'] : '';
        return $url;
    }

    /**
     * initCounter
     * @param integer
     * @return string
     */
    public static function initCounter($intAlbumId)
    {
        if (preg_match('/bot|sp[iy]der|crawler|lib(?:cur|www)|search|archive/i', $_SERVER['HTTP_USER_AGENT'])) {
            // do not count spiders/bots
            return;
        }

        if (TL_MODE == 'FE') {
            $objDb = \Database::getInstance()->prepare('SELECT visitors, visitors_details FROM tl_gallery_creator_albums WHERE id=?')->executeUncached($intAlbumId);
            if (strpos($objDb->visitors_details, $_SERVER['REMOTE_ADDR'])) {
                // return if the visitor is allready registered
                return;
            }

            // increase the number of visitors by one
            $intCount = (int)$objDb->visitors + 1;

            $arrVisitors = strlen($objDb->visitors_details) ? unserialize($objDb->visitors_details) : array();
            if (is_array($arrVisitors)) {
                // keep visiors data in the db unless 20 other users visited the album
                if (count($arrVisitors) == 20) {
                    // slice the last position
                    $arrVisitors = array_slice($arrVisitors, 0, count($arrVisitors) - 1);
                }
            } else {
                $set = array('visitors_details' => '');
                $objDbUpd = \Database::getInstance()->prepare('UPDATE tl_gallery_creator_albums %s WHERE id=?')->set($set)->executeUncached($intAlbumId);
            }

            //build up the array
            $newVisitor = array($_SERVER['REMOTE_ADDR'] => array(
                'ip' => $_SERVER['REMOTE_ADDR'],
                'pid' => $intAlbumId,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                'tstamp' => time(),
                'url' => \Environment::get('request'),
            )
            );

            if (count($arrVisitors)) {
                // insert the element to the beginning of the array
                array_unshift($arrVisitors, $newVisitor);
            } else {
                // create the new array
                $arrVisitors = array();
                $arrVisitors[] = array($_SERVER['REMOTE_ADDR'] => $newVisitor);
            }

            // update database
            $set = array('visitors' => $intCount, 'visitors_details' => serialize($arrVisitors));
            $objDb = \Database::getInstance()->prepare('UPDATE tl_gallery_creator_albums %s WHERE id=?')->set($set)->executeUncached($intAlbumId);
        }
    }

}