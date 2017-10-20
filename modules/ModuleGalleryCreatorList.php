<?php

/**
 * Gallery Creator for Contao Open Source CMS
 *
 * Copyright (C) 2008-2018 Marko Cupic
 *
 * @package    Galery Creator
 * @link       https://github.com/markocupic/gallery_creator/
 * @license    https://opensource.org/licenses/lgpl-3.0.html LGPL
 */

namespace Markocupic\GalleryCreator;


use Contao\Input;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Pagination;
use Contao\Environment;

/**
 * Front end module "gallery_creator_list".
 *
 * @author Marko Cupic <https://github.com/markocupic>
 */
class ModuleGalleryCreatorList extends ModuleGalleryCreator
{


    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'mod_gallery_creator_list';

    /**
     * @var
     */
    protected $arrSelectedAlbums;

    /**
     * Display a wildcard in the back end
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            /** @var BackendTemplate|object $objTemplate */
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### ' . utf8_strtoupper($GLOBALS['TL_LANG']['FMD']['gallery_creator_list'][0]) . ' ###';
            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Ajax Requests
        if (TL_MODE == 'FE' && Environment::get('isAjaxRequest'))
        {
            $this->generateAjax();
        }


        $this->gc_galleries = $this->sortOutProtected(deserialize($this->gc_galleries));

        // Return if there are no calendars
        if (!is_array($this->gc_galleries) || empty($this->gc_galleries))
        {
            return '';
        }

        // Show the event reader if an item has been selected
        if ($this->gc_readerModule > 0 && (isset($_GET['albums']) || (Config::get('useAutoItem') && isset($_GET['auto_item']))))
        {
            return $this->getFrontendModule($this->gc_readerModule, $this->strColumn);
        }
        return parent::generate();
    }


    /**
     * Generate the module
     */
    protected function compile()
    {
        global $objPage;

        $arrAllAlbums = $this->getAllAlbums($this->gc_galleries);


        // pagination settings
        $limit = $this->gc_albumsPerPage;
        $offset = 0;
        if ($limit > 0)
        {
            // Get the current page
            $id = 'page_g' . $this->id;
            $page = (Input::get($id) !== null) ? Input::get($id) : 1;
            $offset = ($page - 1) * $limit;

            // count albums
            $itemsTotal = count($arrAllAlbums);

            // create pagination menu
            $numberOfLinks = $this->gc_paginationNumberOfLinks < 1 ? 7 : $this->gc_paginationNumberOfLinks;
            $objPagination = new Pagination($itemsTotal, $limit, $numberOfLinks, $id);
            $this->Template->pagination = $objPagination->generate("\n ");
        }

        if ($limit == 0 || $limit > count($arrAllAlbums))
        {
            $limit = count($arrAllAlbums);
        }

        $arrAlbums = array();
        for ($i = $offset; $i < $offset + $limit; $i++)
        {
            if (isset($arrAllAlbums[$i]))
            {
                $arrAlbums[] = $arrAllAlbums[$i];
            }
        }

        // Add css classes
        if (count($arrAlbums) > 0)
        {
            $arrAlbums[0]['cssClass'] .= ' first';
            $arrAlbums[count($arrAlbums) - 1]['cssClass'] .= ' last';
        }

        $this->Template->imagemargin = $this->generateMargin(unserialize($this->gc_imagemargin));
        $this->Template->arrAlbums = $arrAlbums;

        // Call gc_generateFrontendTemplate
        // HOOK: modify the page or template object
        if (isset($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']) && is_array($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate']))
        {
            foreach ($GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate'] as $callback)
            {
                $this->import($callback[0]);
                $this->Template = $this->$callback[0]->$callback[1]($this, null);
            }
        }
    }
}
