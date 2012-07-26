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

$GLOBALS['TL_DCA']['tl_gallery_creator_pictures'] = array(
	// Config
	'config' => array(
		'ptable' => 'tl_gallery_creator_albums',
		'dataContainer' => 'Table',
		'onload_callback' => array(
			array(
				'tl_gallery_creator_pictures',
				'onloadCbCheckPermission'
			),
			array(
				'tl_gallery_creator_pictures',
				'onloadCbSetUpPalettes'
			),
			array(
				'tl_gallery_creator_pictures',
				'onloadCbCheckRefThumb'
			)
		),

		'ondelete_callback' => array( array(
				'tl_gallery_creator_pictures',
				'ondeleteCb'
			)),
	),

	//list
	'list' => array(
		'sorting' => array(
			'flag' => 1,
			'mode' => 4,
			'disableGrouping' => true,
			'headerFields' => array(
				'id',
				'date',
				'owners_name',
				'name',
				'comment',
				'thumb'
			),
			'panelLayout' => 'limit',
			'fields' => array('sorting ASC'),
			'child_record_callback' => array(
				'tl_gallery_creator_pictures',
				'childRecordCb'
			),
		),

		'label' => array(
			//
		),

		'global_operations' => array(
			'jumpLoader' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['jumpLoader'],
				'href' => 'act=edit&table=tl_gallery_creator_albums&mode=fileupload',
				'class' => 'led_new',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			),

			'all' => array(
				'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href' => 'act=select',
				'class' => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),

		'operations' => array(
			'edit' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['edit'],
				'href' => 'act=edit',
				'icon' => 'edit.gif',
				'button_callback' => array(
					'tl_gallery_creator_pictures',
					'buttonCbEditImage'
				)
			),

			'delete' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['delete'],
				'href' => 'act=delete',
				'icon' => 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
				'button_callback' => array(
					'tl_gallery_creator_pictures',
					'buttonCbDeletePicture'
				)
			),

			'cut' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['cut'],
				'href' => 'act=paste&mode=cut',
				'icon' => 'cut.gif',
				'attributes' => 'onclick="Backend.getScrollOffset();"',
				'button_callback' => array(
					'tl_gallery_creator_pictures',
					'buttonCbCutImage'
				)
			),

			'paste' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['paste'],
				'href' => 'act=cut&mode=1',
				'icon' => 'pasteafter.gif',
				'attributes' => 'class="blink" onclick="Backend.getScrollOffset();"',
				'button_callback' => array(
					'tl_gallery_creator_pictures',
					'buttonCbPasteImage'
				)
			),

			'imagerotate' => array(
				'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['imagerotate'],
				'href' => 'mode=imagerotate',
				'icon' => 'system/modules/gallery_creator/html/rotate.png',
				'attributes' => 'onclick="Backend.getScrollOffset();"',
				'button_callback' => array(
					'tl_gallery_creator_pictures',
					'buttonCbRotateImage'
				)
			)
		)
	),

	// Palettes
	'palettes' => array(
		'__selector__' => array('addCustomThumb'),
		'default' => 'published,owner,date,image_info,addCustomThumb,title,comment,picture;{media_integration:hide},socialMediaSRC,localMediaSRC;{id/class:hide},cssID',
		'restricted_user' => 'image_info,picture'
	),

	// Subpalettes
	'subpalettes' => array('addCustomThumb' => 'customThumb'),

	// Fields
	'fields' => array(
		'published' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['published'],
			'inputType' => 'checkbox',
			'eval' => array(
				'isBoolean' => true,
				'submitOnChange' => true,
				'tl_class' => 'long'
			)
		),

		'image_info' => array(
			'input_field_callback' => array(
				'tl_gallery_creator_pictures',
				'inputFieldCbGenerateImageInformation'
			),
			'eval' => array(
				'tl_class' => 'clr',
				'doNotShow' => true
			)
		),

		'title' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['title'],
			'exclude' => true,
			'inputType' => 'text',
			'eval' => array(
				'allowHtml' => false,
				'decodeEntities' => true,
				'rgxp' => 'alnum'
			)
		),

		'comment' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['comment'],
			'inputType' => 'textarea',
			'exclude' => true,
			'cols' => 20,
			'rows' => 6,
			'eval' => array(
				'decodeEntities' => true,
				'tl_class' => 'w50 ',
				'style' => 'margin-right:-15px; width:90%; height:150px;'
			)
		),

		'picture' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['picture'],
			'input_field_callback' => array(
				'tl_gallery_creator_pictures',
				'inputFieldCbGenerateImage'
			),
			'eval' => array('doNotShow' => true)
		),

		'date' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['date'],
			'inputType' => 'text',
			'eval' => array(
				'mandatory' => true,
				'maxlength' => 10,
				'datepicker' => $this->getDatePickerString(),
				'submitOnChange' => false,
				'rgxp' => 'date',
				'tl_class' => 'm12 w50 wizard ',
				'submitOnChange' => false
			)
		),

		'addCustomThumb' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['addCustomThumb'],
			'exclude' => true,
			'inputType' => 'checkbox',
			'eval' => array(
				'submitOnChange' => true,
				'doNotShow' => true
			)
		),

		'customThumb' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['customThumb'],
			//'exclude'               => true,
			'inputType' => 'fileTree',
			'eval' => array(
				'fieldType' => 'radio',
				'files' => true,
				'filesOnly' => true,
				'extensions' => 'jpeg,jpg,gif,png,bmp,tiff'
			)
		),

		'owner' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['owner'],
			'default' => $this->User->id,
			'foreignKey' => 'tl_user.name',
			'inputType' => 'select',
			'eval' => array(
				'includeBlankOption' => true,
				'blankOptionLabel' => 'noName',
				'doNotShow' => true,
				'nospace' => true,
				'tl_class' => 'clr m12 w50'
			)
		),
		'socialMediaSRC' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['socialMediaSRC'],
			'exclude' => true,
			'filter' => true,
			'inputType' => 'text',
			'eval' => array('tl_class' => 'clr')
		),
		'localMediaSRC' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['localMediaSRC'],
			'exclude' => true,
			'filter' => true,
			'inputType' => 'fileTree',
			'eval' => array(
				'files' => true,
				'filesOnly' => true,
				'fieldType' => 'radio'
			)
		),
		'cssID' => array(
			'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['cssID'],
			'exclude' => true,
			'inputType' => 'text',
			'eval' => array(
				'multiple' => true,
				'size' => 2,
				'tl_class' => 'w50 clr'
			)
		)
	)
);

/**
 * Class tl_gallery_creator_pictures
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Marko Cupic 2005-2010
 * @author     Marko Cupic
 */
class tl_gallery_creator_pictures extends Backend
{

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

	/*
	 * bool
	 * bei eingeschränkten Usern wird der Wert auf true gesetzt
	 */
	public $restrictedUser = false;

	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
		$this->import('Files');

		//absoluter Pfad zum Upload-Dir
		$this->imgDir = TL_ROOT . '/' . $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/';

		//relativer Pfad zum Upload-Dir fuer safe-mode-hack
		$this->relImgDir = $GLOBALS['TL_CONFIG']['uploadPath'] . '/gallery_creator_albums/';

		//Setzt bei jedem neuen Aufruf das Clipboard in die Anfangskonfiguration zurück
		if ($this->Input->get('act') != 'paste')
			$this->Session->set('CLIPBOARD', array());

		//parse Backend Template Hook registrieren
		$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array(
			'tl_gallery_creator_pictures',
			'myParseBackendTemplate'
		);

		switch ($this->Input->get('mode'))
		{

			case 'imagerotate' :
				$this->rotateJpgImage();
				$this->redirect('contao/main.php?do=gallery_creator&table=tl_gallery_creator_pictures&id=' . $this->Input->get('id'));
				break;
			default :
				break;
		}//end switch

		switch ($this->Input->get('act'))
		{
			case 'create' :
				//Neue Bilder können ausschliesslich über einen Bildupload realisiert werden
				$this->Redirect('contao/main.php?do=gallery_creator&table=tl_gallery_creator_pictures&id=' . $this->Input->get('pid'));
				break;

			case 'select' :
				if (!$this->User->isAdmin)
				{
					$GLOBALS['TL_DCA']['tl_gallery_creator_pictures']['list']['sorting']['filter'] = array( array(
							'owner=?',
							$this->User->id
						));
				}

				break;

			case 'imagerotate' :
				$this->rotateJpgImage();
				break;

			default :
				break;
		} //end switch
	}


	/**
	 * Return the delete-image-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbDeletePicture($row, $href, $label, $title, $icon, $attributes)
	{
		$objImg = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_pictures WHERE id=?')->execute($row['id']);
		return ($this->User->isAdmin || $this->User->id == $objImg->owner || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) ? '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
	}


	/**
	 * Return the edit-image-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbEditImage($row, $href, $label, $title, $icon, $attributes)
	{

		$objImg = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_pictures WHERE id=?')->execute($row['id']);
		return ($this->User->isAdmin || $this->User->id == $objImg->owner || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) ? '<a href="' . $this->addToUrl($href . '&id=' . $row['id'], true) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ' : $this->generateImage(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';

	}


	/**
	 * Return the cut-image-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbCutImage($row, $href, $label, $title, $icon, $attributes)
	{
		return '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
	}


	/**
	 * Return the paste-image-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbPasteImage($row, $href, $label, $title, $icon, $attributes)
	{
		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']), 'class="blink"');

		if ($this->Input->get('act') == 'paste' && $this->Input->get('mode') == 'cut')
		{
			if ($row['id'] == $this->Input->get('id'))
			{
				return;
			}
			return '<a href="' . $this->addToUrl($href . '&id=' . $this->Input->get('id') . '&pid=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $imagePasteAfter . '</a> ';
		}
	}


	/**
	 * Return the rotate-image-button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function buttonCbRotateImage($row, $href, $label, $title, $icon, $attributes)
	{
		return '<a href="' . $this->addToUrl($href . '&imgid=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ';
	}


	/**
	 * child-record-callback
	 * @param array
	 * @return string
	 */
	public function childRecordCb($arrRow)
	{
		$time = time();
		$key = ($arrRow['published'] == '1') ? 'published' : 'unpublished';
		$date = $this->parseDate($GLOBALS['TL_CONFIG']['datimFormat'], $arrRow['date']);
		//nächste Zeile nötig, da be_bredcrumb sonst bei "mehrere bearbeiten" hier einen Fehler produziert
		if (!is_file(TL_ROOT . "/" . $arrRow['path'] . '/' . $arrRow['name']))
		{
			return "";
		}

		$imageSrc = $arrRow['path'] . '/' . $arrRow['name'];
		$objFile = new File($imageSrc);
		if ($objFile->isGdImage)
		{
			$hasMovie = "";
			$src = $imageSrc;

			//if dataset contains a link to movie file...
			$src = trim($arrRow['socialMediaSRC']) != "" ? trim($arrRow['socialMediaSRC']) : $src;
			$src = trim($arrRow['localMediaSRC']) != "" ? trim($arrRow['localMediaSRC']) : $src;
			if (trim($arrRow['socialMediaSRC']) != "" or trim($arrRow['localMediaSRC']) != "")
			{
				$type = trim($arrRow['localMediaSRC']) != "" ? 'embeded local-media: ' : 'embeded social media: ';
				$hasMovie = '<div class="block"><img src="system/modules/gallery_creator/html/film.png"> ' . $type . '<a href="' . $src . '" data-lightbox="gc_album_' . $this->Input->get('id') . '">' . $src . '</a></div>';
			}
			//-->

			return '

<div class="cte_type ' . $key . '"><strong>' . $arrRow['headline'] . '</strong> - ' . $arrRow['name'] . ' [' . $objFile->width . ' x ' . $objFile->height . ' px, ' . $this->getReadableSize($objFile->filesize) . ']</div>
' . $hasMovie . '
<div class="block"><a href="' . $src . '" data-lightbox="gc_album_' . $this->Input->get('id') . '"><img src="' . $this->getImage($imageSrc, "100", "", "proportional") . '"></a></div>
<div class="limit_height' . (!$GLOBALS['TL_CONFIG']['doNotCollapse'] ? ' h64' : '') . ' block">
' . (($arrRow['comment'] != '') ? $arrRow['comment'] : '') . '
</div>' . "\n";
		}
	}


	/**
	 * $imgSrc - GD image handle of source image
	 * $angle - angle of rotation. Needs to be positive integer
	 * angle shall be 0,90,180,270, but if you give other it
	 * will be rouned to nearest right angle (i.e. 52->90 degs,
	 * 96->90 degs)
	 * returns GD image handle of rotated image.
	 * http://www.php.net/manual/de/function.imagerotate.php
	 * thanks to Borszczuk
	 */
	private function imageRotateRightAngle($imgSrc, $angle)
	{
		// ensuring we got really RightAngle (if not we choose the closest one)
		$angle = min(((int)(($angle + 45) / 90) * 90), 270);

		// no need to fight
		if ($angle == 0)
			return ($imgSrc);

		// dimenstion of source image
		$srcX = imagesx($imgSrc);
		$srcY = imagesy($imgSrc);
		switch( $angle )
		{
			case 90 :
				$imgDest = imagecreatetruecolor($srcY, $srcX);
				for ($x = 0; $x < $srcX; $x++)
					for ($y = 0; $y < $srcY; $y++)
						imagecopy($imgDest, $imgSrc, $srcY - $y - 1, $x, $x, $y, 1, 1);
				break;

			case 180 :
				$imgDest = ImageFlip($imgSrc, IMAGE_FLIP_BOTH);
				break;

			case 270 :
				$imgDest = imagecreatetruecolor($srcY, $srcX);
				for ($x = 0; $x < $srcX; $x++)
					for ($y = 0; $y < $srcY; $y++)
						imagecopy($imgDest, $imgSrc, $y, $srcX - $x - 1, $x, $y, 1, 1);
				break;
		}
		return ($imgDest);
	}


	/**
	 * input-field-callback generate image
	 * Returns the html-img-tag
	 * @return string
	 */
	public function inputFieldCbGenerateImage()
	{
		$objImg = $this->Database->prepare('SELECT path,name,pid FROM tl_gallery_creator_pictures WHERE id=?')->limit(1)->execute($this->Input->get('id'));
		$src = $objImg->path . '/' . $objImg->name;
		return '

<div class="w50 easyExclude easyExcludeFN_picture" style="height:200px;">
	<h3><label for="ctrl_picture">' . $objImg->name . '</label></h3>
	<a href="' . $src . '" data-lightbox="gc_image_' . $this->Input->get('id') . '"><img src="' . $this->getImage($src, '180', '180', 'crop') . '"></a>
</div>
		';
	}


	/**
	 * input-field-callback generate image information
	 * Returns the html-table-tag containing some picture informations
	 * @return string
	 */
	public function inputFieldCbGenerateImageInformation()
	{
		$objImg = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')->execute($this->Input->get('id'));

		$objUser = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objImg->owner);
		$output = '
			<div class="album_infos">
			<br /><br />
			<table cellpadding="0" cellspacing="0" width="100%" summary="">
				<tr class="odd">
					<td style="width:20%"><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['id'][0] . ': </strong></td>
					<td>' . $objImg->id . '</td>
				</tr>

				<tr>
					<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['path'][0] . ': </strong></td>
					<td>' . $objImg->path . '</td>
				</tr>

				<tr class="odd">
					<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['filename'][0] . ': </strong></td>
					<td>' . $objImg->name . '</td>
				</tr>';

		if ($this->restrictedUser)
		{
			$output .= '
					<tr>
					<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['date'][0] . ': </strong></td>
					<td>' . $this->parseDate("Y-m-d", $objImg->date) . '</td>
					</tr>
					
					<tr class="odd">
						<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['owner'][0] . ': </strong></td>
						<td>' . ($objUser->name == "" ? "Couldn't find username with ID " . $objImg->owner . " in the db." : $objUser->name) . '</td>
					</tr>

					<tr>
					<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['title'][0] . ': </strong></td>
					<td>' . $objImg->title . '</td>
					</tr>

					<tr class="odd">
					<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['video_href_social'][0] . ': </strong></td>
					<td>' . trim($objImg->video_href_social) != "" ? trim($objImg->video_href_social) : "-" . '</td>
					</tr>
					
					<tr>
					<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['video_id'][0] . ': </strong></td>
					<td>' . trim($objImg->video_href_local) != "" ? trim($objImg->video_href_local) : "-" . '</td>
					</tr>';
		}

		$output .= '
			</table>
			</div>
		';
		return $output;
	}


	/**
	 * Parse Backend Template Hook
	 * @param string
	 * @param string
	 * @return string
	 */
	public function myParseBackendTemplate($strContent, $strTemplate)
	{
		if ($this->Input->get('table') == 'tl_gallery_creator_pictures')
		{
			//da alle neuen Bilder (neue Datensaetze) nur über fileupload oder importImages realisiert werden, ist der "Create-Button" obsolet
			//entfernt den Create-Button aus den den global operations
			$strContent = preg_replace('/<a href(.*?)tl_gallery_creator_pictures(.*?)act=create(.*?)<\/a>(.*?)::/i', "", $strContent);

			//Bei einigen Browsern überragt die textarea den unteren Seitenrand, deshalb eine weitere leere clearing-box
			$strContent = str_replace('</fieldset>', '<div class="clr" style="clear:both"><p> </p><!-- clearing Box --></div></fieldset>', $strContent);
		}

		if ($this->Input->get('table') == 'tl_gallery_creator_pictures' && $this->Input->get('act') == 'edit')
		{
			//saveNcreate button-entfernen
			$strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
			//saveNclose button-entfernen
			//$strContent=preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/','',$strContent);
			//saveNback button-entfernen
			//$strContent=preg_replace('/<input type=\"submit\" name=\"saveNback\"((\r|\n|.)+?)>/','',$strContent);
		}
		return $strContent;
	}


	/**
	 * ondelete-callback
	 * prevents deleting images by unauthorised users
	 */
	public function ondeleteCb()
	{
		if ($this->Input->get('act') == 'deleteAll')
		{
			foreach ($_SESSION["BE_DATA"]["CURRENT"]["IDS"] as $id)
			{
				$objImg = $this->Database->prepare('SELECT id,path,name,owner FROM tl_gallery_creator_pictures WHERE id=?')->execute($id);
				if ($objImg->owner == $this->User->id || $this->User->isAdmin || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
				{
					if (is_file(TL_ROOT . '/' . $objImg->path . '/' . $objImg->name))
					{
						//Nur Bilder innerhalb des gallery_creator_albums und wenn sie nicht in einem anderen Datensatz noch Verwendung finden, werden vom Server geloescht
						$objDeleteItem = $this->Database->prepare('DELETE FROM tl_gallery_creator_pictures WHERE id=?')->execute($objImg->id);
						$objImgNumRows = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE path=? AND name=?')->execute($objImg->path, $objImg->name);

						if (strstr($objImg->path, "gallery_creator_albums") && $objImgNumRows->numRows < 1)
							$this->Files->delete($objImg->path . '/' . $objImg->name);
					}
				}
				else
				{
					$this->log('Datensatz mit ID ' . $id . ' wurde vom  Benutzer mit ID ' . $this->User->id . ' versucht aus tl_gallery_creator_pictures zu loeschen.', __METHOD__, TL_ERROR);
				}
			}
		}
		else
		{
			$objImg = $this->Database->prepare('SELECT id,owner,path,name FROM tl_gallery_creator_pictures WHERE id=?')->execute($this->Input->get('id'));

			if ($objImg->owner == $this->User->id || $this->User->isAdmin || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
			{
				//Nur Bilder innerhalb des gallery_creator_albums und wenn sie nicht in einem anderen Datensatz noch Verwendung finden, werden vom Server geloescht
				$objDeleteItem = $this->Database->prepare('DELETE FROM tl_gallery_creator_pictures WHERE id=?')->execute($objImg->id);
				$objImgNumRows = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE path=? AND name=?')->execute($objImg->path, $objImg->name);
				if (strstr($objImg->path, "gallery_creator_albums") && $objImgNumRows->numRows < 1)
					$this->Files->delete($objImg->path . '/' . $objImg->name);
			}
			if (!$this->User->isAdmin && $objImg->owner != $this->User->id)
			{
				$this->log('Datensatz mit ID ' . $this->Input->get('id') . ' wurde vom  Benutzer mit ID ' . $this->User->id . ' versucht aus tl_gallery_creator_pictures zu loeschen.', __METHOD__, TL_ERROR);
				$this->redirect('contao/main.php?do=error');
			}
		}
	}


	/**
	 * child-record-callback
	 * @param array
	 * @return string
	 */
	public function onloadCbCheckPermission()
	{
		//admin hat keine Einschraenkungen
		if ($this->User->isAdmin)
		{
			return;
		}

		//Nur der Ersteller hat keine Einschraenkungen

		if ($this->Input->get('act') == 'edit')

		{
			$objUser = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_pictures WHERE id=?')->execute($this->Input->get('id'));

			if (true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
			{
				return;
			}

			if ($objUser->owner != $this->User->id)
			{
				$this->restrictedUser = true;
			}
		}
	}


	/**
	 * onload-callback
	 * Kontrolliert, ob das in der db eingetragenen Vorschaubild im Bilderordner auch tatsaechlich existiert
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
	 * set up the palette
	 * prevents deleting images by unauthorised users
	 */
	public function onloadCbSetUpPalettes()
	{
		if ($this->restrictedUser)
		{
			$this->restrictedUser = true;
			$GLOBALS['TL_DCA']['tl_gallery_creator_pictures']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_pictures']['palettes']['restricted_user'];
		}

		if ($this->User->isAdmin)
		{
			$GLOBALS['TL_DCA']['tl_gallery_creator_pictures']['fields']['owner']['eval']['doNotShow'] = false;
		}
	}


	/**
	 * rotates image by 90°
	 */
	public function rotateJpgImage()
	{

		$objPic = $this->Database->prepare('SELECT path,name,pid FROM tl_gallery_creator_pictures WHERE id=?')->execute($this->Input->get('imgid'));

		// File and rotation
		$filename = '../' . $objPic->path . '/' . $objPic->name;
		$this->Files->chmod($objPic->path . '/' . $objPic->name, 0777);

		// Load
		$source = imagecreatefromjpeg($filename);
		// Rotate
		if (is_callable('imagerotate'))
		{
			//imagerotate ist nicht auf allen Systemen verfügbar
			$degrees = 270;
			$rotate = imagerotate($source, $degrees, 0);

		}
		else
		{
			$degrees = 90;
			$rotate = $this->imageRotateRightAngle($source, $degrees);
		}

		// Output
		imagejpeg($rotate, $filename);
		imagedestroy($source);
		$this->Files->chmod($objPic->path . '/' . $objPic->name, 0644);
	}


}
?>