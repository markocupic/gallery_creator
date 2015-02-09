<?php

/**
 * Contao Open Source CMS
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
 * Class GcHelpers
 * Provide methods for using the gallery_creator extension
 *
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class GcHelpers extends \System
{

       /**
        * insert a new entry in tl_gallery_creator_pictures
        *
        * @param integer
        * @param string
        * $intAlbumId - albumId
        * $strFilepath - filepath -> files/gallery_creator_albums/albumalias/filename.jpg
        * @return bool
        */
       public static function createNewImage($intAlbumId, $strFilepath)
       {

              //get the file-object
              $objFile = new \File($strFilepath);
              $objFile->close();
              if (!$objFile->isGdImage)
              {
                     return false;
              }

              //get the album-object
              $objAlbum = \GalleryCreatorAlbumsModel::findById($intAlbumId);

              // get the assigned album directory
              $objFolder = \FilesModel::findByUuid($objAlbum->assignedDir);
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

              //check if the file ist stored in the album-directory or if it is stored in an external directory
              $blnExternalFile = strstr($objFile->dirname, $assignedDir) ? false : true;

              //get the album object and the alias
              $strAlbumAlias = $objAlbum->alias;
              //db insert
              $objImg = new \GalleryCreatorPicturesModel();
              $objImg->tstamp = time();
              $objImg->pid = $objAlbum->id;
              $objImg->externalFile = $blnExternalFile ? "1" : "";
              $objImg->save();

              if ($objImg->id)
              {
                     $insertId = $objImg->id;
                     //get the next sorting index
                     $objImg_2 = \Database::getInstance()
                            ->prepare('SELECT MAX(sorting)+10 AS maximum FROM tl_gallery_creator_pictures WHERE pid=?')
                            ->execute($objAlbum->id);
                     $sorting = $objImg_2->maximum;

                     //if filename should be generated
                     if (!$objAlbum->preserve_filename)
                     {
                            $newFilepath = sprintf('%s/alb%s_img%s.%s', $assignedDir, $objAlbum->id, $insertId, $objFile->extension);
                            $objFile->renameTo($newFilepath);
                     }

                     //galleryCreatorImagePostInsert - HOOK
                     //uebergibt die id des neu erstellten db-Eintrages ($lastInsertId)
                     if (isset($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']) && is_array($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']))
                     {
                            foreach ($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert'] as $callback)
                            {
                                   $objClass = self::importStatic($callback[0]);
                                   $objClass->$callback[1]($insertId);
                            }
                     }


                     if (is_file(TL_ROOT . '/' . $objFile->path))
                     {
                            //get the userId
                            $userId = '0';
                            if (TL_MODE == 'BE')
                            {
                                   $userId = \BackendUser::getInstance()->id;
                            }
                            // the album-owner is automaticaly the image owner, if the image was uploaded by a by a frontend user
                            if (TL_MODE == 'FE')
                            {
                                   $userId = $objAlbum->owner;
                            }


                            //finally save the new image in tl_gallery_creator_pictures
                            $objPicture = \GalleryCreatorPicturesModel::findByPk($insertId);
                            $objPicture->uuid = $objFile->getModel()->uuid;
                            $objPicture->owner = $userId;
                            $objPicture->date = $objAlbum->date;
                            $objPicture->sorting = $sorting;
                            $objPicture->save();

                            \System::log('A new version of tl_gallery_creator_pictures ID ' . $insertId . ' has been created', __METHOD__, TL_GENERAL);
                            //check for a valid preview-thumb for the album
                            $objAlbum = \GalleryCreatorAlbumsModel::findByAlias($strAlbumAlias);
                            if ($objAlbum !== null)
                            {
                                   if ($objAlbum->thumb == "")
                                   {
                                          $objAlbum->thumb = $insertId;
                                          $objAlbum->save();
                                   }
                            }
                            return true;
                     }
                     else
                     {
                            if ($blnExternalFile === 1)
                            {
                                   $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file'], $strFilepath);
                            }
                            else
                            {
                                   $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['uploadError'], $strFilepath);
                            }
                            \System::log('Unable to create the new image in: ' . $strFilepath . '!', __METHOD__, TL_ERROR);
                     }
              }
              return false;
       }

       /**
        * move uploaded file to the album directory
        * @param $intAlbumId
        * @param string $strName
        * @return array
        */
       public static function fileupload($intAlbumId, $strName = 'file')
       {

              $blnIsError = false;

              // Get the album object
              $objAlb = \GalleryCreatorAlbumsModel::findById($intAlbumId);
              if ($objAlb === null)
              {
                     $blnIsError = true;
                     \Message::addError('Album with ID ' . $intAlbumId . ' does not exist.');
              }

              // Check for a valid upload directory
              $objUploadDir = \FilesModel::findByUuid($objAlb->assignedDir);
              if ($objUploadDir === null || !is_dir(TL_ROOT . '/' . $objUploadDir->path))
              {
                     $blnIsError = true;
                     \Message::addError('No upload directory defined in the album settings!');
              }

              // Check if there are some files in $_FILES
              if (!is_array($_FILES[$strName]))
              {
                     $blnIsError = true;
                     \Message::addError('No Files selected for the uploader.');
              }

              if ($blnIsError)
              {
                     return array();
              }

              // Adapt $_FILES if files are loaded up by jumploader (java applet)
              if (!is_array($_FILES[$strName]['name']))
              {
                     $arrFile = array
                     (
                            'name'     => $_FILES[$strName]['name'],
                            'type'     => $_FILES[$strName]['type'],
                            'tmp_name' => $_FILES[$strName]['tmp_name'],
                            'error'    => $_FILES[$strName]['error'],
                            'size'     => $_FILES[$strName]['size']
                     );

                     unset($_FILES);

                     //rebuild $_FILES for the Contao FileUpload class
                     $_FILES[$strName]['name'][0] = $arrFile['name'];
                     $_FILES[$strName]['type'][0] = $arrFile['type'];
                     $_FILES[$strName]['tmp_name'][0] = $arrFile['tmp_name'];
                     $_FILES[$strName]['error'][0] = $arrFile['error'];
                     $_FILES[$strName]['size'][0] = $arrFile['size'];
              }

              // Do not overwrite files of the same filename
              $intCount = count($_FILES[$strName]['name']);
              for ($i = 0; $i < $intCount; $i++)
              {
                     if (strlen($_FILES[$strName]['name'][$i]))
                     {
                            // Generate unique filename
                            $_FILES[$strName]['name'][$i] = basename(\GcHelpers::generateUniqueFilename($objUploadDir->path . '/' . $_FILES[$strName]['name'][$i]));
                     }
              }

              // Resize image if feature is enabled
              if (\Input::post('img_resolution') > 1)
              {
                     \Config::set('imageWidth', \Input::post('img_resolution'));
                     \Config::set('jpgQuality', \Input::post('img_quality'));
              }
              else
              {
                     \Config::set('maxImageWidth', 999999999);
              }

              // Call the Contao FileUpload class
              $objUpload = new \FileUpload();
              $objUpload->setName($strName);
              $arrUpload = $objUpload->uploadTo($objUploadDir->path);

              foreach ($arrUpload as $strFileSrc)
              {
                     // store file in tl_files
                     $objFile = new \File($strFileSrc);
                     $objFile->close();
              }

              return $arrUpload;
       }

       /**
        * generate a unique filepath for a new picture
        *
        * @param string
        * @return string
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

              //Falls Datei schon existiert, wird hinten eine Zahl mit fuehrenden Nullen angehaengt -> filename0001.jpg
              $i = 0;
              $isUnique = false;
              do
              {
                     $i++;
                     if (!file_exists(TL_ROOT . '/' . $dirname . '/' . $basename . '.' . $extension))
                     {
                            //exit loop when filename is unique
                            $isUnique = true;
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
                            //Integer mit fuehrenden Nullen an den Dateinamen anhaengen ->filename0001.jpg
                            $basename = $filename . '_' . $suffix;

                            //Break after 100 loops
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
        *
        * @param integer
        * @return string
        */
       public static function generateUploader($intAlbumId, $uploader = 'be_gc_jumploader')
       {

              //create the template object
              $objTemplate = new \BackendTemplate($uploader);
              $objUser = \BackendUser::getInstance();

              //upload url
              $objTemplate->uploadUrl = ampersand(sprintf('%scontao/main.php?do=gallery_creator&act=edit&table=tl_gallery_creator_albums&id=%s&mode=fileupload&rt=%s', \Environment::get('base'), $intAlbumId, REQUEST_TOKEN));

              //security tokens
              $objTemplate->securityTokens = sprintf('PHPSESSID=%s; path=/; %s_USER_AUTH=%s; path=/;', session_id(), TL_MODE,
                                                     $_COOKIE[TL_MODE . '_USER_AUTH']);

              //request token
              $objTemplate->requestToken = REQUEST_TOKEN;

              // user auth token
              if (TL_MODE == 'BE')
              {
                     $objTemplate->beUserAuth = $_COOKIE['BE_USER_AUTH'];
              }
              else
              {
                     $objTemplate->feUserAuth = $_COOKIE['FE_USER_AUTH'];
              }

              //get the domain
              $search = array('http%3A', 'https%3A');
              $replace = array('http:', 'https:');
              $url = \System::urlEncode(\Environment::get('base'));
              $domain = str_replace($search, $replace, $url);

              // maxFileSize
              $objTemplate->maxFileSize = $GLOBALS['TL_CONFIG']['maxFileSize'];

              // $_FILES['file']
              $objTemplate->strName = 'file';

              //languageFiles
              $language = strlen($objUser->language) ? $objUser->language : 'en';
              $objTemplate->jumploaderLanguageFiles = $domain . 'system/modules/gallery_creator/assets/plugins/jumploader/lang/messages_' . $language . '.zip';

              //jumploader Archive
              $pathToArchive = $domain . 'system/modules/gallery_creator/assets/plugins/jumploader';
              $arrJumploaderArchive = array(
                     sprintf('%s/mediautil_z.jar', $pathToArchive),
                     sprintf('%s/sanselan_z.jar', $pathToArchive),
                     sprintf('%s/jumploader_z.jar', $pathToArchive),
                     sprintf('%s/xfiledialog.jar', $pathToArchive),
              );
              $objTemplate->jumploaderArchive = implode(',', $arrJumploaderArchive);

              //resize images in browser before loading them up
              $objTemplate->imageRes = $objUser->gc_img_resolution . 'x' . $objUser->gc_img_resolution;
              $objTemplate->imageQuality = $objUser->gc_img_quality * 10;

              //optional jumploader adds a watermark to each uploaded image
              if (strlen($GLOBALS['TL_CONFIG']['gc_watermark_path']))
              {
                     $fileUuid = base64_decode($GLOBALS['TL_CONFIG']['gc_watermark_path']);
                     $objFileModel = \FilesModel::findByUuid($fileUuid);
                     if ($objFileModel !== null)
                     {
                            $objTemplate->watermarkHalign = $GLOBALS['TL_CONFIG']['gc_watermark_halign'];
                            $objTemplate->watermarkValign = $GLOBALS['TL_CONFIG']['gc_watermark_valign'];
                            $objTemplate->watermarkOpacity = $GLOBALS['TL_CONFIG']['gc_watermark_opacity'];
                            $objTemplate->watermarkSource = \Environment::get('base') . $objFileModel->path;
                     }
              }

              // check if images should be scaled during the upload process
              if ($objUser->gc_img_resolution != 'no_scaling')
              {
                     $objTemplate->scaleImages = true;
              }

              // parse the jumloader view and return it
              return $objTemplate->parse();
       }

       /**
        * Returns the information-array about an album
        *
        * @param $intAlbumId
        * @param $objThis
        * @return array
        */
       public static function getAlbumInformationArray($intAlbumId, $objThis)
       {

              global $objPage;

              if ($objThis->moduleType != 'fmd' && $objThis->moduleType != 'cte')
              {
                     $strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
                     __error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
              }

              $objAlbum = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute($intAlbumId);
              //Anzahl Subalben ermitteln
              $objSubAlbums = \Database::getInstance()
                     ->prepare('SELECT thumb, count(id) AS countSubalbums FROM tl_gallery_creator_albums WHERE published=? AND pid=? GROUP BY ?')
                     ->execute('1', $intAlbumId, 'id');

              $objPics = \Database::getInstance()
                     ->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=? AND published=?')
                     ->execute($objAlbum->id, '1');

              //Array Thumbnailbreite
              $arrSize = unserialize($objThis->gc_size_albumlisting);


              $href = null;
              if (TL_MODE == 'FE')
              {
                     //generate the url as a formated string
                     $href = \Controller::generateFrontendUrl($objPage->row(),
                            ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/%s##ceId##' : '/items/%s##ceId##'), $objPage->language);
                     //add the content-element-id if necessary
                     $href = $objThis->moduleType == 'cte' && $objThis->countGcContentElementsOnPage() > 1 ? str_replace('##ceId##', '/ce/' . $objThis->id, $href) : str_replace('##ceId##', '', $href);
              }

              $arrPreviewThumb = $objThis->getAlbumPreviewThumb($objAlbum->id);

              $arrAlbum = array(
                     'id'                  => $objAlbum->id,
                     //[int] pid parent Album-Id
                     'pid'                 => $objAlbum->pid,
                     //[int] Sortierindex
                     'sorting'             => $objAlbum->sorting,
                     //[boolean] veroeffentlicht (true/false)
                     'published'           => $objAlbum->published,
                     //[int] id des Albumbesitzers
                     'owner'               => $objAlbum->owner,
                     //[string] Benutzername des Albumbesitzers
                     'owners_name'         => $objAlbum->owners_name,
                     //[int] Zeitstempel der letzten Aenderung
                     'tstamp'              => $objAlbum->tstamp,
                     //[int] Event-Unix-timestamp (unformatiert)
                     'event_tstamp'        => $objAlbum->date,
                     'date'                => $objAlbum->date,
                     //[string] Event-Datum (formatiert)
                     'event_date'          => \Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], $objAlbum->date),
                     //[string] Event-Location
                     'event_location'      => specialchars($objAlbum->event_location),
                     //[string] Albumname
                     'name'                => specialchars($objAlbum->name),
                     //[string] Albumalias (=Verzeichnisname)
                     'alias'               => $objAlbum->alias,
                     //[string] Albumkommentar
                     'comment'             => $objPage->outputFormat == 'xhtml' ? \String::toXhtml($objAlbum->comment) : \String::toHtml5($objAlbum->comment),
                     'caption'             => $objPage->outputFormat == 'xhtml' ? \String::toXhtml($objAlbum->comment) : \String::toHtml5($objAlbum->comment),
                     //[int] Albumbesucher (Anzahl Klicks)
                     'visitors'            => $objAlbum->visitors,
                     //[string] Link zur Detailansicht
                     'href'                => TL_MODE == 'FE' ? sprintf($href, $objAlbum->alias) : null,
                     //[string] Inhalt fuer das title Attribut
                     'title'               => $objAlbum->name . ' [' . ($objPics->numRows ? $objPics->numRows . ' ' . $GLOBALS['TL_LANG']['gallery_creator']['pictures'] : '') . ($objThis->gc_hierarchicalOutput && $objSubAlbums->countSubalbums > 0 ? ' ' . $GLOBALS['TL_LANG']['gallery_creator']['contains'] . ' ' . $objSubAlbums->countSubalbums . '  ' . $GLOBALS['TL_LANG']['gallery_creator']['subalbums'] . ']' : ']'),
                     //[int] Anzahl Bilder im Album
                     'count'               => $objPics->numRows,
                     //[int] Anzahl Unteralben
                     'count_subalbums'     => count(self::getChildAlbums($objAlbum->id)),
                     //[string] alt Attribut fuer das Vorschaubild
                     'alt'                 => $arrPreviewThumb['name'],
                     //[string] Pfad zum Originalbild
                     'src'                 => TL_FILES_URL . $arrPreviewThumb['path'],
                     //[string] Pfad zum Thumbnail
                     'thumb_src'           => TL_FILES_URL . \Image::get($arrPreviewThumb['path'], $arrSize[0], $arrSize[1], $arrSize[2]),
                     //[int] article id
                     'insert_article_pre'  => $objAlbum->insert_article_pre ? $objAlbum->insert_article_pre : null,
                     //[int] article id
                     'insert_article_post' => $objAlbum->insert_article_post ? $objAlbum->insert_article_post : null,
                     //[string] css-Classname
                     'class'               => 'thumb',
                     //[int] Thumbnailgrösse
                     'size'                => $arrSize,
                     //[string] javascript-Aufruf
                     'thumbMouseover'      => $objThis->gc_activateThumbSlider ? "objGalleryCreator.initThumbSlide(this," . $objAlbum->id . "," . $objPics->numRows . ");" : ""
              );

              //Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_albums erweitert wurde
              $objAlbum = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute($intAlbumId);
              foreach ($objAlbum->fetchAssoc() as $key => $value)
              {
                     if (!array_key_exists($key, $arrAlbum))
                     {
                            $arrAlbum[$key] = $value;
                     }
              }
              return $arrAlbum;
       }

       /**
        * Returns the information-array about an album
        *
        * @param null $intPictureId
        * @param $objThis
        * @return array|null
        */
       public static function getPictureInformationArray($intPictureId = null, $objThis)
       {

              if ($intPictureId < 1)
              {
                     return;
              }
              global $objPage;

              if ($objThis->moduleType != 'fmd' && $objThis->moduleType != 'cte')
              {
                     $strMessage = "<pre>Parameter 'ContentType' must be 'fmd' or 'cte'! <br /></pre>";
                     __error(E_USER_ERROR, $strMessage, __FILE__, __LINE__);
              }
              if ($objThis->Template)
              {
                     $objThis->Template->elementType = strtolower($objThis->moduleType);
                     $objThis->Template->elementId = $objThis->id;
              }

              $objPicture = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')
                     ->execute($intPictureId);

              //Alle Informationen zum Album in ein array packen
              $objAlbum = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($objPicture->pid);
              $arrAlbumInfo = $objAlbum->fetchAssoc();

              //Bild-Besitzer
              $objOwner = \Database::getInstance()->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objPicture->owner);
              $strImageSrc = '';
              $arrMeta = array();
              $objFileModel = \FilesModel::findByUuid($objPicture->uuid);
              if ($objFileModel == null)
              {
                     $strImageSrc = $objThis->defaultThumb;
              }
              else
              {
                     $strImageSrc = $objFileModel->path;
                     if (!is_file(TL_ROOT . '/' . $strImageSrc))
                     {
                            $strImageSrc = $objThis->defaultThumb;
                     }

                     //meta
                     $arrMeta = $objThis->getMetaData($objFileModel->meta, $objPage->language);
                     // Use the file name as title if none is given
                     if ($arrMeta['title'] == '')
                     {
                            $arrMeta['title'] = specialchars(str_replace('_', ' ', preg_replace('/^[0-9]+_/', '', $objFileModel->name)));
                     }
              }


              // get thumb dimensions
              $arrSize = unserialize($objThis->gc_size_detailview);

              //Thumbnails generieren
              $thumbSrc = \Image::get($strImageSrc, $arrSize[0], $arrSize[1], $arrSize[2]);
              $objFileThumb = new \File(rawurldecode($thumbSrc));
              $arrSize[0] = $objFileThumb->width;
              $arrSize[1] = $objFileThumb->height;
              $arrFile["thumb_width"] = $objFileThumb->width;
              $arrFile["thumb_height"] = $objFileThumb->height;
              $objFileThumb->close();

              // get some image params
              if (is_file(TL_ROOT . '/' . $strImageSrc))
              {
                     $objFileImage = new \File($strImageSrc);
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
                     $objFileImage->close();
              }
              else
              {
                     return null;
              }


              //check if there is a custom thumbnail selected
              if ($objPicture->addCustomThumb && !empty($objPicture->customThumb))
              {
                     $customThumbModel = \FilesModel::findByUuid($objPicture->customThumb);
                     if ($customThumbModel !== null)
                     {
                            if (is_file(TL_ROOT . '/' . $customThumbModel->path))
                            {
                                   $objFileCustomThumb = new \File($customThumbModel->path, true);
                                   if ($objFileCustomThumb->isGdImage)
                                   {
                                          $arrSize = unserialize($objThis->gc_size_detailview);
                                          $thumbSrc = \Image::get($objFileCustomThumb->path, $arrSize[0], $arrSize[1], $arrSize[2]);
                                          $objFileCustomThumb = new \File(rawurldecode($thumbSrc));
                                          $arrSize[0] = $objFileCustomThumb->width;
                                          $arrSize[1] = $objFileCustomThumb->height;
                                          $arrFile["thumb_width"] = $objFileCustomThumb->width;
                                          $arrFile["thumb_height"] = $objFileCustomThumb->height;
                                          $objFileCustomThumb->close();
                                   }
                            }
                     }
              }

              //exif
              if ($GLOBALS['TL_CONFIG']['gc_read_exif'])
              {
                     try
                     {
                            $exif = is_callable('exif_read_data') && TL_MODE == 'FE' ? exif_read_data($strImageSrc) : array('info' => "The function 'exif_read_data()' is not available on this server.");
                     }
                     catch (Exception $e)
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
              if (\Validator::isUuid($objPicture->localMediaSRC))
              {
                     //get path of a local Media
                     $objMovieFile = \FilesModel::findById($objPicture->localMediaSRC);
                     $strMediaSrc = $objMovieFile !== null ? $objMovieFile->path : $strMediaSrc;
              }
              $href = null;
              if (TL_MODE == 'FE' && $objThis->gc_fullsize)
              {
                     $href = $strMediaSrc != "" ? $strMediaSrc : \System::urlEncode($strImageSrc);
              }

              //cssID
              $cssID = deserialize($objPicture->cssID, true);

              // build the array
              $arrPicture = array(
                     'id'               => $objPicture->id,
                     //[int] pid parent Album-Id
                     'pid'              => $objPicture->pid,
                     //[int] das Datum, welches fuer das Bild gesetzt werden soll (= in der Regel das Upload-Datum)
                     'date'             => $objPicture->date,
                     //[int] id des Albumbesitzers
                     'owner'            => $objPicture->owner,
                     //Name des Erstellers
                     'owners_name'      => $objOwner->name,
                     //[int] album_id oder pid
                     'album_id'         => $objPicture->pid,
                     //[string] name (basename/filename of the file)
                     'name'             => specialchars($arrFile["basename"]),
                     //[string] filename without extension
                     'filename'         => $arrFile["filename"],
                     //[string] Pfad zur Datei
                     'uuid'             => $objPicture->uuid,
                     // uuid of the image
                     'path'             => $arrFile["path"],
                     //[string] basename similar to name
                     'basename'         => $arrFile["basename"],
                     //[string] dirname
                     'dirname'          => $arrFile["dirname"],
                     //[string] file-extension
                     'extension'        => $arrFile["extension"],
                     //[string] alt-attribut
                     'alt'              => $objPicture->title != '' ? specialchars($objPicture->title) : specialchars($arrMeta['title']),
                     //[string] title-attribut
                     'title'            => $objPicture->title != '' ? specialchars($objPicture->title) : specialchars($arrMeta['title']),
                     //[string] Bildkommentar oder Bildbeschreibung
                     'comment'          => $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(\String::toXhtml($objPicture->comment)) : specialchars(\String::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']),
                     'caption'          => $objPicture->comment != '' ? ($objPage->outputFormat == 'xhtml' ? specialchars(\String::toXhtml($objPicture->comment)) : specialchars(\String::toHtml5($objPicture->comment))) : specialchars($arrMeta['caption']),
                     //[string] path to media (video, picture, sound...)
                     'href'             => TL_FILES_URL . $href,
                     // single image url
                     'single_image_url' => \Controller::generateFrontendUrl($objPage->row(), ($GLOBALS['TL_CONFIG']['useAutoItem'] ? '/' : '/items/') . \Input::get('items') . '/img/' . $arrFile["filename"], $objPage->language),
                     //[string] path to the image,
                     'image_src'        => $arrFile["path"],
                     //[string] path to the other selected media
                     'media_src'        => $strMediaSrc,
                     //[string] path to a media on a social-media-plattform
                     'socialMediaSRC'   => $objPicture->socialMediaSRC,
                     //[string] path to a media stored on the webserver
                     'localMediaSRC'    => $objPicture->localMediaSRC,
                     //[string] Pfad zu einem benutzerdefinierten Thumbnail
                     'addCustomThumb'   => $objPicture->addCustomThumb,
                     //[string] Thumbnailquelle
                     'thumb_src'        => isset($thumbSrc) ? TL_FILES_URL . $thumbSrc : '',
                     //[array] Thumbnail-Ausmasse Array $arrSize[Breite, Hoehe, Methode]
                     'size'             => $arrSize,
                     //[int] thumb-width in px
                     'thumb_width'      => $arrFile["thumb_width"],
                     //[int] thumb-height in px
                     'thumb_height'     => $arrFile["thumb_height"],
                     //[int] image-width in px
                     'image_width'      => $arrFile["image_width"],
                     //[int] image-height in px
                     'image_height'     => $arrFile["image_height"],
                     //[int] das rel oder data-lightbox Attribut fuer das Anzeigen der Bilder in der Lightbox
                     'lightbox'         => $objPage->outputFormat == 'xhtml' ? 'rel="lightbox[lb' . $objPicture->pid . ']"' : 'data-lightbox="lb' . $objPicture->pid . '"',
                     //[int] Zeitstempel der letzten Aenderung
                     'tstamp'           => $objPicture->tstamp,
                     //[int] Sortierindex
                     'sorting'          => $objPicture->sorting,
                     //[boolean] veroeffentlicht (true/false)
                     'published'        => $objPicture->published,
                     //[array] Array mit exif metatags
                     'exif'             => $exif,
                     //[array] Array mit allen Albuminformation (albumname, owners_name...)
                     'albuminfo'        => $arrAlbumInfo,
                     //[array] Array mit Bildinfos aus den meta-Angaben der Datei, gespeichert in tl_files.meta
                     'metaData'         => $arrMeta,
                     //[string] css-ID des Bildcontainers
                     'cssID'            => $cssID[0] != '' ? $cssID[0] : '',
                     //[string] css-Klasse des Bildcontainers
                     'cssClass'         => $cssID[1] != '' ? $cssID[1] : '',
                     //[bool] true, wenn es sich um ein Bild handelt, das nicht in files/gallery_creator_albums/albumname gespeichert ist
                     'externalFile'     => $objPicture->externalFile,
              );

              //Fuegt dem Array weitere Eintraege hinzu, falls tl_gallery_creator_pictures erweitert wurde
              $objPicture = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')
                     ->execute($intPictureId);
              foreach ($objPicture->fetchAssoc() as $key => $value)
              {
                     if (!array_key_exists($key, $arrPicture))
                     {
                            $arrPicture[$key] = $value;
                     }
              }

              return $arrPicture;
       }

       /**
        * Returns the information-array about all subalbums ofd a certain parent album
        *
        * @param $intAlbumId
        * @param $objThis
        * @return array
        */
       public static function getSubalbumsInformationArray($intAlbumId, $objThis)
       {

              $objSubAlbums = \Database::getInstance()
                     ->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid=? AND published=? ORDER BY sorting ASC')
                     ->execute($intAlbumId, '1');
              $arrSubalbums = array();
              while ($objSubAlbums->next())
              {
                     $arrSubalbum = self::getAlbumInformationArray($objSubAlbums->id, $objThis);
                     array_push($arrSubalbums, $arrSubalbum);
              }
              return $arrSubalbums;
       }

       /**
        * @param $parentId
        * @param string $strSorting
        * @param null $iterationDepth
        * @return array
        */
       public static function getChildAlbums($parentId, $strSorting = '', $iterationDepth = null)
       {

              // get the iteration depth
              $iterationDepth = $iterationDepth === '' ? null : $iterationDepth;

              $arrSubAlbums = array();
              if ($strSorting == '')
              {
                     $strSql = 'SELECT id FROM tl_gallery_creator_albums WHERE pid=? ORDER BY sorting';
              }
              else
              {
                     $strSql = 'SELECT id FROM tl_gallery_creator_albums WHERE pid=? ORDER BY ' . $strSorting;
              }
              $objAlb = \Database::getInstance()->prepare($strSql)->execute($parentId);
              $depth = $iterationDepth !== null ? $iterationDepth - 1 : null;


              while ($objAlb->next())
              {
                     if ($depth < 0 && $iterationDepth !== null)
                     {
                            return $arrSubAlbums;
                     }
                     $arrSubAlbums[] = $objAlb->id;
                     $arrSubAlbums = array_merge($arrSubAlbums, self::getChildAlbums($objAlb->id, $strSorting, $depth));
              }
              return $arrSubAlbums;
       }

       /**
        * gibt ein Array mit allen Angaben des Parent-Albums zurueck
        *
        * @param integer
        * @return array
        */
       public static function getParentAlbum($AlbumId)
       {

              $objAlbPid = \Database::getInstance()->prepare('SELECT pid FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute($AlbumId);
              $parentAlb = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute($objAlbPid->pid);
              if ($parentAlb->numRows == 0)
              {
                     return null;
              }
              $arrParentAlbum = $parentAlb->fetchAllAssoc();
              return $arrParentAlbum[0];
       }

       /**
        * @param string
        * @param integer
        * @return bool
        * $imgPath - relative path to the filesource
        * angle - the rotation angle is interpreted as the number of degrees to rotate the image anticlockwise.
        * angle shall be 0,90,180,270
        */
       public static function imageRotate($imgPath, $angle)
       {

              if ($angle == 0)
              {
                     return false;
              }
              if ($angle % 90 !== 0)
              {
                     return false;
              }
              if ($angle < 90 || $angle > 360)
              {
                     return false;
              }
              if (!function_exists('imagerotate'))
              {
                     return false;
              }

              // chmod
              \Files::getInstance()->chmod($imgPath, 0777);

              // Load
              if (TL_MODE == 'BE')
              {
                     $imgSrc = '../' . $imgPath;
              }
              else
              {
                     $imgSrc = $imgPath;
              }
              $source = imagecreatefromjpeg($imgSrc);

              //rotate
              $imgTmp = imagerotate($source, $angle, 0);

              // Output
              imagejpeg($imgTmp, $imgSrc);
              imagedestroy($source);

              // chmod
              \Files::getInstance()->chmod($imgPath, 0644);
              return true;
       }

       /**
        * @param integer
        * @param string
        * Bilder aus Verzeichnis auf dem Server in Album einlesen
        */
       public static function importFromFilesystem($intAlbumId, $strMultiSRC)
       {

              $images = array();
              $objFilesModel = \FilesModel::findMultipleByUuids(explode(',', $strMultiSRC));
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
                            $objFile = new \File($objFilesModel->path);
                            if ($objFile->isGdImage)
                            {
                                   $images[$objFile->path] = array('name' => $objFile->basename, 'path' => $objFile->path);
                            }
                            $objFile->close();
                     }
                     else
                     {
                            // If it is a directory, then store its files in the array
                            $objSubfilesModel = \FilesModel::findByPid($objFilesModel->uuid);
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

                                   $objFile = new \File($objSubfilesModel->path);
                                   if ($objFile->isGdImage)
                                   {
                                          $images[$objFile->path] = array('name' => $objFile->basename, 'path' => $objFile->path);
                                   }
                                   $objFile->close();
                            }
                     }
              }
              if (count($images))
              {
                     $uploadPath = GALLERY_CREATOR_UPLOAD_PATH;
                     $objAlb = \GalleryCreatorAlbumsModel::findById($intAlbumId);
                     foreach ($images as $image)
                     {
                            if ($GLOBALS['TL_CONFIG']['gc_album_import_copy_files'])
                            {

                                   $strSource = $image['path'];
                                   $strDestination = $uploadPath . '/' . $objAlb->alias . '/' . basename($strSource);
                                   $strDestination = self::generateUniqueFilename($strDestination);
                                   if (is_file(TL_ROOT . '/' . $strSource))
                                   {
                                          //copy Image to the upload folder
                                          $objFile = new \File($strSource);
                                          $objFile->copyTo($strDestination);
                                          $objFile->close();
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
        * reviseTable
        *
        * @param bool
        */
       public static function reviseTable($blnCleanDb = false, $albumId = null)
       {
              // Requires a lot of Memory
              if (\Input::get('mode') == 'clean_db')
              {
                     //Upload-Verzeichnis erstellen, falls nicht mehr vorhanden
                     new \Folder(GALLERY_CREATOR_UPLOAD_PATH);

                     //Sorgt dafuer, dass der zur id gehoerende Name immer aktuell ist
                     $db = \Database::getInstance()->execute('SELECT id, owner, alias FROM tl_gallery_creator_albums');
                     while ($db->next())
                     {
                            // Albumbesitzer ueberpruefen
                            $db_2 = \Database::getInstance()->prepare('SELECT name FROM tl_user WHERE id=?')->execute($db->owner);
                            $owner = $db_2->name;
                            if ($db_2->name == '')
                            {
                                   $owner = "no-name";
                            }
                            $objUpdate = \GalleryCreatorAlbumsModel::findById($db->id);
                            if (is_object($objUpdate))
                            {
                                   $objUpdate->owners_name = $owner;
                                   $objUpdate->save();
                            }
                     }


                     // Auf gueltige pid ueberpruefen

                     $objAlb = \Database::getInstance()->prepare('SELECT id, pid FROM tl_gallery_creator_albums WHERE pid!=?')
                            ->execute('0');
                     while ($objAlb->next())
                     {
                            $objParentAlb = \Database::getInstance()->prepare('SELECT id FROM tl_gallery_creator_albums WHERE id=?')
                                   ->execute($objAlb->pid);
                            if ($objParentAlb->numRows < 1)
                            {
                                   $objUpd = \FilesModel::findByPk($objAlb->id);
                                   $objUpd->pid = null;
                                   $objUpd->save();
                            }
                     }


                     if (\Database::getInstance()->fieldExists('path', 'tl_gallery_creator_pictures'))
                     {

                            // Datensaetzen ohne gültige uuid über den Feldinhalt path versuchen zu "retten"
                            if ($albumId !== null)
                            {
                                   $objPictures = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=?')
                                          ->execute($albumId);
                            }
                            else
                            {
                                   $objPictures = \Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_pictures');
                            }
                            while ($objPictures->next())
                            {
                                   // Get parent album
                                   $objAlbum = \GalleryCreatorPicturesModel::findByPk($objPictures->id)->getRelated('pid');
                                   $objFile = \FilesModel::findByUuid($objPictures->uuid);
                                   if ($objFile === null)
                                   {
                                          if ($objPictures->path != '')
                                          {
                                                 if (is_file(TL_ROOT . '/' . $objPictures->path))
                                                 {
                                                        $objModel = \Dbafs::addResource($objPictures->path);
                                                        if (\Validator::isUuid($objModel->uuid))
                                                        {
                                                               $objUpd = \GalleryCreatorPicturesModel::findByPk($objPictures->id);
                                                               $objUpd->uuid = $objModel->uuid;
                                                               $objUpd->save();
                                                               continue;
                                                        }
                                                 }
                                          }
                                          if ($blnCleanDb !== false)
                                          {
                                                 $objDel = \GalleryCreatorPicturesModel::findByPk($objPictures->id);
                                                 $objDel->delete();
                                          }
                                          else
                                          {
                                                 //show the error-message
                                                 $objDel = \GalleryCreatorPicturesModel::findByPk($objPictures->id);
                                                 $path = $objPictures->path != '' ? $objPictures->path : 'unknown path';
                                                 $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file_1'], $objDel->id, $path, $objAlbum->alias);
                                          }
                                   }
                                   elseif (!is_file(TL_ROOT . '/' . $objFile->path))
                                   {
                                          // If file has an entry in Dbafs, but doesn't exist on the server anymore
                                          if ($blnCleanDb !== false)
                                          {
                                                 $objDel = \GalleryCreatorPicturesModel::findByPk($objPictures->id);
                                                 $objDel->delete();
                                          }
                                          else
                                          {
                                                 $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file_1'], $objPictures->id, $objFile->path, $objAlbum->alias);
                                          }
                                   }
                                   else
                                   {
                                          // Pfadangaben mit tl_files.path abgleichen (Redundanz)
                                          if ($objPictures->path != $objFile->path)
                                          {
                                                 $objUpd = \GalleryCreatorPicturesModel::findByPk($objPictures->id);
                                                 $objUpd->path = $objFile->path;
                                                 $objUpd->save();
                                          }
                                   }
                            }
                     }


                     /**
                      * Sorgt dafuer, dass in tl_content im Feld gc_publish_albums keine verwaisten AlbumId's vorhanden sind
                      * Prueft, ob die im Inhaltselement definiertern Alben auch noch existieren.
                      * Wenn nein, werden diese aus dem Array entfernt.
                      */
                     $objCont = \Database::getInstance()->prepare('SELECT id, gc_publish_albums FROM tl_content WHERE type=?')->execute('gallery_creator');
                     while ($objCont->next())
                     {
                            $newArr = array();
                            $arrAlbums = unserialize($objCont->gc_publish_albums);
                            if (is_array($arrAlbums))
                            {
                                   foreach ($arrAlbums as $AlbumID)
                                   {
                                          $objAlb = \Database::getInstance()->prepare('SELECT id FROM tl_gallery_creator_albums WHERE id=?')->limit('1')->execute($AlbumID);
                                          if ($objAlb->next())
                                          {
                                                 $newArr[] = $AlbumID;
                                          }
                                   }
                            }
                            \Database::getInstance()->prepare('UPDATE tl_content SET gc_publish_albums=? WHERE id=?')
                                   ->execute(serialize($newArr), $objCont->id);
                     }
              }
       }
}
