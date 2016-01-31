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
namespace MCupic\GalleryCreator;

/**
 * Class GalleryCreator
 *
 * Provide methods regarding gallery_creator albums.
 *
 * @copyright  Marko Cupic 2015
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
abstract class GalleryCreator extends \Module
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
        $this->moduleType = 'cte';

        // Ajax Requests
        if (TL_MODE == 'FE' && $this->Environment->get('isAjaxRequest'))
        {
            $this->generateAjax();
        }

        if (TL_MODE == 'BE')
        {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $GLOBALS['TL_LANG']['CTE']['gallery_creator_ce'][0] . ' ###';

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

    /**
     * @param \Module $objModule
     * @param null $objAlbum
     * @return mixed
     */
    protected function callGcGenerateFrontendTemplateHook(\Module $objModule, $objAlbum = null)
    {

        // HOOK: modify the page or template object
        if (isset($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']) && is_array($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']))
        {
            foreach ($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate'] as $callback)
            {
                $this->import($callback[0]);
                $objModule->Template = $this->$callback[0]->$callback[1]($objModule, $objAlbum);
            }
        }
        return $objModule->Template;
    }


    /**
     * evaluate the url data
     * extracts the album-id, the content-element-id, etc.
     */
    public function getUrlParams()
    {

        if (!$this->gc_publish_all_albums)
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
            $this->authenticate($this->strAlbumalias);

            $objAlbum = \GalleryCreatorAlbumsModel::findByAlias($this->strAlbumalias);
            if ($objAlbum !== null)
            {
                $this->intAlbumId = $objAlbum->id;
                $this->DETAIL_VIEW = true;
            }
        }
    }

    /**
     * Check if fe-user has access to a certain album
     *
     * @param string
     * @return bool
     */
    protected function authenticate($strAlbumalias)
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
                    return false;
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
            exit;
        }
    }


    /**
     * Returns the path to the preview-thumbnail of an album
     * @param $intAlbumId
     * @return array
     */
    public function getAlbumPreviewThumb($intAlbumId)
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
     * Set the template-vars to the template object for the selected album
     *
     * @param integer
     */
    protected function getAlbumTemplateVars($intAlbumId)
    {

        global $objPage;

        // Get the page model
        $objPageModel = \PageModel::findByPk($objPage->id);

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
        $this->Template->backLink = $this->generateBackLink($intAlbumId);
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
        $this->Template->imagemargin = $this->DETAIL_VIEW ? $this->generateMargin(deserialize($this->gc_imagemargin_detailview), 'margin') : $this->generateMargin(deserialize($this->gc_imagemargin_albumlisting), 'margin');
        //Anzahl Spalten pro Reihe
        $this->Template->colsPerRow = $this->gc_rows == "" ? 4 : $this->gc_rows;

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
    public function generateBackLink($intAlbumId)
    {

        global $objPage;

        if (TL_MODE == 'BE')
        {
            return false;
        }

        // Get the page model
        $objPageModel = \PageModel::findByPk($objPage->id);

        //generiert den Link zum Parent-Album
        if ($this->gc_hierarchicalOutput && \GalleryCreatorAlbumsModel::getParentAlbum($intAlbumId))
        {
            $arrParentAlbum = \GalleryCreatorAlbumsModel::getParentAlbum($intAlbumId);
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
                if (count($arrVisitors) == 50)
                {
                    // slice the last position
                    $arrVisitors = array_slice($arrVisitors, 0, count($arrVisitors) - 1);
                }
            }
            else
            {
                $set = array('visitors_details' => '');
                \Database::getInstance()->prepare('UPDATE tl_gallery_creator_albums %s WHERE id=?')
                    ->set($set)->execute($intAlbumId);
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
                'visitors' => $intCount,
                'visitors_details' => serialize($arrVisitors)
            );
            $objDb = \Database::getInstance()->prepare('UPDATE tl_gallery_creator_albums %s WHERE id=?')->set($set)
                ->execute($intAlbumId);
        }
    }

}