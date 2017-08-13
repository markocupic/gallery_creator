<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2015 Leo Feyer
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
 * @copyright  Marko Cupic 2017
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class GcHelpers extends \System
{


    /**
     * Insert a new entry in tl_gallery_creator_pictures
     *
     * @param integer
     * @param string
     * $intAlbumId - albumId
     * $strFilepath - filepath -> files/gallery_creator_albums/albumalias/filename.jpg
     * @return bool
     */
    public static function createNewImage($intAlbumId, $strFilepath)
    {
        // Get the file-object
        $objFile = new \File($strFilepath);
        if (!$objFile->isGdImage)
        {
            return false;
        }

        // Get the album-object
        $objAlbum = \GalleryCreatorAlbumsModel::findById($intAlbumId);

        // Get the assigned album directory
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

        // Check if the file is stored in the album-directory or if it is stored in an external directory
        $blnExternalFile = false;
        if (\Input::get('importFromFilesystem'))
        {
            $blnExternalFile = strstr($objFile->dirname, $assignedDir) ? false : true;
        }

        // Get the album object and the alias
        $strAlbumAlias = $objAlbum->alias;
        // Db insert
        $objImg = new \GalleryCreatorPicturesModel();
        $objImg->tstamp = time();
        $objImg->pid = $objAlbum->id;
        $objImg->externalFile = $blnExternalFile ? "1" : "";
        $objImg->save();

        if ($objImg->id)
        {
            $insertId = $objImg->id;
            // Get the next sorting index
            $objImg_2 = \Database::getInstance()
                ->prepare('SELECT MAX(sorting)+10 AS maximum FROM tl_gallery_creator_pictures WHERE pid=?')
                ->execute($objAlbum->id);
            $sorting = $objImg_2->maximum;

            // If filename should be generated
            if (!$objAlbum->preserve_filename && $blnExternalFile === false)
            {
                $newFilepath = sprintf('%s/alb%s_img%s.%s', $assignedDir, $objAlbum->id, $insertId,
                    $objFile->extension);
                $objFile->renameTo($newFilepath);
            }


            if (is_file(TL_ROOT . '/' . $objFile->path))
            {
                // Get the userId
                $userId = '0';
                if (TL_MODE == 'BE')
                {
                    $userId = \BackendUser::getInstance()->id;
                }

                // The album-owner is automaticaly the image owner, if the image was uploaded by a by a frontend user
                if (TL_MODE == 'FE')
                {
                    $userId = $objAlbum->owner;
                }

                // Get the FilesModel
                $objFileModel = \FilesModel::findByPath($objFile->path);

                // Finally save the new image in tl_gallery_creator_pictures
                $objPicture = \GalleryCreatorPicturesModel::findByPk($insertId);
                $objPicture->uuid = $objFileModel->uuid;
                $objPicture->owner = $userId;
                $objPicture->date = $objAlbum->date;
                $objPicture->sorting = $sorting;
                $objPicture->save();

                \System::log('A new version of tl_gallery_creator_pictures ID ' . $insertId . ' has been created',
                    __METHOD__, TL_GENERAL);

                // Check for a valid preview-thumb for the album
                $objAlbum = \GalleryCreatorAlbumsModel::findByAlias($strAlbumAlias);
                if ($objAlbum !== null)
                {
                    if ($objAlbum->thumb == "")
                    {
                        $objAlbum->thumb = $insertId;
                        $objAlbum->save();
                    }
                }

                // GalleryCreatorImagePostInsert - HOOK
                if (isset($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']) && is_array($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert'] as $callback)
                    {
                        $objClass = self::importStatic($callback[0]);
                        $objClass->$callback[1]($insertId);
                    }
                }

                return true;
            }
            else
            {
                if ($blnExternalFile === true)
                {
                    $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file'],
                        $strFilepath);
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
     * Move uploaded file to the album directory
     *
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


        // Do not overwrite files of the same filename
        $intCount = count($_FILES[$strName]['name']);
        for ($i = 0; $i < $intCount; $i++)
        {
            if (strlen($_FILES[$strName]['name'][$i]))
            {
                // Generate unique filename
                $_FILES[$strName]['name'][$i] = basename(self::generateUniqueFilename($objUploadDir->path . '/' . $_FILES[$strName]['name'][$i]));
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
            // Store file in tl_files
            \Dbafs::addResource($strFileSrc);
        }

        return $arrUpload;
    }

    /**
     * generate a unique filepath for a new picture
     * @param $strFilename
     * @return bool|string
     * @throws Exception
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

        // Falls Datei schon existiert, wird hinten eine Zahl mit fuehrenden Nullen angehaengt -> filename0001.jpg
        $i = 0;
        $isUnique = false;
        do
        {
            $i++;
            if (!file_exists(TL_ROOT . '/' . $dirname . '/' . $basename . '.' . $extension))
            {
                //exit loop when filename is unique
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

                // Integer mit fuehrenden Nullen an den Dateinamen anhaengen ->filename0001.jpg
                $basename = $filename . '_' . $suffix;

                // Break after 100 loops
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
     * @param string $uploader
     * @return string
     */
    public static function generateUploader($uploader = 'be_gc_html5_uploader')
    {

        //create the template object
        $objTemplate = new \BackendTemplate($uploader);


        // maxFileSize
        $objTemplate->maxFileSize = $GLOBALS['TL_CONFIG']['maxFileSize'];

        // $_FILES['file']
        $objTemplate->strName = 'file';

        // parse the jumloader view and return it
        return $objTemplate->parse();
    }


    /**
     * @param string
     * @param integer
     * @return bool
     * $src - path to the filesource (f.ex: files/folder/img.jpeg)
     * angle - the rotation angle is interpreted as the number of degrees to rotate the image anticlockwise.
     * angle shall be 0,90,180,270
     */
    public static function imageRotate($src, $angle)
    {
        $src = html_entity_decode($src);

        if (!file_exists(TL_ROOT . '/' . $src))
        {
            \Message::addError(sprintf('File "%s" not found.', $src));
            return false;
        }

        $objFile = new \File($src);
        if (!$objFile->isGdImage)
        {
            \Message::addError(sprintf('File "%s" could not be rotated because it is not an image.', $src));
            return false;
        }

        if (!function_exists('imagerotate'))
        {
            \Message::addError(sprintf('PHP function "%s" is not installed.', 'imagerotate'));
            return false;
        }

        $source = imagecreatefromjpeg(TL_ROOT . '/' . $src);

        //rotate
        $imgTmp = imagerotate($source, $angle, 0);

        // Output
        imagejpeg($imgTmp, TL_ROOT . '/' . $src);
        imagedestroy($source);
        return true;


    }

    /**
     * @param integer
     * @param string
     * Import images from a folder
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
                    $images[$objFile->path] = array(
                        'uuid' => $objFilesModel->uuid,
                        'basename' => $objFile->basename,
                        'path' => $objFile->path
                    );
                }
            }
            else
            {
                // If it is a directory, then store its files in the array
                $objSubfilesModel = \FilesModel::findMultipleFilesByFolder($objFilesModel->path);
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
                        $images[$objFile->path] = array(
                            'uuid' => $objSubfilesModel->uuid,
                            'basename' => $objFile->basename,
                            'path' => $objFile->path
                        );
                    }
                }
            }
        }

        if (count($images))
        {
            $arrPictures = array(
                'uuid' => array(),
                'path' => array(),
                'basename' => array()
            );

            $objPictures = \Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=?')->execute($intAlbumId);
            $arrPictures['uuid'] = $objPictures->fetchEach('uuid');
            $arrPictures['path'] = $objPictures->fetchEach('path');
            foreach ($arrPictures['path'] as $path)
            {
                $arrPictures['basename'][] = basename($path);
            }

            $objAlb = \GalleryCreatorAlbumsModel::findById($intAlbumId);
            foreach ($images as $image)
            {
                // Prevent duplicate entries
                if (in_array($image['uuid'], $arrPictures['uuid']))
                {
                    continue;
                }

                // Prevent duplicate entries
                if (in_array($image['basename'], $arrPictures['basename']))
                {
                    continue;
                }

                \Input::setGet('importFromFilesystem', 'true');
                if ($GLOBALS['TL_CONFIG']['gc_album_import_copy_files'])
                {

                    $strSource = $image['path'];

                    // Get the album upload directory
                    $objFolderModel = \FilesModel::findByUuid($objAlb->assignedDir);
                    $errMsg = 'Aborted import process, because there is no upload folder assigned to the album with ID ' . $objAlb->id . '.';
                    if ($objFolderModel === null)
                    {
                        die($errMsg);
                    }
                    if ($objFolderModel->type != 'folder')
                    {
                        die($errMsg);
                    }
                    if (!is_dir(TL_ROOT . '/' . $objFolderModel->path))
                    {
                        die($errMsg);
                    }

                    $strDestination = self::generateUniqueFilename($objFolderModel->path . '/' . basename($strSource));
                    if (is_file(TL_ROOT . '/' . $strSource))
                    {
                        // Copy image to the upload folder
                        $objFile = new \File($strSource);
                        $objFile->copyTo($strDestination);
                        \Dbafs::addResource($strSource);
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
}
