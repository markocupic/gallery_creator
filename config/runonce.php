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
 * Class RunonceJob
 *
 * Provide methods regarding gallery_creator albums.
 * @copyright  Marko Cupic 2012
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    Gallery Creator
 */
class RunonceJob extends System
{       
       public function __construct()
       {
		parent::__construct();
		$this->import('Files');
		$this->import('Database');
	}

	public function run()
	{
		try
		{
			//pid bestimmen
			$db = $this->Database->prepare('SELECT id FROM tl_repository_installs WHERE extension=?')->execute('gallery_creator');
			$pid = $db->id;
		}
		catch (Exception $e)
              {
       	       //       
		}

		$pfad = TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums';

		if (!is_dir($pfad))
		{
			// create the gallery_creator-upload-directory
			$folder = new Folder($GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums');
			try
			{
				// 1. entry in tl_repository_instfiles
				$set = array(
					'pid' => $pid,
					'tstamp' => time(),
					'filename' => $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums',
					'filetype' => 'D'
				);
				$log_db = $this->Database->prepare('INSERT INTO tl_repository_instfiles %s')->set($set)->execute();
			}
			catch (Exception $e)
			{
                            //
			}
		}

		try
		{
			// remove runonce.php from tl_repository_instfiles 
			$log_db = $this->Database->prepare('DELETE FROM tl_repository_instfiles WHERE filename=? AND pid=?')->execute('system/runonce.php', $pid);
		}
		catch (Exception $e)
		{
                     //
		}

	}

	public function migrationHack()
	{
		// since contao 3.2.0 'tl_gallery_creator_pictures.path' contains the path to the ressource => 'files/gallery_creator_albums/album_1/bild1.jpg'
		$objPictures = $this->Database->execute("SELECT id,name,path FROM tl_gallery_creator_pictures");
		while ($objPictures->next())
		{
			if (!strstr($objPictures->path, $objPictures->name))
			{
				// add the path to the filename
				$newPath = $objPictures->path . '/' . $objPictures->name;
				$objUpdate = $this->Database->prepare('UPDATE tl_gallery_creator_pictures SET path=? WHERE id=?')->execute($newPath, $objPictures->id);
			}
		}

		//copy all images from tl_files/gallery_creator_albums/*.* to files/gallery_creator_albums/*.*
		if (file_exists(TL_ROOT . '/tl_files/gallery_creator_albums'))
		{
			$objOldUploadPath = new Folder('tl_files/gallery_creator_albums');
			$blnOldUploadPathIsEmpty = $objOldUploadPath->isEmpty() ? true : null;

			$objNewUploadPath = new Folder($GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums');

			if ($objNewUploadPath->isEmpty() && !$blnOldUploadPathIsEmpty)
			{
                            $this->copy_recursive('tl_files/gallery_creator_albums', $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums');
                            
                            // display the message for the backend admin
				$_SESSION["TL_CONFIRM"][] = "It seems to be that you recently made an update of contao from a version < 3.2.0 to a version > 3.2.0";
				$_SESSION["TL_CONFIRM"][] = "If not allready done, the runonce.php copied all files from 'tl_files/gallery_creator_albums' to 'files/gallery_creator_albums'";
       			$_SESSION["TL_CONFIRM"][] = "Please check the system log.";
                            $_SESSION["TL_CONFIRM"][] = "Please check, if all files were moved properly and run the contao-file-synchronisation.";
				$_SESSION["TL_CONFIRM"][] = "If all files were moved properly you can delete the old folder in 'tl_files/gallery_creator_albums'";
			}
		}
	}

	// recursive copy
	public function copy_recursive($strSource, $strDestination)
	{
		$arrRes = scan(TL_ROOT . '/' . $strSource);
		foreach ($arrRes as $res)
		{
			if (is_file(TL_ROOT . '/' . $strSource . '/' . $res))
			{
				$this->renameUploadPathInDb($strSource . '/' . $res);
				$this->Files->copy($strSource . '/' . $res, $strDestination . '/' . $res);
				$this->Files->chmod($strDestination, 0777);
                            GcHelpers::registerInFilesystem($strDestination . '/' . $res);
                            $this->log('Gallery Creator Update Message: "' . $strSource . '/' . $res . '" was copied to the new upload-path: "' . $strDestination . '/' . $res .'".', __METHOD__, GENERAL);
			}
			if (is_dir(TL_ROOT . '/' . $strSource . '/' . $res))
			{
				$folder = new Folder($strDestination . '/' . $res);
                            GcHelpers::registerInFilesystem($strDestination . '/' . $res);
				$this->copy_recursive($strSource . '/' . $res, $strDestination . '/' . $res);
			}
		}
	}
       
       
	public function renameUploadPathInDb($path)
	{
		$objPictureOld = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE path=?')
		                                ->execute($path);
		while ($objPictureOld->next())
		{
			$newPath = str_replace('tl_files/',  $GLOBALS['TL_CONFIG']['uploadPath'].'/', $objPictureOld->path);
			// change new uploadPath in the db
			$this->Database->prepare('UPDATE tl_gallery_creator_pictures SET path=? WHERE id=?')->execute($newPath, $objPictureOld->id);
                     $this->log('Gallery Creator Update Message: tl_gallery_creator_pictures.path with ID ' . $objPictureOld->id . ' has been renamed from ' . $objPictureOld->path . ' to ' . $newPath . '.', __METHOD__, GENERAL);
		}
	}

}

$objRunonceJob = new RunonceJob();
$objRunonceJob->migrationHack();
$objRunonceJob->run();