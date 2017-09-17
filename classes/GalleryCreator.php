<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Markocupic\GalleryCreator;



use Contao\Config;
use Contao\GalleryCreatorGalleriesModel;
use Contao\GalleryCreatorAlbumsModel;
use Contao\Frontend;
use Contao\PageModel;
use Contao\Date;



/**
 * Class GalleryCreator
 * @package GalleryCreator
 */
class GalleryCreator extends Frontend
{




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
