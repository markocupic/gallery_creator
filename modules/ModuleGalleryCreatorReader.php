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
use Contao\GalleryCreatorAlbumsModel;
use Contao\GalleryCreatorPicturesModel;
use Contao\Pagination;
use Contao\FilesModel;

/**
 * Front end module "event reader".
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class ModuleGalleryCreatorReader extends ModuleGalleryCreator
{

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_gallery_creator_reader';


    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        $this->start = microtime(true);
        if (TL_MODE == 'BE')
        {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['gallery_creator_reader'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }
        
        // Set the item from the auto_item parameter
        if (!isset($_GET['albums']) && Config::get('useAutoItem') && isset($_GET['auto_item']))
        {
            Input::setGet('albums', Input::get('auto_item'));
        }

        // Do not index or cache the page if no event has been specified
        if (!Input::get('albums'))
        {
            /** @var PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        $this->gc_galleries = $this->sortOutProtected(deserialize($this->gc_galleries));

        // Do not index or cache the page if there are no calendars
        if (!is_array($this->gc_galleries) || empty($this->gc_galleries))
        {
            /** @var PageModel $objPage */
            global $objPage;

            $objPage->noSearch = 1;
            $objPage->cache = 0;

            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        /** @var PageModel $objPage */
        global $objPage;

        $this->Template->referer = 'javascript:history.go(-1)';
        $this->Template->back = $GLOBALS['TL_LANG']['MSC']['goBack'];
        // Get the current event
        $objAlbum = GalleryCreatorAlbumsModel::findPublishedByParentAndIdOrAlias(Input::get('albums'), $this->gc_galleries);

        if (null === $objAlbum)
        {
            /** @var PageError404 $objHandler */
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
        }


        // count pictures
        $objTotal = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE published=? AND pid=?')->execute('1', $objAlbum->id);
        $total = $objTotal->numRows;

        // pagination settings
        $limit = $this->gc_thumbsPerPage;
        $offset = 0;
        if ($limit > 0)
        {
            // Get the current page
            $id = 'page_g' . $this->id;
            $page = (Input::get($id) !== null) ? Input::get($id) : 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total / $limit), 1))
            {
                /** @var PageError404 $objHandler */
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
            }
            $offset = ($page - 1) * $limit;


            // create the pagination menu
            $numberOfLinks = $this->gc_paginationNumberOfLinks ? $this->gc_paginationNumberOfLinks : 7;
            $objPagination = new Pagination($total, $limit, $numberOfLinks, $id);
            $this->Template->pagination = $objPagination->generate("\n  ");
        }

        // picture sorting
        $str_sorting = $this->gc_picture_sorting == '' || $this->gc_picture_sorting_direction == '' ? 'sorting ASC' : $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;

        // sort by name is done below
        $str_sorting = str_replace('name', 'id', $str_sorting);
        $arrOptions = array(
            'column' => array('tl_gallery_creator_pictures.published=?', 'tl_gallery_creator_pictures.pid=?'),
            'value' => array('1', $objAlbum->id),
            'order' => $str_sorting
        );
        if ($limit > 0)
        {
            $arrOptions['limit'] = $limit;
            $arrOptions['offset'] = $offset;
        }
        $objPictures = GalleryCreatorPicturesModel::findAll($arrOptions);
        if ($objPictures === null)
        {
            return;
        }


        $auxBasename = array();
        while ($objPictures->next())
        {

            $objFilesModel = FilesModel::findByUuid($objPictures->uuid);
            $basename = 'undefined';
            if ($objFilesModel !== null)
            {
                $basename = $objFilesModel->name;
            }
            $auxBasename[] = $basename;
            $this->addPicture($objPictures, $objAlbum->getRelated('pid')->id);
        }


        // sort by basename
        if ($this->gc_picture_sorting == 'name')
        {
            if ($this->gc_picture_sorting_direction == 'ASC')
            {
                array_multisort($this->arrPictures, SORT_STRING, $auxBasename, SORT_ASC);
            }
            else
            {
                array_multisort($this->arrPictures, SORT_STRING, $auxBasename, SORT_DESC);
            }
        }


        // store $arrPictures in the template variable
        $this->Template->arrPictures = $this->arrPictures;

        // generate other template variables
        $this->getAlbumTemplateVars($objAlbum);

        // init the counter
        $this->initCounter($objAlbum);


        // HOOK: modify the page or template object
        if (isset($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']) && is_array($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']))
        {
            foreach ($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate'] as $callback)
            {
                $this->import($callback[0]);
                $this->Template = $this->$callback[0]->$callback[1]($this, $objAlbum);
            }
        }
    }
}
