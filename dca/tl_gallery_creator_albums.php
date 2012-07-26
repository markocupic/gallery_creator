<?php
if (!defined('TL_ROOT'))
	die('You can not access this file directly!');
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Marko Cupic 2010
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    gallery_creator
 * @license    GNU/LGPL
 * @filesource
 */

//Hilfsklasse einbinden
require_once (TL_ROOT . '/system/modules/gallery_creator/helpers/GcHelpers.php');

/**
 * Table tl_gallery_creator_albums
 */
$GLOBALS['TL_DCA']['tl_gallery_creator_albums'] = array(
	// Config
	'config' => array(
		'ctable' => array('tl_gallery_creator_pictures'),
		'doNotCopyRecords' => true,
		'dataContainer' => 'Table',
		'onload_callback' => array(
			array(
				'tl_gallery_creator_albums',
				'onloadCbSetUpPalettes'
			),
			array(
				'tl_gallery_creator_albums',
				'onloadCbReviseTable'
			),
			array(
				'tl_gallery_creator_albums',
				'onloadCbCheckFolderSettings'
			),
			array(
				'tl_gallery_creator_albums',
				'onloadCbImportImages'
			),
			array(
				'tl_gallery_creator_albums',
				'onloadCbFileupload'
			),
			array(
				'tl_gallery_creator_albums',
				'onloadCbCheckRefThumb'
			),
			array(
				'tl_gallery_creator_albums',
				'onloadCbGetGcCteElements'
			)
		),
		'ondelete_callback' => array( array(
				'tl_gallery_creator_albums',
				'ondeleteCb'
			))
	),

	// List
	'list' => array(
		'sorting' => array(
			'panelLayout' => 'limit,sort',
			'mode' => 5,
			'paste_button_callback' => array(
				'tl_gallery_creator_albums',
				'buttonCbPastePicture'
			)
		),
		'label' => array(
			'fields' => array('name'),
			'format' => '<span style="#padding-left#"><img src="#icon#" /></span> #datum# <span style="color:#b3b3b3; padding-left:3px;">[%s] [#count_pics# images]</span>',
			'label_callback' => array(
				'tl_gallery_creator_albums',
				'labelCb'
			)
		),
		'global_operations' => array('all' => array(
				'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href' => 'act=select',
				'class' => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)),
		'operations' => array(
			'edit' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['edit'],
				'href' => 'table=tl_gallery_creator_pictures',
				'icon' => 'edit.gif',
				'attributes' => 'class="contextmenu"',
				'button_callback' => array(
					'tl_gallery_creator_albums',
					'buttonCbEdit'
				)
			),
			'delete' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['delete'],
				'href' => 'act=delete',
				'icon' => 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback' => array(
					'tl_gallery_creator_albums',
					'buttonCbDelete'
				)
			),
			'upload_images' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['upload_images'],
				'icon' => 'system/modules/gallery_creator/html/photo.png',
				'button_callback' => array(
					'tl_gallery_creator_albums',
					'buttonCbAddImages'
				)
			),
			'import_images' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['import_images'],
				'icon' => 'system/modules/gallery_creator/html/photo_album.png',
				'button_callback' => array(
					'tl_gallery_creator_albums',
					'buttonCbImportImages'
				)
			),
			'cut' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['cut'],
				'href' => 'act=paste&mode=cut',
				'icon' => 'cut.gif',
				'attributes' => 'onclick="Backend.getScrollOffset();"',
				'button_callback' => array(
					'tl_gallery_creator_albums',
					'buttonCbCutPicture'
				)
			)
		)
	),

	// Palettes
	'palettes' => array(
		'__selector__' => array('protected'),
		'default' => '{album_info},published,name,alias,album_info,displ_alb_in_this_ce,owner,date,event_location,thumb,comment;{protection:hide},protected',
		'restricted_user' => '{album_info},link_edit_images,album_info',
		'fileupload' => '{upload_settings},preserve_filename,img_resolution,img_quality;{uploader},fileupload',
		'import_images' => '{upload_settings},preserve_filename,singleSRC'
	),

	// Subpalettes
	'subpalettes' => array('protected' => 'groups'),

	// Fields
	'fields' => array(

		'published' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['published'],
			'inputType' => 'checkbox',
			'eval' => array('submitOnChange' => true)
		),

		'date' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['date'],
			'inputType' => 'text',
			'eval' => array(
				'mandatory' => true,
				'maxlength' => 10,
				'datepicker' => $this->getDatePickerString(),
				'submitOnChange' => true,
				'rgxp' => 'date',
				'tl_class' => 'w50 wizard m12',
				'submitOnChange' => false
			)
		),

		'owner' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owner'],
			'default' => $this->User->id,
			'eval' => array(
				'includeBlankOption' => true,
				'blankOptionLabel' => 'noName',
				'doNotShow' => true,
				'nospace' => true,
				'tl_class' => 'w50 m12'
			),
			'foreignKey' => 'tl_user.name',
			'inputType' => 'select'
		),

		'owners_name' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owners_name'],
			'default' => $this->User->name,
			'eval' => array(
				'doNotShow' => true,
				'tl_class' => 'clr w50 m12 readonly'
			)
		),

		'event_location' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['event_location'],
			'exclude' => true,
			'inputType' => 'text',
			'eval' => array(
				'mandatory' => false,
				'tl_class' => 'clr w50 m12',
				'submitOnChange' => false
			)
		),

		'name' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['name'],
			'inputType' => 'text',
			'eval' => array(
				'mandatory' => true,
				'tl_class' => 'w50 m12',
				'submitOnChange' => false
			)
		),

		'alias' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['alias'],
			'inputType' => 'text',
			'eval' => array(
				'doNotShow' => false,
				'doNotCopy' => true,
				'maxlength' => 50,
				'tl_class' => 'w50 m12',
				'unique' => true
			),
			'save_callback' => array( array(
					'tl_gallery_creator_albums',
					'saveCbGenerateAlias'
				))
		),

		'comment' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['comment'],
			'exclude' => true,
			'inputType' => 'textarea',
			'eval' => array(
				'tl_class' => 'clr long',
				'style' => 'height:7em;',
				'allowHtml' => false,
				'submitOnChange' => false
			)
		),

		'thumb' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['thumb'],
			'inputType' => 'select',
			'options_callback' => array(
				'tl_gallery_creator_albums',
				'optionsCbThumb'
			),
			'eval' => array(
				'doNotShow' => true,
				'includeBlankOption' => true,
				'nospace' => true,
				'rgxp' => 'digit',
				'maxlength' => 64,
				'tl_class' => 'w50 m12',
				'submitOnChange' => true
			)
		),

		'fileupload' => array(
			'input_field_callback' => array(
				'tl_gallery_creator_albums',
				'inputFieldCbGenerateJumpLoader'
			),
			'eval' => array('doNotShow' => true)
		),

		'album_info' => array(
			'input_field_callback' => array(
				'tl_gallery_creator_albums',
				'inputFieldCbGenerateAlbumInformations'
			),
			'eval' => array('doNotShow' => true)
		),
		'displ_alb_in_this_ce' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['displ_alb_in_this_ce'],
			'exclude' => true,
			'inputType' => 'checkbox',
			'options_callback' => array(
				'tl_gallery_creator_albums',
				'optionsCbDisplAlbInThisContentElements'
			),
			'save_callback' => array( array(
					'tl_gallery_creator_albums',
					'saveCbDisplAlbInThisContentElements'
				)),
			'eval' => array(
				'multiple' => true,
				'doNotShow' => false,
				'submitOnChange' => false
			)
		),
		//Wert wird in tl_user gespeichert
		'img_resolution' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_resolution'],
			'default' => '600',
			'inputType' => 'select',
			'load_callback' => array( array(
					'tl_gallery_creator_albums',
					'loadCbGetImageResolution'
				)),
			'save_callback' => array( array(
					'tl_gallery_creator_albums',
					'saveCbSaveImageResolution'
				)),
			'options' => range(100, 3500, 50),
			'eval' => array(
				'doNotShow' => true,
				'tl_class' => 'w50',
				'submitOnChange' => true
			)
		),

		//Wert wird in tl_user gespeichert
		'img_quality' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_quality'],
			'default' => '1000',
			'inputType' => 'select',
			'load_callback' => array( array(
					'tl_gallery_creator_albums',
					'loadCbGetImageQuality'
				)),
			'save_callback' => array( array(
					'tl_gallery_creator_albums',
					'saveCbSaveImageQuality'
				)),
			'options' => range(100, 1000, 100),
			'eval' => array(
				'doNotShow' => true,
				'tl_class' => 'w50',
				'submitOnChange' => true
			)
		),

		'preserve_filename' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['preserve_filename'],
			'inputType' => 'checkbox',
			'eval' => array(
				'doNotShow' => true,
				'submitOnChange' => true
			),
			'default' => true
		),

		'singleSRC' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_content']['singleSRC'],
			'inputType' => 'fileTree',
			'eval' => array(
				'doNotShow' => true,
				'fieldType' => 'checkbox',
				'filesOnly' => false,
				'files' => true,
				'mandatory' => false,
				'tl_class' => 'clr'
			)
		),

		'protected' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['protected'],
			'exclude' => true,
			'inputType' => 'checkbox',
			'eval' => array(
				'doNotShow' => true,
				'submitOnChange' => true,
				'tl_class' => 'clr'
			)
		),

		'groups' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['groups'],
			'inputType' => 'checkbox',
			'foreignKey' => 'tl_member_group.name',
			'eval' => array(
				'doNotShow' => true,
				'mandatory' => true,
				'multiple' => true,
				'tl_class' => 'clr'
			)
		)
	)
);

/**
 * Class tl_gallery_creator_albums
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Marko Cupic
 * @author     Marko Cupic
 * @package    Controller
 */
class tl_gallery_creator_albums extends Backend
{
	public $restrictedUser = false;

	/**
	 * absoluter Pfad ins Bildverzeichnis
	 * @var string
	 */
	public $imgDir;

	/**
	 *  Pfad ab TL_ROOT ins Bildverzeichnis
	 * @var string
	 */
	public $relImgDir;

	public function __construct()
	{
		parent::__construct();

		$this->import('BackendUser', 'User');
		$this->import('Environment');
		$this->import('Files');
		$this->GcHelpers = new GcHelpers;

		//absoluter Pfad zum Upload-Dir
		$this->imgDir = TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/';
		//relativer Pfad zum Upload-Dir fuer safe-mode-hack
		$this->relImgDir = $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/';
		//parse Backend Template Hook registrieren
		$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array(
			'tl_gallery_creator_albums',
			'myParseBackendTemplate'
		);

		if ($_SESSION['BE_DATA']['CLIPBOARD']['tl_gallery_creator_albums']['mode'] == 'copyAll')
		{
			$this->redirect('contao/main.php?do=gallery_creator&clipboard=1');
		}
	}


	/**
	 * Return the add-images-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbAddImages($row, $href, $label, $title, $icon, $attributes)
	{
		$href = $href . 'id=' . $row['id'] . '&act=edit&table=tl_gallery_creator_albums&mode=fileupload';
		return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . ' style="margin-right:5px">' . $this->generateImage($icon, $label) . '</a>';
	}


	/**
	 * Return the cut-picture-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbCutPicture($row, $href, $label, $title, $icon, $attributes)
	{
		//Albenbesitzer und admins koennen Alben verschieben (thanks to nachtarbeit)
		$objAlb = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_albums WHERE id=?')->execute($row['id']);
		return (($this->User->id == $objAlb->owner || $this->User->isAdmin || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) ? ' <a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ' : ' ' . $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ');
	}


	/**
	 * Return the delete-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbDelete($row, $href, $label, $title, $icon, $attributes)
	{
		$objAlb = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_albums WHERE id=?')->execute($row['id']);
		return ($this->User->isAdmin || $this->User->id == $objAlb->owner || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) ? '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
	}


	/**
	 * Return the edit-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbEdit($row, $href, $label, $title, $icon, $attributes)
	{
		return '<a href="' . $this->addToUrl($href . '&id=' . $row['id'], 1) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
	}


	/**
	 * Return the import-images button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbImportImages($row, $href, $label, $title, $icon, $attributes)
	{
		$href = $href . 'id=' . $row['id'] . '&act=edit&table=tl_gallery_creator_albums&mode=import_images';
		return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a>';
	}


	/**
	 * Return the paste-picture-button
	 * @param object
	 * @param array
	 * @param string
	 * @param boolean
	 * @param array
	 * @return string
	 */
	public function buttonCbPastePicture(DataContainer $dc, $row, $table, $cr, $arrClipboard = false)
	{
		$disablePA = false;
		$disablePI = false;

		// Disable all buttons if there is a circular reference
		if ($this->User->isAdmin && $arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id']))))
		{
			$disablePA = true;
			$disablePI = true;
		}

		// Return the buttons
		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']), 'class="blink"');
		$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']), 'class="blink"');

		if ($row['id'] > 0)
		{
			$return = $disablePA ? $this->generateImage('pasteafter_.gif', '', 'class="blink"') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&mode=1&pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteAfter . '</a> ';
		}
		return $return . ($disablePI ? $this->generateImage('pasteinto_.gif', '', 'class="blink"') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&mode=2&pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteInto . '</a> ');
	}


	/**
	 * Checks if the current user obtains full rights or only restricted rights on the selected album
	 */
	public function checkUserRole()
	{
		$objUser = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_albums WHERE id=?')->execute($this->Input->get('id'));

		if ($this->User->isAdmin || true == $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
		{
			$this->restrictedUser = false;
			return;
		}

		if ($objUser->owner != $this->User->id)
		{
			$this->restrictedUser = true;
			return;
		}
		//...so the current user is the album owner
		$this->restrictedUser = false;
	}


	/**
	 * insert a new line in tl_gallery_creator_pictures
	 * @param string
	 * @param string
	 * @param int
	 */
	private function createNewImage($strFilename, $strPath, $intExternalFile = "")
	{
		$objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->executeUncached($this->Input->get('id'));

		//db insert
		$objImg = $this->Database->prepare('INSERT INTO tl_gallery_creator_pictures SET tstamp=?, pid=?, externalFile=?')->execute(time(), $this->Input->get('id'), $intExternalFile);
		$lastInsertId = false;
		if ($objImg->affectedRows)
		{
			$lastInsertId = $objImg->insertId;
			//Ord-Nr generieren

			$objImg_2 = $this->Database->prepare('SELECT MAX(sorting)+10 AS maximum FROM tl_gallery_creator_pictures WHERE pid=?')->executeUncached($this->Input->get('id'));
			$nextOrd = $objImg_2->maximum;
			$objAlbum = $this->Database->prepare('SELECT preserve_filename,alias FROM tl_gallery_creator_albums WHERE id=?')->executeUncached($this->Input->get('id'));

			//Wenn der Dateiname generiert werden soll
			if ($this->Input->get('mode') == 'JumpLoader' && false == $objAlbum->preserve_filename)
			{
				$old_filename = $strFilename;
				$pathinfo = pathinfo($strFilename);
				$extension = $pathinfo['extension'];
				$strFilename = 'alb' . $this->Input->get('id') . '_img' . $lastInsertId . '.' . strtolower($extension);
				$this->Files->rename($this->relImgDir . $objAlbum->alias . '/' . $old_filename, $this->relImgDir . $objAlbum->alias . '/' . $strFilename);
			}

			//Dateinamen, Bildbesitzer und Datum in der db abspeichern
			$objImg_3 = $this->Database->prepare('UPDATE tl_gallery_creator_pictures SET name=?, path=?, owner=?, date=? , sorting=? WHERE id=?')->execute($strFilename, $strPath, $this->User->id, time(), $nextOrd, $lastInsertId);

			//galleryCreatorImagePostInsert - HOOK
			if (isset($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']) && is_array($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert']))
			{
				foreach ($GLOBALS['TL_HOOKS']['galleryCreatorImagePostInsert'] as $callback)
				{
					$this->import($callback[0]);
					$this->$callback[0]->$callback[1]($lastInsertId);
				}
			}

			if (is_file(TL_ROOT . '/' . $strPath . '/' . $strFilename))
			{
				$this->log('A new version of tl_gallery_creator_pictures ID ' . $lastInsertId . ' has been created', __CLASS__ . ' ' . __FUNCTION__ . '()', TL_GENERAL);
			}
			else
			{
				if ($intExternalFile === 1)
				{
					$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file'], $strFilename);
				}
				else
				{
					$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['uploadError'], $strFilename);
				}
				$this->log('Unable to create the new image in: ' . $strPath . '/' . $strFilename . '!', __METHOD__, TL_ERROR);
			}
		}
	}


	/**
	 * generate a unique filename for a new picture
	 * @param string
	 */
	protected function createUniqueFilename($strFilename)
	{
		$strFilename = utf8_romanize($strFilename);
		$strFilename = str_replace('"', '', $strFilename);
		$strFilename = str_replace(' ', '_', $strFilename);
		if (preg_match('/\.$/', $strFilename))
		{
			throw new Exception($GLOBALS['TL_LANG']['ERR']['invalidName']);
			return;
		}

		//Falls Datei schon existiert wird hinten eine Zahl angehaengt -> filename0001.jpg
		for ($i = 2; $i < 1000; $i++)
		{
			$objImg = $this->Database->prepare('SELECT count(id) AS items FROM tl_gallery_creator_pictures WHERE name=?')->execute($strFilename);
			if ($objImg->items < 1)
				break;

			$info = pathinfo($strFilename);
			//Dateinamen ohne Extension
			$file_name = basename($strFilename, '.' . $info['extension']);
			if ($i != 2)
				$file_name = substr($file_name, 0, -5);
			$number = str_pad($i, 4, '0', STR_PAD_LEFT);
			//Integer mit fuehrenden Nullen an den Dateinamen anhaengen ->filename0001.jpg
			$strFilename = $file_name . '_' . $number . '.' . $info['extension'];
			if ($i == 1000)
			{
				$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['fileExists'], $strFilename);
				return;
			}
		}
		return $strFilename;
	}


	/**
	 * return the level of an album or subalbum (level_0, level_1, level_2,...)
	 * @param integer
	 * @return integer
	 */
	private function getLevel($pid)
	{
		$level = 0;
		if ($pid == '0')
			return $level;
		$hasParent = true;
		while ($hasParent)
		{
			$level++;
			$mysql = $this->Database->prepare('SELECT pid FROM tl_gallery_creator_albums WHERE id=?')->executeUncached($pid);
			if ($mysql->pid < 1)
				$hasParent = false;
			$pid = $mysql->pid;
		}
		return $level;
	}


	/**
	 * generate the watermark html for the jumploader applet
	 * @return string
	 */
	public function getWatermarkHtml()
	{
		if (is_file(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['gc_watermark_path']))
		{
			$file = $GLOBALS['TL_CONFIG']['gc_watermark_path'];
			$opacity = $GLOBALS['TL_CONFIG']['gc_watermark_opacity'];
			$valign = $GLOBALS['TL_CONFIG']['gc_watermark_valign'];
			$halign = $GLOBALS['TL_CONFIG']['gc_watermark_halign'];
			$objFile = new File($file);
			if ($objFile->isGdImage)
			{
				return '
<!-- Wasserzeichen-->
<param name="uc_scaledInstanceWaterMarkNames" value="Watermark_1" />
<param name="Watermark_1" value="halign=' . $halign . ';valign=' . $valign . ';opacityPercent=' . $opacity . ';imageUrl=' . $this->Environment->base . $file . '"/>
				';
			}
		}
	}


	/**
	 * return the path to the watermark-background-image
	 * @return string
	 */
	public function getWatermarkPath()
	{
		return $this->User->gc_watermark_path;
	}


	/**
	 * Input-field-callback
	 * return the html-table with the album-information for restricted users
	 * @return string
	 */
	public function inputFieldCbGenerateAlbumInformations()
	{
		$objAlb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($this->Input->get('id'));
		$objUser = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objAlb->owner);

		//check User Role
		$this->checkUserRole();
		if (false == $this->restrictedUser)
		{
			$output = '
<div class="album_infos">
<br /><br />
<table cellpadding="0" cellspacing="0" width="100%" summary="">
	<tr class="odd">
		<td style="width:25%"><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['id'][0] . ': </strong></td>
		<td>' . $objAlb->id . '</td>
	</tr>
</table>
</div>
				';
			return $output;
		}
		else
		{
			$output = '
<div class="album_infos">
<table cellpadding="0" cellspacing="0" width="100%" summary="">
	<tr class="odd">
		<td style="width:25%"><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['id'][0] . ': </strong></td>
		<td>' . $objAlb->id . '</td>
	</tr>
	<tr>
		<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['date'][0] . ': </strong></td>
		<td>' . $this->parseDate("Y-m-d", $objAlb->date) . '</td>
	</tr>
	<tr class="odd">
		<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owners_name'][0] . ': </strong></td>
		<td>' . $objUser->name . '</td>
	</tr>
	<tr>
		<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['name'][0] . ': </strong></td>
		<td>' . $objAlb->name . '</td>
	</tr>

	<tr class="odd">
		<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['comment'][0] . ': </strong></td>
		<td>' . $objAlb->comment . '</td>
	</tr>
	<tr>
		<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['thumb'][0] . ': </strong></td>
		<td>' . $objAlb->thumb . '</td>
	</tr>
</table>
</div>
		';

			return $output;
		}
	}


	/**
	 * Input Field Callback for fileupload
	 * return the html for the jumploader-applet
	 * @return string
	 */
	public function inputFieldCbGenerateJumpLoader()
	{
		$output = '
<applet id="jumpLoaderApplet" name="jumpLoaderApplet"
	code="jmaster.jumploader.app.JumpLoaderApplet.class"
	archive="' . $this->Environment->base . 'system/modules/gallery_creator/jumpLoader/mediautil_z.jar,' . $this->Environment->base . 'system/modules/gallery_creator/jumpLoader/sanselan_z.jar,' . $this->Environment->base . 'system/modules/gallery_creator/jumpLoader/jumploader_z.jar"
	width="650"
	height="600"
	mayscript>
	<param name="uc_imageEditorEnabled" value="true"/>
	<param name="uc_uploadUrl" value="' . $this->Environment->base . $this->Environment->request . '&request_token=' . REQUEST_TOKEN . '"/>

	<param name="uc_imageEditorEnabled" value="true"/>
	<param name="uc_useLosslessJpegTransformations" value="true"/>


	<!-- Dateifilter einschalten -->
	<param name="uc_fileNamePattern" value="^.+\.(?i)((jpg)|(jpeg))$"/>
	<param name="vc_fileNamePattern" value="^.+\.(?i)((jpg)|(jpeg))$"/>

	<!--Bildrotator einschalten -->
	<param name="uc_imageRotateEnabled" value="true"/>

	<!--Bildaufloesung aendern: ja -->
	<param name="uc_uploadScaledImages" value="true"/>

	<!-- Uploadinformationen im Uploadscript im Array $_FILES["file"] verfuegbar-->
	<param name="uc_scaledInstanceNames" value="file"/>

	' . $this->getWatermarkHtml() . '

	<!-- Exif-Daten beim Skalieren beibehalten-->
	<param name="uc_scaledInstancePreserveMetadata" value="true"/>

	<!--Bildaufloesung nach Upload -->
	<param name="uc_scaledInstanceDimensions" value="' . $this->User->gc_img_resolution . 'x' . $this->User->gc_img_resolution . '"/>
	<param name="uc_scaledInstanceQualityFactors" value="' . $this->User->gc_img_quality . '"/>

	<param name="uc_deleteTempFilesOnRemove" value="true"/>
		';

		//Falls zu JumpLoader eine passende Sprachdatei vorhanden ist, diese Laden
		$language = strtolower($this->User->language);
		if ($this->User->language == "" || $this->User->language == "en")
			$language = null;
		$lang_dir = str_replace('/dca', '/jumpLoader/lang/', dirname(__FILE__));
		if (file_exists($lang_dir . 'messages_' . $language . '.zip'))
		{
			$output .= '
	<!-- Sprachdatei-Einstellungen -->
	<param name="ac_messagesZipUrl" value="' . $this->Environment->base . 'system/modules/gallery_creator/jumpLoader/lang/messages_' . $language . '.zip"/>
			';
		}
		$output .= '</applet>';
		$output .= '
<script type="text/javascript">
	var uploader = document.jumpLoaderApplet.getUploader();
	var attrSet = uploader.getAttributeSet();
	var attr = attrSet.createStringAttribute("REQUEST_TOKEN", "' . REQUEST_TOKEN . '");
	attr.setSendToServer(true);
</script>';
		return $output;
	}


	/**
	 * check if album has subalbums
	 * @param integer
	 * @return bool
	 */
	private function isNode($id)
	{
		$mysql = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE pid=?')->executeUncached($id);
		if ($mysql->numRows > 0)
			return true;
	}


	/**
	 * label-callback for the albumlisting
	 * @param array
	 * @param string
	 * @return string
	 */
	public function labelCb($row, $label)
	{
		$mysql = $this->Database->prepare('SELECT count(id) as countImg FROM tl_gallery_creator_pictures WHERE pid=?')->execute($row['id']);
		$label = str_replace('#count_pics#', $mysql->countImg, $label);
		$label = str_replace('#datum#', date('Y-m-d', $row['date']), $label);
		$label = str_replace('#icon#', "system/modules/gallery_creator/html/slides.png", $label);
		$padding = $this->isNode($row["id"]) ? 3 * $this->getLevel($row["pid"]) : 20 + (3 * $this->getLevel($row["pid"]));
		$label = str_replace('#padding-left#', 'padding-left:' . $padding . 'px;', $label);
		return $label;
	}


	/**
	 * load-callback for image-quality
	 * @return string
	 */
	public function loadCbGetImageQuality()
	{
		return $this->User->gc_img_quality;

	}


	/**
	 * load-callback for image-resolution
	 * @return string
	 */
	public function loadCbGetImageResolution()
	{
		return $this->User->gc_img_resolution;

	}


	/**
	 * Parse Backend Template Hook
	 * @param string
	 * @param string
	 * @return string
	 */
	public function myParseBackendTemplate($strContent, $strTemplate)
	{

		if ($this->Input->get('act') == 'select')
		{
			//Entfernt Buttons
			if ($this->Input->get('table') != 'tl_gallery_creator_pictures')
			{
				$strContent = preg_replace('/<input type=\"submit\" name=\"delete\"((\r|\n|.)+?)>/', '', $strContent);
			}
			$strContent = preg_replace('/<input type=\"submit\" name=\"cut\"((\r|\n|.)+?)>/', '', $strContent);
			$strContent = preg_replace('/<input type=\"submit\" name=\"copy\"((\r|\n|.)+?)>/', '', $strContent);
		}

		if ($this->Input->get('mode') == 'fileupload')
		{
			//form encode
			$strContent = str_replace('application/x-www-form-urlencoded', 'multipart/form-data', $strContent);
			//Entfernt Buttons
			$strContent = preg_replace('/<input type=\"submit\" name=\"save\"((\r|\n|.)+?)>/', '', $strContent);
			$strContent = preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/', '', $strContent);
			$strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
			$strContent = preg_replace('/<input type=\"submit\" name=\"uploadNback\"((\r|\n|.)+?)>/', '', $strContent);
		}

		if ($this->Input->get('mode') == 'import_images')
		{
			$strContent = preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/', '', $strContent);
			$strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
			$strContent = preg_replace('/<input type=\"submit\" name=\"uploadNback\"((\r|\n|.)+?)>/', '', $strContent);
		}
		return $strContent;
	}


	/**
	 * on-delete-callback
	 */
	public function ondeleteCb()
	{
		if ($this->Input->get('act') != 'deleteAll')
		{
			$this->checkUserRole();
			if ($this->restrictedUser)
			{
				$this->log('Datensatz mit ID ' . $this->Input->get('id') . ' wurde von einem nicht authorisierten Benutzer versucht aus tl_gallery_creator_albums zu loeschen.', __METHOD__, TL_ERROR);
				$this->redirect('contao/main.php?do=error');
			}

			//auch alle Kindelemente loeschen
			$arrDeletedAlbums = $this->GcHelpers->getAllSubalbums($this->Input->get('id'));
			$arrDeletedAlbums = array_merge(array($this->Input->get('id')), $arrDeletedAlbums);

			foreach ($arrDeletedAlbums as $idDelAlbum)
			{
				$objAlb = $this->Database->prepare('SELECT alias, owner FROM tl_gallery_creator_albums WHERE id=?')->execute($idDelAlbum);
				if ($this->User->isAdmin || $objAlb->owner == $this->User->id || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
				{
					$objAlbDel = $this->Database->prepare('DELETE FROM tl_gallery_creator_albums WHERE id=?')->execute($idDelAlbum);
					//Alle Bilder des Verzeichnisses und das Verzeichnis selber loeschen
					$folder = new Folder($this->relImgDir . $objAlb->alias);
					$this->Files->chmod($this->relImgDir . $objAlb->alias, 0777);
					$folder->delete($this->relImgDir . $objAlb->alias);
				}
				else
				{
					//Unteralben, die dem Benutzer nicht gehoeren nicht loeschen
					$objAlbUpd = $this->Database->prepare('UPDATE tl_gallery_creator_albums SET pid=? WHERE id=?')->execute('0', $idDelAlbum);
				}
			}
		}
		$this->redirect('contao/main.php?do=gallery_creator');
	}


	/**
	 * onload-callback
	 * checks availability of the upload-folder
	 */
	public function onloadCbCheckFolderSettings()
	{
		//Uploadverzeichnis
		$folder = new Folder($this->relImgDir);
		if (!is_writable(substr($this->imgDir, 0, -1)))
		{
			$this->Files->chmod($this->relImgDir, 0777);
			if (!is_writable(substr($this->imgDir, 0, -1)))
			{
				$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['dirNotWriteable'], $this->relImgDir);
			}
		}
	}


	/**
	 * onload-callback
	 * checks the existence of the selected album-preview-thumbnail
	 */
	public function onloadCbCheckRefThumb()
	{
		$objAlb = $this->Database->execute('SELECT id, thumb FROM tl_gallery_creator_albums');

		while ($objAlb->next())
		{
			$objPreviewThumb = $this->Database->prepare('SELECT name,path FROM tl_gallery_creator_pictures WHERE id=?')->execute($objAlb->thumb);

			if (!is_file(TL_ROOT . '/' . $objPreviewThumb->path . '/' . $objPreviewThumb->name))
			{
				$objPic = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE pid=?')->limit(1)->execute($objAlb->id);
				if ($objPic->id && $objPic->numRows)
				{
					$objAlb2 = $this->Database->prepare('UPDATE tl_gallery_creator_albums SET thumb=? WHERE id=?')->execute($objPic->id, $objAlb->id);
				}
				else
				{
					$objAlb2 = $this->Database->prepare('UPDATE tl_gallery_creator_albums SET thumb=? WHERE id=?')->execute('', $objAlb->id);
				}
			}
		}
	}


	/**
	 * onload-callback
	 * initiate the fileupload
	 */
	public function onloadCbFileupload()
	{

		if ($this->Input->get('mode') != 'fileupload' || $_FILES['file']['tmp_name'] == "")
			return;

		//Wichtig!!!! aus der requestUri stoerende, nicht mehr benoetigte Parameter entfernen (z.B. PHPSESSID)
		$pos = strpos($this->Environment->requestUri, '&mode');
		$this->Environment->requestUri = substr($this->Environment->requestUri, 0, $pos);

		//JumpLoader uebermittelt den original-Dateinamen ueber $_POST['fileName']
		//dateinamen romanisieren
		$strFilename = $_POST['fileName'];
		$strFilename = $this->createUniqueFilename($strFilename);

		//unerlaubte Dateitypen abfangen
		$pathinfo = pathinfo($strFilename);
		$uploadTypes = trimsplit(',', strtolower($GLOBALS['TL_CONFIG']['uploadTypes']));
		if (!in_array(strtolower($pathinfo['extension']), $uploadTypes))
		{
			//Fehlermeldung anzeigen
			$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $pathinfo['extension']);
			$this->log('File type "' . $pathinfo['extension'] . '" is not allowed to be uploaded (' . $strFilename . ')', __METHOD__, TL_ERROR);
			return;
		}

		//zu grosse und zu kleine, defekte Dateien abfangen
		if ($GLOBALS['TL_CONFIG']['maxFileSize'] <= $_FILES['file']['size'] || $_FILES['file']['size'] < 1000)
		{
			//Fehlermeldung anzeigen
			$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $GLOBALS['TL_CONFIG']['maxFileSize']);
			$this->log('Maximum upload-filesize exceeded. Filename: ' . $strFilename . ' size: ' . $_FILES['file']['size'], __METHOD__, TL_ERROR);
			return;
		}

		//Album-Unterverzeichnis erstellen, falls es noch nicht existiert!
		$objAlb = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')->execute($this->Input->get('id'));
		$folder = new Folder($GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/' . $objAlb->alias);
		//chmod-settings
		$this->Files->chmod($this->relImgDir . $objAlb->alias, 0777);
		if (!is_writable($this->imgDir . $objAlb->alias))
		{
			$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['dirNotWriteable'], $this->relImgDir . $objAlb->alias);
		}

		//aus irgend einem Grund funktioniert $this->Files->move_uploaded_file nicht auf allen Systemen
		if (move_uploaded_file($_FILES['file']['tmp_name'], $this->imgDir . $objAlb->alias . '/' . $strFilename))
		{
			$this->createNewImage($strFilename, $this->relImgDir . $objAlb->alias);
			return;
		}

		//moveupload
		if ($this->Files->move_uploaded_file($_FILES['file']['tmp_name'], $this->relImgDir . $objAlb->alias . '/' . $strFilename))
		{
			//chmod
			$this->Files->chmod($this->relImgDir . $objAlb->alias . '/' . $strFilename, 0644);
			$this->createNewImage($strFilename, $this->relImgDir . $objAlb->alias);
			return;
		}
		else
		{
			//Upload-Fehler
			$this->log('Unable to upload Files from tmpdir to the upload-dir.', __METHOD__, TL_ERROR);
			$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['uploadError'], print_r($_FILES['file'], true) . $_FILES['file']['name']);
			return;
		}
	}


	/**
	 * onload-callback
	 * Gibt ein array zurueck mit der id der Inhaltselemente des typs 'gallery_creator' in denen das Album mit id "$this->Input->get('id')" gezeigt wird.
	 * @return array
	 */
	public function onloadCbGetGcCteElements()
	{
		//echo $varValue;
		if ($this->Input->get('act') != '')
			return;
		$objDb = $this->Database->execute('SELECT id FROM tl_gallery_creator_albums');
		$arrAlbumIds = array();
		while ($objDb->next())
		{
			$arrAlbumIds[] = $objDb->id;
		}
		foreach ($arrAlbumIds as $albumId)
		{
			$arrGcArticles = array();
			$objDb = $this->Database->prepare('SELECT id, gc_publish_albums FROM tl_content WHERE type=?')->execute('gallery_creator');
			while ($objDb->next())
			{
				$arrPublAlbums = $objDb->gc_publish_albums != "" ? unserialize($objDb->gc_publish_albums) : array();
				if (in_array($albumId, $arrPublAlbums))
				{
					$arrGcArticles[] = $objDb->id;
				}
			}

			//Update tl_gallery_creator_albums.gc_articles
			$arrGcArticles = count($arrGcArticles) > 0 ? serialize($arrGcArticles) : '';
			$objDbUpdate = $this->Database->prepare('UPDATE tl_gallery_creator_albums SET displ_alb_in_this_ce=? WHERE id=?')->execute($arrGcArticles, $albumId);
		}
	}


	/**
	 * onload-callback
	 * Bilder aus Verzeichnis auf dem Server in Album einlesen
	 */
	public function onloadCbImportImages()
	{
		//Sprachdatei laden
		$this->loadLanguageFile('tl_content');
		if ($this->Input->get('mode') != 'import_images')
			return;
		if (!$this->Input->post('FORM_SUBMIT'))
			return;

		$this->Database->prepare('UPDATE tl_gallery_creator_albums SET preserve_filename=? WHERE id=?')->execute($this->Input->post('preserve_filename'), $this->Input->get('id'));

		$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['preserve_filename']['eval']['submitOnChange'] = false;

		$singleSRC = $this->Input->post('singleSRC');
		$images = array();
		if (!is_array($singleSRC))
			return $images;
		// Get all images
		foreach ($singleSRC as $file)
		{
			if (isset($images[$file]) || !file_exists(TL_ROOT . '/' . $file))
			{
				$_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['link_to_not_existing_file'], $file);
				continue;
			}

			// Single files
			if (is_file(TL_ROOT . '/' . $file))
			{
				$objFile = new File($file);

				if ($objFile->isGdImage)
				{
					$images[$file] = array(
						'path' => str_replace(TL_ROOT . '/', '', $objFile->dirname),
						'name' => $objFile->basename
					);
				}
				continue;
			}

			$subfiles = scan(TL_ROOT . '/' . $file);

			// Folders
			foreach ($subfiles as $subfile)
			{
				if (is_dir(TL_ROOT . '/' . $file . '/' . $subfile))
				{
					continue;
				}

				$objFile = new File($file . '/' . $subfile);
				if ($objFile->isGdImage)
				{

					$images[$file . '/' . $subfile] = array(
						'path' => str_replace(TL_ROOT . '/', '', $objFile->dirname),
						'name' => $objFile->basename
					);
				}
			}
		}
		//Falls vom Bild eine Kopie im gallery_creator_albums-Verzeichnis erstellt werden soll...
		if ($GLOBALS['TL_CONFIG']['gc_album_import_copy_files'])
		{
			$objAlb = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')->execute($this->Input->get('id'));
			$newPath = 'tl_files/gallery_creator_albums/' . $objAlb->alias;
			$folder = new Folder($newPath . '/');
			$this->Files->chmod($newPath, 0777);
		}

		foreach ($images as $image)
		{
			if (!$GLOBALS['TL_CONFIG']['gc_album_import_copy_files'])
				$newPath = $image['path'];
			$strFilename = $image['name'];
			$intExternalFile = 1;
			if ($GLOBALS['TL_CONFIG']['gc_album_import_copy_files'])
			{
				$intExternalFile = "";
				$strFilename = $this->createUniqueFilename($image['name']);
				$this->Files->copy($image['path'] . '/' . $image['name'], $newPath . '/' . $strFilename);
			}
			$this->createNewImage($strFilename, $newPath, $intExternalFile);
		}
		$this->redirect('contao/main.php?do=gallery_creator&table=tl_gallery_creator_pictures&id=' . $this->Input->get('id'));
	}


	/**
	 * onload-callback
	 * revise table
	 */
	public function onloadCbReviseTable()
	{
		if (is_dir(TL_ROOT . "/" . $GLOBALS['TL_CONFIG']['uploadPath'] . '/GalleryCreatorAlbums') && !is_dir($this->imgDir))
		{
			die("Please rename '" . $GLOBALS['TL_CONFIG']['uploadPath'] . "/GalleryCreatorAlbums'  to '" . $this->relImgDir . "'!");
		}

		//Sorgt dafuer, dass der zur id gehoerende Name immer aktuell ist
		$db = $this->Database->execute('SELECT id, owner, alias FROM tl_gallery_creator_albums');
		while ($db->next())
		{
			//Besitzt das Album ein Verzeichnis
			$folder = new Folder($GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/' . $db->alias);
			//chmod-settings
			$this->Files->chmod($this->relImgDir . $db->alias, 0777);

			$db_2 = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($db->owner);
			$Besitzer = $db_2->name;
			if ($db_2->name == '')
				$Besitzer = "Couldn't find username with ID " . $db->owner . " in the db.";

			$db_3 = $this->Database->prepare('UPDATE tl_gallery_creator_albums SET owners_name=? WHERE id=?')->execute($Besitzer, $db->id);
		}

		//auf gueltige pid ueberpruefen
		$objAlb = $this->Database->prepare('SELECT id, pid FROM tl_gallery_creator_albums WHERE pid!=?')->execute('0');
		while ($objAlb->next())
		{
			$objParentAlb = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE id=?')->execute($objAlb->pid);
			if ($objParentAlb->numRows < 1)
			{
				$objAlbUpdatePid = $this->Database->prepare('UPDATE tl_gallery_creator_albums SET pid=? WHERE id=?')->execute('0', $objAlb->id);
			}
		}

		$folder = new Folder($this->relImgDir);
		if (!$folder->isEmpty())
		{
			//Datensaetze ohne Bildnamen loeschen
			$objImg = $this->Database->prepare('DELETE FROM tl_gallery_creator_pictures WHERE name=?')->execute('');

			//pruefen, ob zu jedem Datensatz auch eine Bilddatei existiert.
			$objImg = $this->Database->execute('SELECT id, pid, name, path FROM tl_gallery_creator_pictures');
			while ($objImg->next())
			{
				if (!is_file(TL_ROOT . '/' . $objImg->path . '/' . $objImg->name))
				{
					$error = true;
					//Datensatz wurde geloescht, weil keine Bilddatei dazu vorhanden ist
					$objImgDel = $this->Database->prepare('DELETE FROM tl_gallery_creator_pictures WHERE id=?')->execute($objImg->id);
				}
			}
		}

		// Sorgt dafuer, dass in tl_content im Feld gc_publish_albums keine verwaisten AlbumId's vorhanden sind
		// Prueft, ob es sich bei den gewaehlten Alben noch um existierende Alben handelt
		$objCont = $this->Database->prepare('SELECT id, gc_publish_albums FROM tl_content WHERE type=?')->execute('gallery_creator');
		while ($objCont->next())
		{
			$newArr = array();
			$arrAlbums = unserialize($objCont->gc_publish_albums);
			if (is_array($arrAlbums))
			{
				foreach ($arrAlbums as $AlbumID)
				{
					$objAlb = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE id=?')->limit('1')->execute($AlbumID);
					if ($objAlb->next())
					{
						$newArr[] = $AlbumID;
					}
				}

			}
			$objContUpd = $this->Database->prepare('UPDATE tl_content SET gc_publish_albums=? WHERE id=?')->execute(serialize($newArr), $objCont->id);
		}
	}


	/**
	 * onload-callback
	 * create the palette
	 */
	public function onloadCbSetUpPalettes()
	{
		//global_operations nur fuer Admins
		if (!$this->User->isAdmin)
			unset($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['all']);

		//Zwecks db-Integritaet erhalten alle user fuer die folgenden Felder nur Leserechte
		$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['id']['eval']['style'] = '" readonly="readonly';
		$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['owners_name']['eval']['style'] = '" readonly="readonly';

		//Palette fuer JumpLoader setzen
		if ($this->Input->get('mode') == 'fileupload')
		{
			$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['fileupload'];
			return;
		}

		//Palette fuer import_images setzen
		if ($this->Input->get('mode') == 'import_images')
		{
			$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['import_images'];
			$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['preserve_filename']['eval']['submitOnChange'] = false;
			return;
		}

		//Palette fuer admins
		if ($this->User->isAdmin)
		{
			$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['owner']['eval']['doNotShow'] = false;
			$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['protected']['eval']['doNotShow'] = false;
			$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['groups']['eval']['doNotShow'] = false;
			return;
		}

		$objAlb = $this->Database->prepare('SELECT id, owner FROM tl_gallery_creator_albums WHERE id=?')->execute($this->Input->get('id'));
		//Nur admins und die Ersteller eines Albums haben Schreibzugriff auf die folgenden Felder
		$this->checkUserRole();
		if ($objAlb->owner != $this->User->id && true == $this->restrictedUser)
		{
			$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['restricted_user'];
		}
	}


	/**
	 * displ_alb_in_this_ce  - options_callback
	 */
	protected function optionsCbDisplAlbInThisContentElements()
	{
		$objDb = $this->Database->prepare('SELECT tl_content.id AS id, tl_article.title as title, tl_page.title as pagename FROM tl_content, tl_article, tl_page  WHERE tl_article.id=tl_content.pid AND tl_page.id=tl_article.pid AND tl_content.type=?')->execute('gallery_creator');
		$opt = array();
		while ($objDb->next())
		{
			$opt[$objDb->id] = sprintf($GLOBALS['TL_LANG']['tl_gallery_creator_albums']['reference']['displ_alb_in_this_ce'], $objDb->id, $objDb->title, $objDb->pagename);
		}
		return $opt;
	}


	/**
	 * Options Callback fuer das Auswaehlen des Vorschaubildes
	 * @return array
	 */
	public function optionsCbThumb()
	{
		//Gibt alle Bildnamen eines Albums zurueck
		$objDb = $this->Database->prepare('SELECT id,name FROM tl_gallery_creator_pictures WHERE pid=? ORDER BY id')->execute($this->Input->get('id'));
		$arrThumbId = array();
		while ($objDb->next())
		{
			$arrThumbId[$objDb->id] = $objDb->name;
		}
		$arrSubalbums = $this->GcHelpers->getAllSubalbums($this->Input->get('id'));

		if (count($arrSubalbums))
		{
			foreach ($arrSubalbums as $albId)
			{
				$objPic = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_pictures WHERE pid=? ORDER BY id')->execute($albId);
				if ($objPic->numRows)
				{
					$objAlbName = $this->Database->prepare('SELECT name, alias FROM tl_gallery_creator_albums WHERE id=?')->execute($albId);
					$arrThumbId["Subalbum: " . $objAlbName->alias] = "--- Subalbum: " . $objAlbName->name . " ---";
					while ($objPic->next())
					{
						$arrThumbId[$objPic->id] = $objPic->name;
					}
				}
			}
		}
		return $arrThumbId;
	}


	/**
	 * displ_alb_in_this_ce  - save_callback
	 */
	public function saveCbDisplAlbInThisContentElements($varValue, DataContainer $dc)
	{
		$albumId = $dc->id;
		$arrGcArticles = $varValue == "" ? array() : unserialize($varValue);
		$objDb = $this->Database->prepare('SELECT id, gc_publish_albums FROM tl_content WHERE type=?')->execute('gallery_creator');
		$arrContentElements = array();
		while ($objDb->next())
		{
			$arrContentElements[] = $objDb->id;
		}

		//Feld gc_publish_albums in jedem Datensatz von tl_content aendern, in denen das gallery_creator - Inhaltselement zur Anwendung kommt.
		foreach ($arrContentElements as $currentCteId)
		{
			$objSelect = $this->Database->prepare('SELECT gc_publish_albums FROM tl_content WHERE id=?')->executeUncached($currentCteId);
			//!important!!! ->executeUncached
			$arrPublAlbums = is_array($objSelect->gc_publish_albums) ? $objSelect->gc_publish_albums : unserialize($objSelect->gc_publish_albums);
			$arrPublAlbums = count($arrPublAlbums) > 0 ? $arrPublAlbums : array();
			$this->log('First: AlbumId: ' . $dc->id . ' ContentID: ' . $currentCteId . ' gc_publish_albums: ' . serialize($arrPublAlbums), __METHOD__, TL_ERROR);

			if (in_array($currentCteId, $arrGcArticles))
			{
				$arrPublAlbums[] = $albumId;
			}
			else
			{
				$arrPublAlbums = array_flip($arrPublAlbums);
				unset($arrPublAlbums[$albumId]);
				$arrPublAlbums = array_flip($arrPublAlbums);
			}
			$arrPublAlbums = array_unique($arrPublAlbums);
			$objDbUpd = $this->Database->prepare('UPDATE tl_content SET gc_publish_albums=? WHERE id=?')->executeUncached(serialize($arrPublAlbums), $currentCteId);
			$this->log('A new version of record "tl_content.id=' . $currentCteId . '" has been created', __METHOD__, GENERAL);
		}
		return $varValue;
	}


	/**
	 * generiert aus dem Albumnamen ein alias, wenn noch nicht vorhanden
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function saveCbGenerateAlias($varValue, DataContainer $dc)
	{
		$this->import('String');
		$varValue = standardize($varValue);
		// Wenn kein Alias vorhanden, wird er aus dem Albumnamen generiert
		if (!strlen($varValue))
		{
			$varValue = standardize($dc->activeRecord->name);
		}

		//alias auf 50 Zeichen beschraenken
		$varValue = substr($varValue, 0, 43);

		//ungueltige Zeichen entfernen
		$varValue = preg_replace("/[^a-z0-9\_\-]/", "", $varValue);

		if (!strlen($varValue))
			$varValue = md5(microtime() . $objAlb->name);
		//testen, ob Alias schon existiert
		$objAlias = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE id!=? AND alias=?')->execute($dc->activeRecord->id, $varValue);
		if ($objAlias->numRows)
		{
			$varValue .= '_' . substr(md5(microtime() . rand(100, 10000)), 0, 6);
		}
		//Wenn alias geaendert wurde, muessen das Verzeichnis und bei jedem betroffenem Bild die Pfade angepasst werden
		$objAlbum = $this->Database->prepare('SELECT alias FROM tl_gallery_creator_albums WHERE id=?')->execute($dc->activeRecord->id);
		if ($objAlbum->alias != $varValue)
		{
			if (is_dir(TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/' . $objAlbum->alias))
			{
				//Verzeichnis umbenennen
				$this->Files->chmod($GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/' . $objAlbum->alias, 0777);
				$this->Files->rename($GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/' . $objAlbum->alias, $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/' . $varValue);
				$objPic = $this->Database->prepare('SELECT id,path FROM tl_gallery_creator_pictures WHERE pid=? AND externalFile=?')->execute($dc->activeRecord->id, "");
				while ($objPic->next())
				{
					//Pfade in db anpassen
					$newPath = str_replace('gallery_creator_albums/' . $objAlbum->alias, 'gallery_creator_albums/' . $varValue, $objPic->path);
					$this->Database->prepare('UPDATE tl_gallery_creator_pictures SET path=? WHERE id=?')->execute($newPath, $objPic->id);
				}
			}
		}
		return $varValue;
	}


	/**
	 * save_callback fuer das Einstellen der Bildaufloesung vor dem Uploadvorgang
	 * @return string
	 */
	public function saveCbSaveImageQuality($value)
	{
		$db = $this->Database->prepare('UPDATE tl_user SET gc_img_quality=? WHERE id=?')->execute($value, $this->User->id);
	}


	/**
	 * save_callback fuer das Einstellen der Bildaufloesung vor dem Uploadvorgang
	 * @return string
	 */
	public function saveCbSaveImageResolution($value)
	{
		$db = $this->Database->prepare('UPDATE tl_user SET gc_img_resolution=? WHERE id=?')->execute($value, $this->User->id);
	}


}
?>