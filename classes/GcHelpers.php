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
 * Class GcHelpers
 *
 * Provide methods for using the gallery_creator extension
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class GcHelpers extends \System
{

       /**
        * insert a new entry in tl_gallery_creator_pictures
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
              if (!$objFile->isGdImage)
                     return false;
              
              //get the album-object
              $objAlbum = \GalleryCreatorAlbumsModel::findById($intAlbumId);
              
              //check if the file ist stored in the album-directory or if it is stored in an external directory
              $blnExternalFile = strstr($objFile->dirname, $objAlbum->alias) ? false : true;
              
              //the upload path
              $strUploadPath = GALLERY_CREATOR_UPLOAD_PATH;
              
              
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
                     $objImg_2 = \Database::getInstance()->prepare('SELECT MAX(sorting)+10 AS maximum FROM tl_gallery_creator_pictures WHERE pid=?')->executeUncached($objAlbum->id);
                     $nextOrd = $objImg_2->maximum;
              
                     //if filename should be generated
                     if (!$objAlbum->preserve_filename) {
                            $oldFilepath = $strFilepath;
                            $newFilepath = sprintf('%s/%s/alb%s_img%s.%s', $strUploadPath, $objAlbum->alias, $objAlbum->id, $insertId, $objFile->extension);
                            \Files::getInstance()->rename($oldFilepath, $newFilepath);
                            $strFilepath = $newFilepath;
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
                     
                     if (is_file(TL_ROOT . '/' . $strFilepath))
                     {
                            $objFile = new \File($strFilepath);
                            if (!$objFile->isGdImage)
                                   return false;
                            
                            // update or insert entries in tl_files
                            self::registerInFilesystem($strFilepath);
                            self::registerInFilesystem(dirname($strFilepath));
                            
                            //get the userId
                            $userId = '0';
                            if (TL_MODE == 'BE') $userId = \BackendUser::getInstance()->id;
                            // the album-owner is automaticaly the image owner, if the image was uploaded by a by a frontend user
                            if (TL_MODE == 'FE') $userId = $objAlbum->owner;
                            
                            //get the fileId
                            $objFiles = \FilesModel::findByPath($strFilepath);
                            $fileId = $objFiles !== null ? $objFiles->id : '0';
                            
                            //finally save new image in tl_gallery_creator_pictures
                            $objPicture = \GalleryCreatorPicturesModel::findByPk($insertId);
                            $objPicture->name = $objFile->basename;
                            $objPicture->path = $strFilepath;
                            $objPicture->owner = $userId;
                            $objPicture->date = $objAlbum->date;
                            $objPicture->sorting = $nextOrd;
                            $objPicture->fileID = $fileId;
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
        * deletes entries in tl_files
        * @param sting|integer
        */
       public static function deleteFromFilesystem($value)
       {
              //$value can either be an id or a path
              if ($value == '')
                     return;
              //checks if $value is of type integer
              if (strval(intval($value)) == strval($value))
              {
                     $field = 'id';
              }
              else
              {
                     $field = 'path';
              }
              switch ($field) {
                     case 'id' :
                            \Database::getInstance()->prepare('DELETE FROM tl_files WHERE id=?')->execute($value);
                            break;
                     
                     case 'path' :
                            \Database::getInstance()->prepare('DELETE FROM tl_files WHERE path=?')->execute($value);
                            break;
              }
       }

       /**
        * move uploaded file to the album directory
        * @param string
        * @param string
        * @param string
        * @return array
        */
       public static function fileupload($intAlbumId, $strFilename)
       {
              $strUploadPath = GALLERY_CREATOR_UPLOAD_PATH;
              $objAlb = \GalleryCreatorAlbumsModel::findById($intAlbumId);
              $strAlbumAlias = $objAlb->alias;
              
              //unerlaubte Dateitypen abfangen
              $pathinfo = pathinfo($strFilename);
              $uploadTypes = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['uploadTypes']));
              if (!in_array(strtolower($pathinfo['extension']), $uploadTypes))
              {
                     //Fehlermeldung anzeigen
                     $errorMsg = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $pathinfo['extension']);
                     $_SESSION['TL_ERROR'][] = $errorMsg;
                     \System::log('File type "' . $pathinfo['extension'] . '" is not allowed to be uploaded (' . $strFilename . ')', __METHOD__, TL_ERROR);
                     //send the response to the jumploader applet
                     die(json_encode(array('status' => 'error', 'serverResponse' => $errorMsg)));
              }
              
              //zu grosse und zu kleine, defekte Dateien abfangen
              if ($GLOBALS['TL_CONFIG']['maxFileSize'] <= $_FILES['file']['size'] || $_FILES['file']['size'] < 1000)
              {
                     //Fehlermeldung anzeigen
                     $errorMsg = sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $GLOBALS['TL_CONFIG']['maxFileSize']);
                     $_SESSION['TL_ERROR'][] = $errorMsg;
                     \System::log('Maximum upload-filesize exceeded. Filename: ' . $strFilename . ' size: ' . $_FILES['file']['size'], __METHOD__, TL_ERROR);
                     //send the response to the jumploader applet
                     die(json_encode(array('status' => 'error', 'serverResponse' => $errorMsg)));
              }
              
              //dateinamen romanisieren und auf Einmaligkeit testen
              $strFilename = self::generateUniqueFilename($strFilename);
              
              
              //chmod-settings
              \Files::getInstance()->chmod($strUploadPath . '/' . $strAlbumAlias, 0777);
              
              //move_uploaded_file
              if (\Files::getInstance()->move_uploaded_file($_FILES['file']['tmp_name'], $strUploadPath . '/' . $strAlbumAlias . '/' . $strFilename))
              {
                     $strFileSrc = $strUploadPath . '/' . $strAlbumAlias . '/' . $strFilename;
                     //chmod
                     \Files::getInstance()->chmod($strFileSrc, 0644);
                     
                     //send the response to the jumploader applet
                     echo json_encode(array('status' => 'success', 'serverResponse' => $GLOBALS['TL_LANG']['ERR']['upploadSuccessful']));
                     //return the array if file was successfully uploaded
                     return array('strFileSrc' => $strFileSrc, 'strAlbumAlias' => $strAlbumAlias, 'strFilename' => $strFilename);
              }
              else
              {
                     //Upload-Fehler
                     \System::log('Unable to upload Files from tmpdir to the upload-dir.', __METHOD__, TL_ERROR);
                     $errorMsg = 'Error in ' . __METHOD__ . ' on line: ' . __LINE__ . '.<br>' . sprintf($GLOBALS['TL_LANG']['ERR']['uploadError'], $strFilename);
                     //send the response to the jumploader applet
                     die(json_encode(array('status' => 'error', 'serverResponse' => $errorMsg)));
              }
       }

       /**
        * generate a unique filename for a new picture
        * @param string
        * @return string
        */
       public static function generateUniqueFilename($strFilename)
       {
              $strFilename = utf8_romanize($strFilename);
              $strFilename = str_replace('"', '', $strFilename);
              $strFilename = str_replace(' ', '_', $strFilename);
              if (preg_match('/\.$/', $strFilename))
              {
                     throw new Exception($GLOBALS['TL_LANG']['ERR']['invalidName']);
              }
              
              //Falls Datei schon existiert, wird hinten eine Zahl mit fuehrenden Nullen angehaengt -> filename0001.jpg
              $i = 1;
              $isUnique = false;
              do {
                     $i++;
                     $objImg = \Database::getInstance()->prepare('SELECT count(id) AS items FROM tl_gallery_creator_pictures WHERE name=?')->execute($strFilename);
                     if ($objImg->items < 1)
                     {
                            //exit loop when filename is unique
                            $isUnique = true;
                     }
                     else
                     {
                            $info = pathinfo($strFilename);
                            //Dateinamen ohne Extension
                            $file_name = basename($strFilename, '.' . $info['extension']);
                            if ($i != 2)
                            {
                                   $file_name = substr($file_name, 0, -5);
                            }
                            $number = str_pad($i, 4, '0', STR_PAD_LEFT);
                            //Integer mit fuehrenden Nullen an den Dateinamen anhaengen ->filename0001.jpg
                            $strFilename = $file_name . '_' . $number . '.' . $info['extension'];
                            //Break after 1000 loops
                            if ($i == 1000)
                            {
                                   $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['fileExists'], $strFilename);
                                   return false;
                            }
                     }
              } while (!$isUnique);
              
              return $strFilename;
       }

       /**
        * generate the jumploader applet
        * @param integer
        * @return string
        */
       public static function generateUploader($intAlbumId)
       {
              //create the template object
              $objTemplate = new \BackendTemplate('be_gc_jumploader');
              $objUser = \BackendUser::getInstance();
              
              //upload url
              $objTemplate->uploadUrl = ampersand(sprintf('%scontao/main.php?do=gallery_creator&act=edit&table=tl_gallery_creator_albums&id=%s&mode=fileupload&rt=%s', \Environment::get('base'), $intAlbumId, REQUEST_TOKEN));
              
              //security tokens
              $objTemplate->securityTokens = sprintf('PHPSESSID=%s; path=/; %s_USER_AUTH=%s; path=/;', session_id(), TL_MODE, $_COOKIE[TL_MODE . '_USER_AUTH']);
              
              //request token
              $objTemplate->requestToken = REQUEST_TOKEN;
              
              //get the domain
              $domain = \Environment::get('base');

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
              $objTemplate->imageQuality = $objUser->gc_img_quality;
              
              //optional jumploader adds a watermark to each uploaded image
              if (strlen($GLOBALS['TL_CONFIG']['gc_watermark_path']))
              {
                     $objFile = FilesModel::findById($GLOBALS['TL_CONFIG']['gc_watermark_path']);
                     if (is_object($objFile) && is_file(TL_ROOT . '/' . $objFile->path))
                     {
                            $objFile = new \File($objFile->path);
                            if ($objFile->isGdImage)
                            {
                                   $objTemplate->watermarkHalign = $GLOBALS['TL_CONFIG']['gc_watermark_halign'];
                                   $objTemplate->watermarkValign = $GLOBALS['TL_CONFIG']['gc_watermark_valign'];
                                   $objTemplate->watermarkOpacity = $GLOBALS['TL_CONFIG']['gc_watermark_opacity'];
                                   $objTemplate->watermarkSource = \Environment::get('base') . $objFile->path;
                            }
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
        * gibt ein Array mit den Unteralben eines Albums zurück
        * @param integer
        * @return array
        */
       public static function getAllSubalbums($parentId)
       {
              $arrSubAlbums = array();
              $objAlb = \Database::getInstance()->prepare('SELECT id FROM tl_gallery_creator_albums WHERE pid=?')->execute($parentId);
              while ($objAlb->next())
              {
                     $arrSubAlbums[] = $objAlb->id;
                     $arrSubAlbums = array_merge($arrSubAlbums, self::getAllSubalbums($objAlb->id));
              }
              return $arrSubAlbums;
       }


       /**
        * gibt ein Array mit allen Angaben des Parent-Albums zurueck
        * @param integer
        * @return array
        */
       public static function getParentAlbum($AlbumId)
       {
              $objAlbPid = \Database::getInstance()->prepare('SELECT pid FROM tl_gallery_creator_albums WHERE id=?')->execute($AlbumId);
              $parentAlb = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($objAlbPid->pid);
              
              if ($parentAlb->numRows == 0)
                     return NULL;
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
              
              if ($angle == 0) return false;
              if ($angle % 90 !== 0) return false;
              if ($angle < 90 || $angle > 360) return false;
              if (!function_exists('imagerotate')) return false;
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
		imagejpeg($imgTmp,  $imgSrc);
		imagedestroy($source);
              
              // chmod
		\Files::getInstance()->chmod($imgPath, 0644);
              return true;
	}


       /**
        * @param integer
        * @param string
        * @param bool
        * Bilder aus Verzeichnis auf dem Server in Album einlesen
        */
       public static function importFromFilesystem($intAlbumId, $strMultiSRC)
       {
              $arrFileSrc = array();
              foreach (explode(',', $strMultiSRC) AS $src)
              {
                     $objFiles = \FilesModel::findById($src);
                     if (is_object($objFiles))
                     {
                            //if item is a file store it in the array
                            if (is_file(TL_ROOT . '/' . $objFiles->path))
                            {
                                   $objFile = new \File($objFiles->path);
                                   if ($objFile->isGdImage)
                                   {
                                          $arrFileSrc[md5($objFiles->path)] = array(
                                          'name' => $objFile->basename,
                                          'path' => $objFiles->path
                                          );
                                   }
                            
                            }
                            //if item is directory store its files in the array
                            if (is_dir(TL_ROOT . '/' . $objFiles->path))
                            {
                                   $arrFiles = scan(TL_ROOT . '/' . $objFiles->path);
                                   foreach ($arrFiles as $fileSrc)
                                   {
                                          if (is_file(TL_ROOT . '/' . $objFiles->path . '/' . $fileSrc))
                                          {
                                                 $objFile = new \File($objFiles->path . '/' . $fileSrc);
                                                 if ($objFile->isGdImage)
                                                 {
                                                        $arrFileSrc[md5($objFiles->path . '/' . $fileSrc)] = array(
                                                        //only the basename
                                                        'name' => $objFile->basename,
                                                        //path (incl. filename)
                                                        'path' => $objFile->value
                                                        );
                                                 }
                                          }
                                   }
                            }
                     }
              }

              if (count($arrFileSrc) < 1)
              {
                     return;
              }
              $uploadPath = GALLERY_CREATOR_UPLOAD_PATH;
              foreach ($arrFileSrc as $image)
              {
                     $objAlb = \GalleryCreatorAlbumsModel::findById($intAlbumId);
                     if (!$GLOBALS['TL_CONFIG']['gc_album_import_copy_files'])
                     {
                            GcHelpers::createNewImage($objAlb->id, $image['path']);
                     }
                     else
                     {
                            $strFilename = GcHelpers::generateUniqueFilename($image['name']);
                            $strSourceSrc = $image['path'];
                            $strDestSrc = $uploadPath . '/' . $objAlb->alias . '/' . $strFilename;
                            //copy Image to the upload folder
                            \Files::getInstance()->copy($strSourceSrc, $strDestSrc);
                            GcHelpers::createNewImage($objAlb->id, $strDestSrc);
                     }
              }
        
       }


       /**
        * Register a file or folder in tl_files
        * @param string
        * @param string
        * @return integer
        */
       public static function registerInFilesystem($strSrc = '', $strSrcUpdateTo = null)
       {
              /**
              * $strSrc: the path to the file/folder: 'files/mydir/myfile.txt' or 'files/mydir'
              * $strSrcUpdateTo: the new path to the file/folder if file was moved or renamed
              */
              
              if (strstr($strSrc, '.svn') || strstr($strSrc, '.DS_Store'))
                     return false;
              if (is_dir(TL_ROOT . '/' . $strSrc))
              {
                     $type = 'folder';
              } else {
                     $type = 'file';
              }       
              
              // for files
              if ($type == 'file')
              {
                     $hash = md5_file(TL_ROOT . '/' . $strSrc);
                     $objFile = new \File($strSrc);
                     $extension = $objFile->extension;
              }
              
              // for folders
              if ($type == 'folder')
              {
                     $objFolder = new \Folder($strSrc);
                     // creating hash of folders causes memory-limit-problems
                     $hash = $objFolder->isEmpty() ? $objFolder->hash : '';
                     $extension = '';
              }
              
              //get the pid
              $parentFolder = dirname($strSrc);
              $objTlFile = \Database::getInstance()->prepare('SELECT * FROM tl_files WHERE path=?')->executeUncached($parentFolder);
              $pid = $objTlFile->numRows ? $objTlFile->id : '0';
              if ($parentFolder == $GLOBALS['TL_CONFIG']['uploadPath'])
              {
                     $pid = 0;
              }
              
              //if entry allready exists, update only
              $objFile = \Database::getInstance()->prepare('SELECT * FROM tl_files WHERE path=?')->executeUncached($strSrc);
              //contao > 3.0.3
              //$objFile = \FilesModel::findByPath($strSrc, array('uncached'=>true));
              if ($objFile->numRows)
              {
                     // if the file/folder war renamed, change the path in tl_files
                     $set['path'] = strlen($strSrcUpdateTo) ? $strSrcUpdateTo : $objFile->path;
                     $set['type'] = $type;
                     $set['pid'] = $pid;
                     $set['name'] = basename($strSrc);
                     $set['extension'] = $extension;
                     $set['hash'] = $hash;
                     $set['tstamp'] = time();
                     $set['found'] = 1;
                     \Database::getInstance()->prepare('UPDATE tl_files %s WHERE id=?')
                                             ->set($set)
                                             ->execute($objFile->id);
                     $fileId = $objFile->id;
              } else {
                     // if file is not registered in tl_files register now
                     $objFiles = new \FilesModel();
                     $objFiles->hash         = $hash;
                     $objFiles->tstamp       = time();
                     $objFiles->path         = $strSrc;
                     $objFiles->name         = basename($strSrc);
                     $objFiles->type         = $type;
                     $objFiles->pid          = $pid;
                     $objFiles->extension    = $extension;
                     $objFiles->found        = 1;
                     $objFiles->save();
                     $fileId = $objFile->id;
              }
              return  $fileId ;
       }


       /**
	 * reviseTable
	 * @param bool
	 *
	 */
       public static function reviseTable($blnCleanDb = false)
       {
              //Upload-Verzeichnis erstellen, falls nicht mehr vorhanden
              new \Folder(GALLERY_CREATOR_UPLOAD_PATH);
              
              //Sorgt dafuer, dass der zur id gehoerende Name immer aktuell ist
              $db = \Database::getInstance()->execute('SELECT id, owner, alias FROM tl_gallery_creator_albums');
              while ($db->next())
              {
                     //Besitzt das Album ein Verzeichnis
                     new \Folder(GALLERY_CREATOR_UPLOAD_PATH . '/' . $db->alias);
                     
                     //chmod-settings
                     \Files::getInstance()->chmod(GALLERY_CREATOR_UPLOAD_PATH . '/' . $db->alias, 0777);
                     
                     //Albumbesitzer ueberpruefen
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

              //auf gueltige fileId ueberpruefen
              $objPic = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE fileID=?')->execute('0');
              while ($objPic->next())
              {
                     $objFiles = \FilesModel::findByPath($objPic->path);
                     if ($objFiles !== null)
                     {
                            $objUpdate = \Database::getInstance()->prepare('UPDATE tl_gallery_creator_pictures SET fileID=? WHERE id=?')->execute($objFiles->id, $objPic->id);
                     }
              }
        
              //auf gueltige pid ueberpruefen
              $objAlb = \Database::getInstance()->prepare('SELECT id, pid FROM tl_gallery_creator_albums WHERE pid!=?')->execute('0');
              while ($objAlb->next())
              {
                     $objParentAlb = \Database::getInstance()->prepare('SELECT id FROM tl_gallery_creator_albums WHERE id=?')->execute($objAlb->pid);
                     if ($objParentAlb->numRows < 1) {
                            \Database::getInstance()->prepare('UPDATE tl_gallery_creator_albums SET pid=? WHERE id=?')->execute('0', $objAlb->id);
                     }
              }

              //Datensaetze ohne definierten Bildnamen loeschen
              \Database::getInstance()->prepare('DELETE FROM tl_gallery_creator_pictures WHERE path=?')->execute('');
              
              //Checks if there belongs a file to each Insert in tl_gallery_creator_pictures
              $objPicture = \Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_pictures ORDER BY pid');
              while ($objPicture->next())
              {
                     if (!is_file(TL_ROOT . '/' . $objPicture->path))
                     {
                            //remove db-insert without a valid picture-file
                            $objDel = \GalleryCreatorPicturesModel::findById($objPicture->id);
                            if ($blnCleanDb == true)
                            {
                                   $objDel->delete();
                            }
                            else
                            {
                                   //show the error-message
                                   $objPicture = \GalleryCreatorPicturesModel::findById($objPicture->id);
                                   $objAlbum = $objPicture->getRelated('pid');
                                   $_SESSION['TL_ERROR'][] = sprintf('The db-entry with ID %s in "tl_gallery_pictures" links to a not existing file. <br>Please check the settings in album with alias: %s or clean the db!', $objPicture->id, $objAlbum->alias);
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
                     if (is_array($arrAlbums)) {
                            foreach ($arrAlbums as $AlbumID)
                            {
                                   $objAlb = \Database::getInstance()->prepare('SELECT id FROM tl_gallery_creator_albums WHERE id=?')->limit('1')->execute($AlbumID);
                                   if ($objAlb->next())
                                   {
                                          $newArr[] = $AlbumID;
                                   }
                            }
                     }
                     \Database::getInstance()->prepare('UPDATE tl_content SET gc_publish_albums=? WHERE id=?')->execute(serialize($newArr), $objCont->id);
              }
       }


       /**
       * Hilfsmethode
       * gibt ein Array mit allen Dateien, Verzeichnissen und Unterverzeichnissen zurueck
       * @param string
       * @return array
       */
       public static function scanRecursive($strPath)
       {
              $arrFiles = array();
              $arrScan = scan($strPath);
              
              foreach ($arrScan as $strFile)
              {
                     if ($strFile == '.svn' || $strFile == '.DS_Store')
                     {
                            continue;
                     }
                     
                     if (is_dir($strPath . '/' . $strFile))
                     {
                            //folders
                            $arrFiles[] = $strPath . '/' . $strFile;
                            $arrFiles = array_merge($arrFiles, self::scanRecursive($strPath . '/' . $strFile));
                     } 
                     else
                     {
                            //files
                            $arrFiles[] = $strPath . '/' . $strFile;
                     }
              }
              return $arrFiles;
       }
}
