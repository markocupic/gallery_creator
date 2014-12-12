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
 * Class ContentDisplayGallery
 *
 * Provide methods regarding gallery_creator albums.
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class ContentDisplayGallery extends DisplayGallery
{

       /**
        * Set the template
        * @return string
        */
       public function generate()
       {

              $this->moduleType = 'cte';

              // set the item from the auto_item parameter
              if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item']))
              {
                     \Input::setGet('items', \Input::get('auto_item'));
              }

              if (\Input::get('items'))
              {
                     // get the content element id from the $_GET - variable if multiple gallery_creator content elements are embeded on the current page
                     $this->ContentElementId = $this->countGcContentElementsOnPage() > 1 ? \Input::get('ce') : $this->id;

                     // only display the detail view of the selected album if multiple gallery_creator content elements are embeded on the current page
                     if ($this->id != $this->ContentElementId && $this->countGcContentElementsOnPage() > 1)
                     {
                            return '';
                     }
              }
              return parent::generate();
       }

       /**
        * Generate module
        */
       protected function compile()
       {

              global $objPage;

              // process request variables
              $this->evalRequestVars();

              if (!is_array(deserialize($this->gc_publish_albums)) && !$this->gc_publish_all_albums)
              {
                     return;
              }

              if ($this->gc_publish_all_albums)
              {
                     // if all albums should be shown
                     $arrSelectedAlb = $this->listAllAlbums();
              }
              else
              {
                     // if only selected albums should be shown
                     $arrSelectedAlb = deserialize($this->gc_publish_albums);
              }

              // clean array from unpublished or empty or protected albums
              foreach ($arrSelectedAlb as $key => $albumId)
              {
                     $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=? AND published=?')->execute($albumId, '1');
                     $objPics = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE pid = ? AND published=?')->execute($albumId, '1');

                     // if the album doesn't exist
                     if (!$objAlbum->numRows)
                     {
                            unset($arrSelectedAlb[$key]);
                            continue;
                     }

                     // if the album doesn't contain any pictures
                     if (!$objPics->numRows)
                     {
                            unset($arrSelectedAlb[$key]);
                            continue;
                     }

                     // remove id from $arrSelectedAlb if user is not allowed
                     if (TL_MODE == 'FE' && $objAlbum->protected == true)
                     {
                            $blnAllowed = null;
                            $this->import('FrontendUser', 'User');
                            // remove id from $arrSelectedAlb if user is not allowed
                            if (FE_USER_LOGGED_IN && is_array(unserialize($this->User->allGroups)))
                            {
                                   // check for accordance
                                   if (array_intersect(unserialize($this->User->allGroups), unserialize($objAlbum->groups)))
                                   {
                                          $blnAllowed = true;
                                   }
                            }
                            if (!$blnAllowed)
                            {
                                   unset($arrSelectedAlb[$key]);
                                   continue;
                            }
                     }
              }
              // build up the new array
              $arrAllowedAlbums = array_values($arrSelectedAlb);

              $switch = strlen(\Input::get('items')) ? 'detailview' : 'albumlisting';
              $switch = strlen(\Input::get('jw_imagerotator')) ? 'jw_imagerotator' : $switch;
              $switch = strlen(\Input::get('img')) ? 'single_image' : $switch;


              switch ($switch)
              {
                     case 'albumlisting' :

                            // abort if no album is selected
                            if (count($arrAllowedAlbums) < 1)
                            {
                                   return;
                            }

                            // pagination settings
                            $limit = $this->gc_AlbumsPerPage;
                            if ($limit > 0)
                            {
                                   $page = \Input::get('page') ? \Input::get('page') : 1;
                                   $offset = ($page - 1) * $limit;
                                   // count albums
                                   $itemsTotal = count($arrAllowedAlbums);
                                   // create pagination menu
                                   $numberOfLinks = $this->gc_PaginationNumberOfLinks<1 ? 7 : $this->gc_PaginationNumberOfLinks;
                                   $objPagination = new \Pagination($itemsTotal, $limit, $numberOfLinks);
                                   $this->Template->pagination = $objPagination->generate("\n ");
                            }

                            if ($limit == '0')
                            {
                                   $limit = count($arrAllowedAlbums);
                                   $offset = 0;
                            }

                            $arrAlbums = array();
                            for ($i = $offset; $i < $offset + $limit; $i++)
                            {
                                   if (!$arrAllowedAlbums[$i])
                                   {
                                          continue;
                                   }

                                   $currAlbumId = $arrAllowedAlbums[$i];
                                   $objAlbum = $this->Database->prepare('SELECT id, alias FROM tl_gallery_creator_albums WHERE id=?')->execute($currAlbumId);
                                   if (false === $this->feUserAuthentication($objAlbum->alias))
                                   {
                                          continue;
                                   }
                                   $arrAlbums[$objAlbum->id] = \GcHelpers::getAlbumInformationArray($objAlbum->id, $this);
                            }
                            $this->Template->imagemargin = $this->generateMargin(unserialize($this->gc_imagemargin_albumlisting));
                            $this->Template->arrAlbums = $arrAlbums;
                            $this->getAlbumTemplateVars($objAlbum->id, 'cte');
                            break;
 
                     case 'detailview':

                            $objAlbum = \GalleryCreatorAlbumsModel::findByAlias($this->strAlbumalias);
                            $published = $objAlbum->published ? true : false;

                            // for security reasons...
                            if (!$published || (!$this->gc_publish_all_albums && !in_array($this->intAlbumId, $arrAllowedAlbums)))
                            {
                                   die("Gallery with alias " . $this->strAlbumalias . " is either not published or not available or you haven't got enough permission to watch it!!!");
                            }

                            // pagination settings
                            $limit = $this->gc_ThumbsPerPage;
                            if ($limit > 0)
                            {
                                   $page = \Input::get('page') ? \Input::get('page') : 1;
                                   $offset = ($page - 1) * $limit;

                                   // count albums
                                   $objTotal = $this->Database->prepare('SELECT COUNT(id) as itemsTotal FROM tl_gallery_creator_pictures WHERE published=? AND pid=? GROUP BY ?')->execute('1', $this->intAlbumId, 'id');
                                   $itemsTotal = $objTotal->itemsTotal;

                                   // create the pagination menu
                                   $numberOfLinks = $this->gc_PaginationNumberOfLinks<1 ? 7 : $this->gc_PaginationNumberOfLinks;
                                   $objPagination = new \Pagination($itemsTotal, $limit, $numberOfLinks);
                                   $this->Template->pagination = $objPagination->generate("\n ");
                            }

                            // picture sorting
                            $str_sorting = $this->gc_picture_sorting == '' || $this->gc_picture_sorting_direction == '' ? 'sorting ASC' : $this->gc_picture_sorting . ' ' . $this->gc_picture_sorting_direction;
                            $objPictures = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE published=? AND pid=? ORDER BY ' . $str_sorting);
                            if ($limit > 0)
                            {
                                   $objPictures->limit($limit, $offset);
                            }
                            $objPictures = $objPictures->execute('1', $this->intAlbumId);

                            // build up $arrPictures
                            $arrPictures = array();
                            while ($objPictures->next())
                            {
                                   $arrPictures[$objPictures->id] = \GcHelpers::getPictureInformationArray($objPictures->id, $this);
                            }

                            // store $arrPictures in the template variable
                            $this->Template->arrPictures = $arrPictures;

                            // generate other template variables
                            $this->getAlbumTemplateVars($this->intAlbumId, 'cte');

                            // init the counter
                            $this->initCounter($this->intAlbumId);
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
                            if (!$published || (!$this->gc_publish_all_albums && !in_array($this->intAlbumId, $arrAllowedAlbums)))
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
                                   $arrPictures['prev'] = \GcHelpers::getPictureInformationArray($arrIDS[$currentIndex - 1], $this);
                                   $arrPictures['current'] = \GcHelpers::getPictureInformationArray($arrIDS[$currentIndex], $this);
                                   $arrPictures['next'] = \GcHelpers::getPictureInformationArray($arrIDS[$currentIndex + 1], $this);

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
                            $this->Template->returnHref = $this->generateFrontendUrl($objPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . \Input::get('items'), $objPage->language);
                            $this->Template->arrPictures = $arrPictures;

                            // generate other template variables
                            $this->getAlbumTemplateVars($this->intAlbumId, 'cte');

                            // init the counter
                            $this->initCounter($this->intAlbumId);
                            break;


                     case 'jw_imagerotator' :
                            header("content-type:text/xml;charset=utf-8");
                            echo $this->getJwImagerotatorXml($this->strAlbumalias);
                            exit;
                            break;
              }
              // end switch
       }

       /**
        * return a sorted array with all albums selected in the content element settings
        * @return array
        */
       protected function listAllAlbums()
       {

              $objContent = $this->Database->prepare('SELECT gc_sorting, gc_sorting_direction FROM tl_content WHERE id=?')->execute($this->id);
              $strSorting = $objContent->gc_sorting == '' || $objContent->gc_sorting_direction == '' ? 'date DESC' : $objContent->gc_sorting . ' ' . $objContent->gc_sorting_direction;
              $objAlbums = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_albums WHERE published=? ORDER BY ' . $strSorting)->execute('1');
              $arrAlb = array();
              while ($objAlbums->next())
              {
                     $arrAlb[] = $objAlbums->id;
              }
              return $arrAlb;
       }

}

