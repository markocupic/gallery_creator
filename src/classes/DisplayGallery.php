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
 * Class DisplayGallery
 *
 * Provide methods regarding gallery_creator albums.
 *
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
abstract class DisplayGallery extends \Module
{

    /**
     * Albumalias
     *
     * @var string
     */
    protected $strAlbumalias;

    /**
     * Album-id
     *
     * @var integer
     */
    protected $intAlbumId;

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'ce_gc_default';

    /**
     * path to the default thumbnail, if no valid preview-thumb was found
     *
     * @var string
     */
    public $defaultThumb = 'system/modules/gallery_creator/assets/images/image_not_found.jpg';

    /**
     * true if page displays the detailview of an album
     *
     * @var boolean
     */
    protected $DETAIL_VIEW = false;

    /**
     * Parse the template
     *
     * @return string
     */
    public function generate()
    {

        // Get the module type 'cte' or 'fmd'
        $this->moduleType = strpos(strtolower(get_class($this)), 'content') !== false ? 'cte' : 'fmd';

        // Ajax Requests
        if (TL_MODE == 'FE' && $this->Environment->get('isAjaxRequest'))
        {
            $this->generateAjax();
            exit;
        }

        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG'][strtoupper($this->moduleType)]['gallery_creator'][0]) . ' ###';
            $objTemplate->title = $this->headline;

            // for module use only
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }


        // unset the Session
        unset($_SESSION['gallery_creator']['CURRENT_ALBUM']);

        // set the item from the auto_item parameter
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
        {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        if (strlen(\Input::get('items')))
        {
            $this->DETAIL_VIEW = true;
        }

        //assigning the frontend template
        $this->strTemplate = $this->gc_template != "" ? $this->gc_template : $this->strTemplate;
        $this->Template = new \FrontendTemplate($this->strTemplate);

        //do some default-settings for the thumb-size if no settings are done in the module-/content-settings
        $this->checkThumbSizeSettings();

        // store the pagination variable page in the current session
        if (!\Input::get('items'))
        {
            unset($_SESSION['gallery_creator']['PAGINATION']);
        }
        if (\Input::get('page') && !$this->DETAIL_VIEW)
        {
            $_SESSION['gallery_creator']['PAGINATION'] = \Input::get('page');
        }

        return parent::generate();

    }

    protected function callGcGenerateFrontendTemplateHook($objModule, $objAlbum = null)
    {

        // HOOK: modify the page or template object
        if (isset($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']) && is_array($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']))
        {
            foreach ($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate'] as $callback)
            {
                $this->import($callback[0]);
                $this->$callback[0]->$callback[1]($objModule, $objAlbum);
            }
        }
    }

    /**
     * do some default-settings for the thumb-size if no settings are done in the module-/content-settings
     */
    protected function checkThumbSizeSettings()
    {

        if ($this->gc_size_albumlisting == "")
        {
            $this->gc_size_albumlisting = serialize(array("110", "110", "crop"));
        }
        if ($this->gc_size_detailview == "")
        {
            $this->gc_size_detailview = serialize(array("110", "110", "crop"));
        }
    }

    /**
     * Hilfsmethode
     * Gibt die Anzahl der Gallery_Creator Inhaltselemente auf einer Seite zurück
     *
     * @param integer
     * @return integer
     */
    public function countGcContentElementsOnPage($intPageId = null)
    {

        if ($intPageId)
        {
            $objPage = $this->Database->prepare('SELECT * FROM tl_page WHERE id=?')->execute($intPageId);
        }
        else
        {
            global $objPage;
        }

        //kontrollieren, ob Weiterleitung zu detailview  moeglich ist
        //Keine Weiterleitung moeglich, bei mehreren aktivierten GALLERY_CREATOR Inhaltselementen im selben Artikel
        $objArticlesOfCurrentPage = $this->Database->prepare('SELECT id FROM tl_article WHERE pid=? AND published=?')
            ->execute($objPage->id, 1);

        $arrArticlesOfCurrentPage = array();
        while ($objArticlesOfCurrentPage->next())
        {
            $arrArticlesOfCurrentPage[] = (int)$objArticlesOfCurrentPage->id;
        }

        $gcElementCounter = 0;
        $objCE = $this->Database->prepare('SELECT pid FROM tl_content WHERE type=? AND invisible=?')
            ->execute('gallery_creator', 0);
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
     * Hilfsmethode
     * Ueberprueft, ob bei nur einem ausgewaehlten Album direkt zur Thumnailuebersicht des Albums weitergeleitet werden soll.
     *
     * @return bool
     */
    protected function doRedirectOnSingleAlbum()
    {

        if (TL_MODE == 'BE')
        {
            return false;
        }
        //if all albums are published
        $objAlb = $this->Database->prepare('SELECT count(id) AS countPublishedAlbums FROM tl_gallery_creator_albums WHERE published=?')
            ->execute('1');
        if ($this->gc_publish_all_albums && $objAlb->countPublishedAlbums == 1)
        {
            $singleAlbum = true;
        }
        if ($this->gc_publish_all_albums && $objAlb->countPublishedAlbums > 1)
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
        if ($this->countGcContentElementsOnPage() == 1 && $singleAlbum && $this->gc_redirectSingleAlb)
        {
            return true;
        }

        return false;
    }

    /**
     * evaluate the request and extracts the album-id and the content-element-id
     */
    public function evalRequestVars()
    {

        if ($this->gc_publish_all_albums != 1)
        {
            if (!unserialize($this->gc_publish_albums))
            {
                return;
            }
        }

        if (\Input::get('items'))
        {
            //aktueller Albumalias
            $this->strAlbumalias = \Input::get('items');

            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $this->feUserAuthentication($this->strAlbumalias);

            //fuer jw_imagerotator ajax-requests
            if (\Input::get('jw_imagerotator'))
            {
                return;
            }
        }

        //wenn nur ein Album ausgewaehlt wurde und Weiterleitung in den Inhaltselementeinstellungen aktiviert wurde, wird weitergeleitet
        if ($this->doRedirectOnSingleAlbum())
        {
            $arrAlbId = unserialize($this->gc_publish_albums);
            if ($this->gc_publish_all_albums)
            {
                //if all albums are selected
                $objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE published=?')
                    ->execute('1');
            }
            else
            {
                $objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')
                    ->execute($arrAlbId[0]);
            }

            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $this->feUserAuthentication($objAlbum->alias);

            \Input::setGet('items', $objAlbum->alias);
            $this->strAlbumalias = $objAlbum->alias;
        }

        // Get the Album Id
        if (\Input::get('items'))
        {
            $objAlbum = \GalleryCreatorAlbumsModel::findByAlias($this->strAlbumalias);
            if ($objAlbum !== null)
            {
                $this->intAlbumId = $objAlbum->id;
                $this->DETAIL_VIEW = true;
            }
        }
    }

    /**
     * Check if fe-user is allowed watching this album
     *
     * @param string
     * @return bool
     */
    protected function feUserAuthentication($strAlbumalias)
    {

        if (TL_MODE == 'FE')
        {
            $objAlb = \GalleryCreatorAlbumsModel::findByAlias($strAlbumalias);
            if ($objAlb !== null)
            {

                if (!$objAlb->protected)
                {
                    return true;
                }

                $this->import('FrontendUser', 'User');
                $groups = deserialize($objAlb->groups);

                if (!FE_USER_LOGGED_IN || !is_array($groups) || count($groups) < 1 || !array_intersect($groups, $this->User->groups))
                {
                    // abort script and display authentification error
                    $strContent = sprintf("<div>\r\n<h1>%s</h1>\r\n<p>%s</p>\r\n</div>", $GLOBALS['TL_LANG']['gallery_creator']['fe_authentification_error'][0], $GLOBALS['TL_LANG']['gallery_creator']['fe_authentification_error'][1]);
                    die($strContent);
                }
            }
        }

        return true;
    }

    /**
     * responds to ajax-requests
     *
     * @access public
     * @return string
     */
    public function generateAjax()
    {

        //gibt ein Array mit allen Bildinformationen des Bildes mit der id imageId zurück
        if (\Input::get('isAjax') && \Input::get('getImage') && strlen(\Input::get('imageId')))
        {
            $arrPicture = $this->getPictureInformationArray(\Input::get('imageId'), null, \Input::get('action'));

            return json_encode($arrPicture);
        }

        //thumbslider der Albenübersicht
        if (\Input::get('isAjax') && \Input::get('thumbSlider'))
        {
            $this->checkThumbSizeSettings();
            $arrSize = unserialize($this->gc_size_albumlisting);

            $objAlbum = $this->Database->prepare('SELECT thumb,alias FROM tl_gallery_creator_albums WHERE id=?')
                ->execute(\Input::get('AlbumId'));

            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $this->feUserAuthentication($objAlbum->alias);
            if (GALLERY_CREATOR_ALBUM_AUTHENTIFICATION_ERROR === true)
            {
                return false;
            }

            $objPictures = $this->Database->prepare('SELECT count(id) AS Anzahl FROM tl_gallery_creator_pictures WHERE published=? AND pid=? AND id!=?')
                ->execute(1, \Input::get('AlbumId'), $objAlbum->thumb);
            if ($objPictures->Anzahl < 2)
            {
                return json_encode(array('thumbPath' => ''));
            }

            $limit = \Input::get('limit');
            $objPicture = $this->Database->prepare('SELECT name, uuid FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY id')
                ->limit(1, $limit)
                ->execute(1, \Input::get('AlbumId'), $objAlbum->thumb);

            $objFile = \FilesModel::findByUuid($objPicture->uuid);
            if ($objFile !== null)
            {
                $jsonUrl = array(
                    'thumbPath' => \Image::get($objFile->path, $arrSize[0], $arrSize[1], $arrSize[2]),
                    'eventId'   => \Input::get('eventId')
                );
            }


            echo json_encode($jsonUrl);
            exit;
        }

        //Detailansicht nur mit Lightbox, für ce_gc_mediabox template
        if (\Input::get('isAjax') && \Input::get('LightboxSlideshow') && \Input::get('albumId'))
        {
            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')
                ->execute(\Input::get('albumId'));

            $this->feUserAuthentication($objAlbum->alias);
            if (GALLERY_CREATOR_ALBUM_AUTHENTIFICATION_ERROR === true)
            {
                return false;
            }

            // Init Album Visit Counter
            $this->initCounter(\Input::get('albumId'));

            $json = "";

            // sorting direction
            $ceType = \Input::get('action');
            if ($ceType == 'cte')
            {
                $sorting = $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;
            }
            else
            {
                $sorting = 'sorting ASC';
            }

            $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY ' . $sorting)
                ->execute(1, \Input::get('albumId'));

            while ($objPicture->next())
            {
                $objFile = \FilesModel::findByUuid($objPicture->uuid);
                if ($objFile !== null)
                {
                    $href = $objFile->path;
                    $href = trim($objPicture->socialMediaSRC) != "" ? trim($objPicture->socialMediaSRC) : $href;
                    $href = trim($objPicture->localMediaSRC) != "" ? trim($objPicture->localMediaSRC) : $href;

                    $json .= specialchars($href) . "###";
                    $json .= specialchars($objPicture->comment) . "###";
                    $json .= specialchars($objPicture->id) . " ***";
                }
            }
            $jsonUrl = array('arrImage' => $json);
            echo json_encode($jsonUrl);
            exit;
        }

        return null;
    }

    /**
     * Generate the xml-output for jwImagerotator
     *
     * @param string
     * @return string
     */
    protected function getJwImagerotatorXml($strAlbumalias)
    {

        $objAlbum = $this->Database->prepare('SELECT id, owners_name FROM tl_gallery_creator_albums WHERE alias=? and published=1')
            ->execute($strAlbumalias);
        $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY sorting')
            ->execute('1', $objAlbum->id);

        //playlist xml output
        $xml = "<playlist version='1' xmlns='http://xspf.org/ns/0/'>\n";
        $xml .= "<trackList>\n";
        while ($objPicture->next())
        {
            $objFile = \FilesModel::findByUuid($objPicture->uuid);
            if ($objFile !== null)
            {
                $caption = trim($objPicture->comment) != "" ? $objPicture->comment : basename($objFile->path);
                $xml .= "\t<track>\n";
                $xml .= "\t\t<title>" . specialchars($caption) . "</title>\n";
                $xml .= "\t\t<location>" . $objFile->path . "</location>\n";
                $xml .= "\t</track>\n";
            }
        }
        $xml .= "</trackList>\n";
        $xml .= "</playlist>\n";

        return $xml;
    }

    /**
     * Returns the path to the preview-thumbnail of an album
     * @param $intAlbumId
     * @return array
     */
    public function getAlbumPreviewThumb($intAlbumId)
    {

        // Predefine thumb
        $arrThumb = array(
            'name' => basename($this->defaultThumb),
            'path' => $this->defaultThumb
        );

        $objAlb = \GalleryCreatorAlbumsModel::findByPk($intAlbumId);
        if ($objAlb->thumb > 0)
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
            if ($oFile !== null && is_file(TL_ROOT . '/' . $oFile->path))
            {
                $arrThumb = array(
                    'name' => basename($oFile->path),
                    'path' => $oFile->path
                );
            }
        }

        return $arrThumb;
    }

    /**
     * Sets the template-vars for the selected album
     *
     * @param integer
     * @param string
     */
    protected function getAlbumTemplateVars($intAlbumId, $strContentType)
    {

        global $objPage;

        // Get the page model
        $objPageModel = \PageModel::findByPk($objPage->id);
        if ($strContentType != 'fmd' && $strContentType != 'cte')
        {
            $strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
            __error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
        }


        // Load the current album from db
        $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')
            ->execute($intAlbumId);

        // add meta tags to the page object
        if (TL_MODE == 'FE' && $this->DETAIL_VIEW === true && $objAlbum !== null)
        {
            $objPage->description = $objAlbum->description != '' ? specialchars($objAlbum->description) : $objPage->description;
            $GLOBALS['TL_KEYWORDS'] = ltrim($GLOBALS['TL_KEYWORDS'] . ',' . specialchars($objAlbum->keywords), ',');
        }

        //store all album-data in the array
        $objAlbum->reset();
        $this->Template->arrAlbumdata = $objAlbum->fetchAssoc();

        // store the data of the current album in the session
        $_SESSION['gallery_creator']['CURRENT_ALBUM'] = $this->Template->arrAlbumdata;

        //der back-Link
        $this->Template->backLink = $this->generateBackLink($strContentType, $intAlbumId);
        //Der dem Bild uebergeordnete Albumname
        $this->Template->Albumname = $objAlbum->name;
        // Albumbesucher (Anzahl Klicks)
        $this->Template->visitors = $objAlbum->vistors;
        //Der Kommentar zum gewaehlten Album
        $this->Template->albumComment = $objPage->outputFormat == 'xhtml' ? \String::toXhtml($objAlbum->comment) : \String::toHtml5($objAlbum->comment);
        // In der Detailansicht kann optional ein Artikel vor dem Album hinzugefuegt werden
        $this->Template->insertArticlePre = $objAlbum->insert_article_pre ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_pre) : null;
        // In der Detailansicht kann optional ein Artikel nach dem Album hinzugefuegt werden
        $this->Template->insertArticlePost = $objAlbum->insert_article_post ? sprintf('{{insert_article::%s}}', $objAlbum->insert_article_post) : null;
        //Das Event-Datum des Albums als unix-timestamp
        $this->Template->eventTstamp = $objAlbum->date;
        //Das Event-Datum des Albums formatiert
        $this->Template->eventDate = \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date);
        //Abstaende
        $this->Template->imagemargin = $this->DETAIL_VIEW ? $this->generateMargin(deserialize($this->gc_imagemargin_detailview), 'margin') : $this->generateMargin(deserialize($this->gc_imagemargin_albumlisting), 'margin');
        //Anzahl Spalten pro Reihe
        $this->Template->colsPerRow = $this->gc_rows == "" ? 4 : $this->gc_rows;
        //Pfad zur xml-Ausgabe fuer jw_imagerotator
        $this->Template->jw_imagerotator_path = TL_MODE == 'FE' ? $objPageModel->getFrontendUrl(($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $objAlbum->alias . '/jw_imagerotator/true') : null;
        //Inhaltselement Id anhaengen wenn es sich um ein Inhaltselement handelt
        if ($strContentType == 'cte')
        {
            //Pfad zur xml-Ausgabe fuer jw_imagerotator
            if ($this->countGcContentElementsOnPage() > 1)
            {
                $this->Template->jw_imagerotator_path = TL_MODE == 'FE' ? $this->Template->jw_imagerotator_path . '/ce/' . $this->id : null;
            }
        }

        $this->Template->objElement = $this;
    }

    /**
     * Hilfsmethode
     * generiert den back-Link
     *
     * @param string
     * @param int
     * @return string
     */
    public function generateBackLink($strContentType, $intAlbumId)
    {

        global $objPage;

        if (TL_MODE == 'BE')
        {
            return false;
        }

        if ($strContentType == 'cte')
        {
            //Nur, wenn nicht automatisch zu overview weitergeleitet wurde, wird der back Link angezeigt
            if ($this->doRedirectOnSingleAlbum())
            {
                return null;
            }
        }

        // Get the page model
        $objPageModel = \PageModel::findByPk($objPage->id);

        //generiert den Link zum Parent-Album
        if ($this->gc_hierarchicalOutput && GcHelpers::getParentAlbum($intAlbumId))
        {
            $arrParentAlbum = GcHelpers::getParentAlbum($intAlbumId);
            return $objPageModel->getFrontendUrl(($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . $arrParentAlbum["alias"]);
        }

        //generiert den Link zur Startuebersicht unter Beruecksichtigung der pagination
        $url = $objPageModel->getFrontendUrl();
        $url .= isset($_SESSION['gallery_creator']['PAGINATION']) ? '?page=' . $_SESSION['gallery_creator']['PAGINATION'] : '';

        return $url;
    }

    /**
     * initCounter
     *
     * @param integer
     * @return string
     */
    public static function initCounter($intAlbumId)
    {

        if (preg_match('/bot|sp[iy]der|crawler|lib(?:cur|www)|search|archive/i', $_SERVER['HTTP_USER_AGENT']))
        {
            // do not count spiders/bots
            return;
        }

        if (TL_MODE == 'FE')
        {
            $objDb = \Database::getInstance()
                ->prepare('SELECT visitors, visitors_details FROM tl_gallery_creator_albums WHERE id=?')
                ->execute($intAlbumId);
            if (strpos($objDb->visitors_details, $_SERVER['REMOTE_ADDR']))
            {
                // return if the visitor is allready registered
                return;
            }

            // increase the number of visitors by one
            $intCount = (int)$objDb->visitors + 1;

            $arrVisitors = strlen($objDb->visitors_details) ? unserialize($objDb->visitors_details) : array();
            if (is_array($arrVisitors))
            {
                // keep visiors data in the db unless 20 other users visited the album
                if (count($arrVisitors) == 20)
                {
                    // slice the last position
                    $arrVisitors = array_slice($arrVisitors, 0, count($arrVisitors) - 1);
                }
            }
            else
            {
                $set = array('visitors_details' => '');
                $objDbUpd = \Database::getInstance()->prepare('UPDATE tl_gallery_creator_albums %s WHERE id=?')
                    ->set($set)->execute($intAlbumId);
            }

            //build up the array
            $newVisitor = array(
                $_SERVER['REMOTE_ADDR'] => array(
                    'ip'         => $_SERVER['REMOTE_ADDR'],
                    'pid'        => $intAlbumId,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                    'tstamp'     => time(),
                    'url'        => \Environment::get('request'),
                )
            );

            if (count($arrVisitors))
            {
                // insert the element to the beginning of the array
                array_unshift($arrVisitors, $newVisitor);
            }
            else
            {
                // create the new array
                $arrVisitors = array();
                $arrVisitors[] = array($_SERVER['REMOTE_ADDR'] => $newVisitor);
            }

            // update database
            $set = array(
                'visitors'         => $intCount,
                'visitors_details' => serialize($arrVisitors)
            );
            $objDb = \Database::getInstance()->prepare('UPDATE tl_gallery_creator_albums %s WHERE id=?')->set($set)
                ->execute($intAlbumId);
        }
    }

}