<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2012 Leo Feyer
 * @package Gallery Creator
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */
/**
 * Run in a custom namespace, so the class can be replaced
 */
namespace GalleryCreator;
/**
 * Class ModuleDisplayGallery
 * Provide methods regarding gallery_creator albums.
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class ModuleDisplayGallery extends DisplayGallery
{
       /**
        * Parse the template
        * @return string
        */
       public function generate()
       {
              $this->moduleType = 'fmd';
              // set the item from the auto_item parameter
              if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
                     \Input::setGet('items', \Input::get('auto_item'));
              }
              return parent::generate();
       }

       /**
        * Generate module
        */
       protected function compile()
       {
              // use a private template
              if (TL_MODE == 'FE' && $this->gc_template != '') {
                     $this->Template->style = count($this->arrStyle) ? implode(' ', $this->arrStyle) : '';
                     $this->Template->cssID = strlen($this->cssID[0]) ? ' id="' . $this->cssID[0] . '"' : '';
                     $this->Template->class = trim('mod_' . $this->type . ' ' . $this->cssID[1]);
              }

              // redirect to the detailview if there is only 1 album
              if (!\Input::get('items') && $this->gc_redirectSingleAlb) {
                     $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE published=?')->execute('1');
                     if ($objAlbum->numRows === 1) {
                            \Input::setGet('items', $objAlbum->alias);
                     }
              }

              if (\Input::get('items')) {
                     $this->strAlbumalias = \Input::get('items');
                     // authenticate user if album is protected
                     $this->feUserAuthentication($this->strAlbumalias);

                     // get the album id from the album alias
                     $objAlbum = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE alias=?')->execute($this->strAlbumalias);
                     $this->intAlbumId = $objAlbum->id;
              }
              
              // moduleType is used for ajax applications
              $this->Template->moduleType = $this->moduleType;
              $switch = strlen(\Input::get('items')) ? 'detailview' : 'albumlisting';
              $switch = strlen(\Input::get('jw_imagerotator')) ? 'jw_imagerotator' : $switch;

              switch ($switch) {

                     case 'albumlisting' :

                            // get all published albums
                            $arrAllowedAlbums = array();
                            if ($this->gc_hierarchicalOutput) {
                                   $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE published=? AND pid=?')->execute('1', '0');
                            } else {
                                   $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE published=?')->execute('1');
                            }

                            while ($objAlbum->next()) {
                                   if (TL_MODE == 'FE' && $objAlbum->protected == true) {
                                          $this->import('FrontendUser', 'User');
                                          // check if the frontend user is allowed
                                          if (FE_USER_LOGGED_IN && is_array(unserialize($this->User->allGroups))) {
                                                 if (array_intersect(unserialize($this->User->allGroups), unserialize($objAlbum->groups))) {
                                                        // user is allowed
                                                        $arrAllowedAlbums[] = $objAlbum->id;
                                                 }
                                          }
                                          continue;
                                   }
                                   // album is not protected
                                   $arrAllowedAlbums[] = $objAlbum->id;
                            }

                            // pagination settings
                            $limit = $this->gc_AlbumsPerPage;
                            if ($limit > 0) {
                                   $page = \Input::get('page') ? \Input::get('page') : 1;
                                   $offset = ($page - 1) * $limit;
                                   $itemsTotal = count($arrAllowedAlbums);

                                   // add pagination menu
                                   $objPagination = new \Pagination($itemsTotal, $limit);
                                   $this->Template->pagination = $objPagination->generate("\n ");
                            }

                            // get all published albums
                            $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id IN(' . implode(",", $arrAllowedAlbums) . ') ORDER BY sorting ASC');
                            if ($limit > 0) {
                                   $objAlbum->limit($limit, $offset);
                            }
                            $objAlbum = $objAlbum->execute('1', '0');

                            // album array
                            $arrAlbums = array();
                            while ($objAlbum->next()) {
                                   $arrAlbums[$objAlbum->id] = $this->getAlbumInformationArray($objAlbum->id, $this->gc_size_albumlisting, 'fmd');
                            }
                            $this->Template->imagemargin = $this->generateMargin(unserialize($this->gc_imagemargin_albumlisting));
                            $this->Template->arrAlbums = $arrAlbums;
                            $this->getAlbumTemplateVars($objAlbum->id, 'fmd');
                            break;

                     case 'detailview' :

                            // generate the subalbum array
                            if ($this->gc_hierarchicalOutput) {
                                   $objSubAlbums = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid=? AND published=? ORDER BY sorting ASC')->execute($this->intAlbumId, '1');
                                   $arrSubalbums = array();
                                   while ($objSubAlbums->next()) {
                                          $arrSubalbum = $this->getAlbumInformationArray($objSubAlbums->id, $this->gc_size_albumlisting, 'fmd');
                                          array_push($arrSubalbums, $arrSubalbum);
                                   }
                                   $this->Template->subalbums = count($arrSubalbums) ? $arrSubalbums : NULL;
                            }

                            // pagination settings
                            $limit = $this->gc_ThumbsPerPage;
                            if ($limit > 0) {
                                   $page = \Input::get('page') ? \Input::get('page') : 1;
                                   $offset = ($page - 1) * $limit;

                                   // count albums
                                   $objTotal = $this->Database->prepare('SELECT COUNT(id) as itemsTotal FROM tl_gallery_creator_pictures WHERE published=? AND pid=?')->execute('1', $this->intAlbumId);
                                   $itemsTotal = $objTotal->itemsTotal;

                                   // add pagination menu
                                   $objPagination = new \Pagination($itemsTotal, $limit);
                                   $this->Template->pagination = $objPagination->generate("\n ");
                            }

                            $objPictures = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=?  AND pid=? ORDER BY sorting');
                            if ($limit > 0) {
                                   $objPictures->limit($limit, $offset);
                            }

                            $objPictures = $objPictures->execute('1', $this->intAlbumId);
                            $arrPictures = array();

                            while ($objPictures->next()) {
                                   // picture array
                                   $arrPictures[$objPictures->id] = $this->getPictureInformationArray($objPictures->id, $this->gc_size_detailview, 'fmd');
                            }

                            // add picture array to the template
                            $this->Template->arrPictures = $arrPictures;

                            // add some other useful template vars
                            $this->getAlbumTemplateVars($this->intAlbumId, 'fmd');

                            // init the counter
                            $this->initCounter($this->intAlbumId);
                            break;

                     case 'jw_imagerotator' :

                            header("content-type:text/xml;charset=utf-8");
                            echo $this->getJwImagerotatorXml($this->strAlbumalias);
                            exit;
                            break;
              }
              //end switch
       }
}
