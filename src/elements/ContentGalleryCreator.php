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
 * Class ContentGalleryCreator
 *
 * Provide methods regarding gallery_creator albums.
 * @copyright  Marko Cupic 2016
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class ContentGalleryCreator extends \ContentElement
{
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
     * list_view, detail_view, single_image
     * @var
     */
    protected $viewMode;

    /**
     * Album-ID
     * @var integer
     */
    protected $intAlbumId;

    /**
     * Albumalias
     * @var string
     */
    protected $strAlbumalias;

    /**
     * Selected albums as array e.g. : [2,4,5,9]
     * @var
     */
    protected $arrSelectedAlbums;


    /**
     * Set the template
     * @return string
     */
    public function generate()
    {

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


        if ($_SESSION['GcRedirectToAlbum'])
        {
            \Input::setGet('items', $_SESSION['GcRedirectToAlbum']);
            unset($_SESSION['GcRedirectToAlbum']);
        }

        // Ajax Requests
        if (TL_MODE == 'FE' && \Environment::get('isAjaxRequest'))
        {
            $this->generateAjax();
        }


        // set the item from the auto_item parameter
        if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
        {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        if (strlen(\Input::get('items')))
        {
            $this->viewMode = 'detail_view';
        }

        // store the pagination variable page in the current session
        if (!\Input::get('items'))
        {
            unset($_SESSION['gallery_creator']['PAGINATION']);
        }
        if (\Input::get('page') && $this->viewMode != 'detail_view')
        {
            $_SESSION['gallery_creator']['PAGINATION'] = \Input::get('page');
        }


        if ($this->gc_publish_all_albums)
        {
            // if all albums should be shown
            $this->arrSelectedAlbums = $this->listAllAlbums();
        }
        else
        {
            // if only selected albums should be shown
            $this->arrSelectedAlbums = deserialize($this->gc_publish_albums, true);
        }

        // clean array from unpublished or empty or protected albums
        foreach ($this->arrSelectedAlbums as $key => $albumId)
        {
            // Get all not empty albums
            $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE (SELECT COUNT(id) FROM tl_gallery_creator_pictures WHERE pid = ? AND published=?) > 0 AND id=? AND published=?')->execute($albumId, 1, $albumId, 1);

            // if the album doesn't exist
            if (!$objAlbum->numRows && !\GalleryCreatorAlbumsModel::hasChildAlbums($objAlbum->id) && !$this->gc_hierarchicalOutput)
            {
                unset($this->arrSelectedAlbums[$key]);
                continue;
            }

            // remove id from $this->arrSelectedAlbums if user is not allowed
            if (TL_MODE == 'FE' && $objAlbum->protected == true)
            {
                if (!$this->authenticate($objAlbum->alias))
                {
                    unset($this->arrSelectedAlbums[$key]);
                    continue;
                }
            }
        }
        // build up the new array
        $this->arrSelectedAlbums = array_values($this->arrSelectedAlbums);

        // abort if no album is selected
        if (empty($this->arrSelectedAlbums))
        {
            return '';
        }


        // Detail view:
        // Authenticate and get album alias and album id
        if (\Input::get('items'))
        {

            $objAlbum = \GalleryCreatorAlbumsModel::findByAlias(\Input::get('items'));
            if ($objAlbum !== null)
            {
                $this->intAlbumId = $objAlbum->id;
                $this->strAlbumalias = $objAlbum->alias;
                $this->viewMode = 'detail_view';
            }
            else
            {
                return '';
            }

            //Authentifizierung bei vor Zugriff geschuetzten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            if (!$this->authenticate($this->strAlbumalias))
            {
                return '';
            }
        }

        $this->viewMode = $this->viewMode ? $this->viewMode : 'list_view';
        $this->viewMode = strlen(\Input::get('img')) ? 'single_image' : $this->viewMode;


        if ($this->viewMode == 'list_view')
        {
            // Redirect to detailview if there is only one album
            if (count($this->arrSelectedAlbums) == 1 && $this->gc_redirectSingleAlb)
            {
                $_SESSION['GcRedirectToAlbum'] = \GalleryCreatorAlbumsModel::findByPk($this->arrSelectedAlbums[0])->alias;
                $this->reload();
            }

            //Hierarchische Ausgabe
            if ($this->gc_hierarchicalOutput)
            {
                foreach ($this->arrSelectedAlbums as $k => $albumId)
                {
                    $objAlbum = \GalleryCreatorAlbumsModel::findByPk($albumId);
                    if ($objAlbum->pid > 0)
                    {
                        unset($this->arrSelectedAlbums[$k]);
                    }
                }
                $this->arrSelectedAlbums = array_values($this->arrSelectedAlbums);
                if (empty($this->arrSelectedAlbums))
                {
                    return '';
                }
            }
        }

        if ($this->viewMode == 'detail_view')
        {
            // for security reasons...
            if (!$this->gc_publish_all_albums && !in_array($this->intAlbumId, $this->arrSelectedAlbums))
            {
                return '';
            }
        }

        //assigning the frontend template
        $this->strTemplate = $this->gc_template != "" ? $this->gc_template : $this->strTemplate;
        $this->Template = new \FrontendTemplate($this->strTemplate);


        return parent::generate();
    }

    /**
     * Generate module
     */
    protected function compile()
    {

        global $objPage;


        switch ($this->viewMode)
        {
            case 'list_view' :

                // pagination settings
                $limit = $this->gc_AlbumsPerPage;
                $offset = 0;
                if ($limit > 0)
                {
                    $page = \Input::get('page') ? \Input::get('page') : 1;
                    $offset = ($page - 1) * $limit;
                    // count albums
                    $itemsTotal = count($this->arrSelectedAlbums);
                    // create pagination menu
                    $numberOfLinks = $this->gc_PaginationNumberOfLinks < 1 ? 7 : $this->gc_PaginationNumberOfLinks;
                    $objPagination = new \Pagination($itemsTotal, $limit, $numberOfLinks);
                    $this->Template->pagination = $objPagination->generate("\n ");
                }

                if ($limit == 0 || $limit > count($this->arrSelectedAlbums))
                {
                    $limit = count($this->arrSelectedAlbums);
                }
                $arrAlbums = array();
                for ($i = $offset; $i < $offset + $limit; $i++)
                {
                    $arrAlbums[] = GcHelpers::getAlbumInformationArray($this->arrSelectedAlbums[$i], $this);
                }

                // Add css classes
                if (count($arrAlbums) > 0)
                {
                    $arrAlbums[0]['cssClass'] .= ' first';
                    $arrAlbums[count($arrAlbums) - 1]['cssClass'] .= ' last';
                }

                $this->Template->imagemargin = $this->generateMargin(unserialize($this->gc_imagemargin_albumlisting));
                $this->Template->arrAlbums = $arrAlbums;


                // Call gcGenerateFrontendTemplateHook
                $this->Template = $this->callGcGenerateFrontendTemplateHook($this);
                break;

            case 'detail_view':
                // generate the subalbum array
                if ($this->gc_hierarchicalOutput)
                {
                    $arrSubalbums = GcHelpers::getSubalbumsInformationArray($this->intAlbumId, $this);
                    $this->Template->subalbums = count($arrSubalbums) ? $arrSubalbums : null;
                }

                // pagination settings
                $limit = $this->gc_ThumbsPerPage;
                $offset = 0;
                if ($limit > 0)
                {
                    $page = \Input::get('page') ? \Input::get('page') : 1;
                    $offset = ($page - 1) * $limit;

                    // count albums
                    $objTotal = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE published=? AND pid=? GROUP BY ?')->execute('1', $this->intAlbumId, 'id');
                    $itemsTotal = $objTotal->numRows;

                    // create the pagination menu
                    $numberOfLinks = $this->gc_PaginationNumberOfLinks < 1 ? 7 : $this->gc_PaginationNumberOfLinks;
                    $objPagination = new \Pagination($itemsTotal, $limit, $numberOfLinks);
                    $this->Template->pagination = $objPagination->generate("\n ");
                }

                // picture sorting
                $str_sorting = $this->gc_picture_sorting == '' || $this->gc_picture_sorting_direction == '' ? 'sorting ASC' : $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;
                // sort by name is done below
                $str_sorting = str_replace('name', 'id', $str_sorting);
                $objPictures = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY ' . $str_sorting);
                if ($limit > 0)
                {
                    $objPictures->limit($limit, $offset);
                }
                $objPictures = $objPictures->execute(1, $this->intAlbumId);

                // build up $arrPictures
                $arrPictures = array();
                $auxBasename = array();
                while ($objPictures->next())
                {
                    $objFilesModel = \FilesModel::findByUuid($objPictures->uuid);
                    $basename = 'undefined';
                    if ($objFilesModel !== null)
                    {
                        $basename = $objFilesModel->name;
                    }
                    $auxBasename[] = $basename;
                    $arrPictures[$objPictures->id] = GcHelpers::getPictureInformationArray($objPictures->id, $this);
                }

                // sort by basename
                if ($this->gc_picture_sorting == 'name')
                {
                    if ($this->gc_picture_sorting_direction == 'ASC')
                    {
                        array_multisort($arrPictures, SORT_STRING, $auxBasename, SORT_ASC);
                    }
                    else
                    {
                        array_multisort($arrPictures, SORT_STRING, $auxBasename, SORT_DESC);
                    }
                }

                $arrPictures = array_values($arrPictures);

                // store $arrPictures in the template variable
                $this->Template->arrPictures = $arrPictures;

                // generate other template variables
                $this->getAlbumTemplateVars($this->intAlbumId);

                // init the counter
                $this->initCounter($this->intAlbumId);

                // Call gcGenerateFrontendTemplateHook
                $objAlbum = \GalleryCreatorAlbumsModel::findByAlias($this->strAlbumalias);
                $this->Template = $this->callGcGenerateFrontendTemplateHook($this, $objAlbum);
                break;

            case 'single_image' :
                $objAlbum = \GalleryCreatorAlbumsModel::findByAlias(\Input::get('items'));
                if ($objAlbum === null)
                {
                    die('Invalid album alias: ' . \Input::get('items'));
                }

                $objPic = \Database::getInstance()->prepare("SELECT * FROM tl_gallery_creator_pictures WHERE pid=? AND name LIKE '" . \Input::get('img') . ".%'")->execute($objAlbum->id);
                if (!$objPic->numRows)
                {
                    die(sprintf('File with filename "%s" does not exist in album with alias "%s".', \Input::get('img'), \Input::get('items')));
                }

                $picId = $objPic->id;
                $published = $objPic->published ? true : false;
                $published = $objAlbum->published ? $published : false;

                // for security reasons...
                if (!$published || (!$this->gc_publish_all_albums && !in_array($this->intAlbumId, $this->arrSelectedAlbums)))
                {
                    die("Picture with id " . $picId . " is either not published or not available or you haven't got enough permission to watch it!!!");
                }


                // picture sorting
                $str_sorting = $this->gc_picture_sorting == '' || $this->gc_picture_sorting_direction == '' ? 'sorting ASC' : $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;
                $objPictures = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY ' . $str_sorting);
                $objPictures = $objPictures->execute('1', $this->intAlbumId);

                // build up $arrPictures
                $arrIDS = array();
                $i = 0;
                $currentIndex = null;
                while ($objPictures->next())
                {
                    if ($picId == $objPictures->id)
                    {
                        $currentIndex = $i;
                    }
                    $arrIDS[] = $objPictures->id;
                    $i++;
                }

                $arrPictures = array();

                if (count($arrIDS))
                {
                    // store $arrPictures in the template variable
                    $arrPictures['prev'] = GcHelpers::getPictureInformationArray($arrIDS[$currentIndex - 1], $this);
                    $arrPictures['current'] = GcHelpers::getPictureInformationArray($arrIDS[$currentIndex], $this);
                    $arrPictures['next'] = GcHelpers::getPictureInformationArray($arrIDS[$currentIndex + 1], $this);

                    // add navigation href's to the template
                    $this->Template->prevHref = $arrPictures['prev']['single_image_url'];
                    $this->Template->nextHref = $arrPictures['next']['single_image_url'];

                    if ($currentIndex == 0)
                    {
                        $arrPictures['prev'] = null;
                        $this->Template->prevHref = null;
                    }

                    if ($currentIndex == count($arrIDS) - 1)
                    {
                        $arrPictures['next'] = null;
                        $this->Template->nextHref = null;
                    }

                    if (count($arrIDS) == 1)
                    {
                        $arrPictures['next'] = null;
                        $arrPictures['prev'] = null;
                        $this->Template->nextHref = null;
                        $this->Template->prevItem = null;
                    }
                }
                // Get the page model
                $objPageModel = \PageModel::findByPk($objPage->id);
                $this->Template->returnHref = $objPageModel->getFrontendUrl(($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . \Input::get('items'), $objPage->language);
                $this->Template->arrPictures = $arrPictures;

                // generate other template variables
                $this->getAlbumTemplateVars($this->intAlbumId);

                // init the counter
                $this->initCounter($this->intAlbumId);

                // Call gcGenerateFrontendTemplateHook
                $this->Template = $this->callGcGenerateFrontendTemplateHook($this, $objAlbum);

                break;

        }
        // end switch
    }

    /**
     * return a sorted array with all album ID's
     * @param int $pid
     * @return array
     */
    protected function listAllAlbums($pid = 0)
    {

        $strSorting = $this->gc_sorting == '' || $this->gc_sorting_direction == '' ? 'date DESC' : $this->gc_sorting . ' ' . $this->gc_sorting_direction;
        $objAlbums = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid=? AND published=? ORDER BY ' . $strSorting)->execute($pid, 1);
        return $objAlbums->fetchEach('id');
    }


    /**
     * @param ContentGalleryCreator $objModule
     * @param null $objAlbum
     * @return \BackendTemplate|\FrontendTemplate|object
     */
    protected function callGcGenerateFrontendTemplateHook(ContentGalleryCreator $objModule, $objAlbum = null)
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

        //gibt ein Array mit allen Bildinformationen des Bildes mit der id imageId zurueck
        if (\Input::get('isAjax') && \Input::get('getImage') && strlen(\Input::get('imageId')))
        {
            $arrPicture = $this->getPictureInformationArray(\Input::get('imageId'), null, \Input::get('action'));

            echo json_encode($arrPicture);
            exit;
        }

        //Detailansicht nur mit Lightbox, für ce_gc_mediabox template
        if (\Input::get('isAjax') && \Input::get('LightboxSlideshow') && \Input::get('albumId'))
        {
            //Authentifizierung bei vor Zugriff geschützten Alben, dh. der Benutzer bekommt, wenn nicht berechtigt, nur das Albumvorschaubild zu sehen.
            $objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')
                ->execute(\Input::get('albumId'));

            if (!$this->authenticate($objAlbum->alias))
            {
                return false;
            }

            // Init Album Visit Counter
            $this->initCounter(\Input::get('albumId'));


            // sorting direction
            $sorting = $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;

            $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY ' . $sorting)
                ->execute(1, \Input::get('albumId'));

            $json = "";

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
            echo json_encode(array('arrImage' => $json));
        }
        exit;
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
     * @param $intAlbumId
     */
    protected function getAlbumTemplateVars($intAlbumId)
    {

        global $objPage;

        // Load the current album from db
        $objAlbum = \GalleryCreatorAlbumsModel::findByPk($intAlbumId);
        if ($objAlbum === null)
        {
            return;
        }

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
        $this->Template->imagemargin = $this->viewMode == 'detail_view' ? $this->generateMargin(deserialize($this->gc_imagemargin_detailview), 'margin') : $this->generateMargin(deserialize($this->gc_imagemargin_albumlisting), 'margin');
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

            $objAlbum = \GalleryCreatorAlbumsModel::findByPk($intAlbumId);
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