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

$GLOBALS['TL_DCA']['tl_gallery_creator_pictures'] = array(
       // Config
       'config' => array(
              'ptable' => 'tl_gallery_creator_albums',
              'enableVersioning' => true,
              'dataContainer' => 'Table',
              'onload_callback' => array(
                     array(
                            'tl_gallery_creator_pictures',
                            'onloadCbCheckPermission'
                     ),
                     array(
                            'tl_gallery_creator_pictures',
                            'onloadCbSetUpPalettes'
                     )
              ),

              'ondelete_callback' => array(
                     array(
                            'tl_gallery_creator_pictures',
                            'ondeleteCb'
                     )
              ),
              'sql' => array(
                     'keys' => array(
                            'id' => 'primary',
                            'pid' => 'index'
                     )
              )
       ),
       //list
       'list' => array(
              'sorting' => array(
                     'mode' => 4,
                     'fields' => array('sorting'),
                     'panelLayout' => 'filter;search,limit',
                     'headerFields' => array(
                            'id',
                            'date',
                            'owners_name',
                            'name',
                            'comment',
                            'thumb'
                     ),
                     'child_record_callback' => array(
                            'tl_gallery_creator_pictures',
                            'childRecordCb'
                     ),
              ),

              'label' => array( //
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
                            'icon' => 'system/modules/gallery_creator/assets/images/rotate.png',
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

              'id' => array('sql' => "int(10) unsigned NOT NULL auto_increment"),

              'pid' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['pid'],
                     'foreignKey' => 'tl_gallery_creator_albums.alias',
                     'sql' => "int(10) unsigned NOT NULL default '0'",
                     'relation' => array(
                            'type' => 'belongsTo',
                            'load' => 'lazy'
                     ),
                     'eval' => array(
                            'doNotShow' => true
                     ),
              ),

              'sorting' => array('sql' => "int(10) unsigned NOT NULL default '0'"),

              'tstamp' => array('sql' => "int(10) unsigned NOT NULL default '0'"),

              'published' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['published'],
                     'inputType' => 'checkbox',
                     'filter' => true,
                     'eval' => array(
                            'isBoolean' => true,
                            'submitOnChange' => true,
                            'tl_class' => 'long'
                     ),
                     'sql' => "char(1) NOT NULL default '1'"
              ),

              'image_info' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['image_info'],
                     'input_field_callback' => array(
                            'tl_gallery_creator_pictures',
                            'inputFieldCbGenerateImageInformation'
                     ),
                     'eval' => array(
                            'tl_class' => 'clr',
                     )
              ),
              'title' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['title'],
                     'exclude' => true,
                     'inputType' => 'text',
                     'filter' => true,
                     'search' => true,
                     'eval' => array(
                            'allowHtml' => false,
                            'decodeEntities' => true,
                            'rgxp' => 'alnum'
                     ),
                     'sql' => "varchar(255) NOT NULL default ''"
              ),

              //filename
              'name' => array(
                     'sql' => "varchar(255) NOT NULL default ''",
                     'search' => true,
              ),

              //path
              'path' => array('sql' => "varchar(255) NOT NULL default ''"),

              //activate subpalette
              'externalFile' => array('sql' => "char(1) NOT NULL default ''"),

              'comment' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['comment'],
                     'inputType' => 'textarea',
                     'exclude' => true,
                     'filter' => true,
                     'search' => true,
                     'cols' => 20,
                     'rows' => 6,
                     'eval' => array(
                            'decodeEntities' => true,
                            'tl_class' => 'w50 ',
                            'style' => 'margin-right:-15px; width:90%; height:150px;'
                     ),
                     'sql' => "text NULL"
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
                     // when upload a new image, the image inherits the date of the parent album
                     'default' => time(),
                     'filter' => true,
                     'search' => true,
                     'eval' => array(
                            'mandatory' => true,
                            'maxlength' => 10,
                            'datepicker' => true,
                            'submitOnChange' => false,
                            'rgxp' => 'date',
                            'tl_class' => 'm12 w50 wizard ',
                            'submitOnChange' => false
                     ),
                     'sql' => "int(10) unsigned NOT NULL default '0'"
              ),

              'addCustomThumb' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['addCustomThumb'],
                     'exclude' => true,
                     'filter' => true,
                     'inputType' => 'checkbox',
                     'eval' => array(
                            'submitOnChange' => true,
                     ),
                     'sql' => "char(1) NOT NULL default ''"
              ),

              'customThumb' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['customThumb'],
                     'exclude' => true,
                     'inputType' => 'fileTree',
                     'eval' => array(
                            'fieldType' => 'radio',
                            'files' => true,
                            'filesOnly' => true,
                            'extensions' => 'jpeg,jpg,gif,png,bmp,tiff'
                     ),
                     'sql' => "int(10) unsigned NOT NULL default '0'"
              ),

              'owner' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['owner'],
                     'default' => $this->User->id,
                     'foreignKey' => 'tl_user.name',
                     'inputType' => 'select',
                     'filter' => true,
                     'search' => true,
                     'eval' => array(
                            'includeBlankOption' => true,
                            'blankOptionLabel' => 'noName',
                            'doNotShow' => true,
                            'nospace' => true,
                            'tl_class' => 'clr m12 w50'
                     ),
                     'sql' => "int(10) NOT NULL default '0'",
                     'relation' => array(
                            'type' => 'hasOne',
                            'load' => 'eager'
                     )
              ),

              'socialMediaSRC' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['socialMediaSRC'],
                     'exclude' => true,
                     'filter' => true,
                     'search' => true,
                     'inputType' => 'text',
                     'eval' => array('tl_class' => 'clr'),
                     'sql' => "varchar(255) NOT NULL default ''"
              ),
              'localMediaSRC' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['localMediaSRC'],
                     'exclude' => true,
                     'filter' => true,
                     'search' => true,
                     'inputType' => 'fileTree',
                     'eval' => array(
                            'files' => true,
                            'filesOnly' => true,
                            'fieldType' => 'radio'
                     ),
                     'sql' => "binary(16) NULL",
              ),
              'cssID' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['cssID'],
                     'exclude' => true,
                     'inputType' => 'text',
                     'eval' => array(
                            'multiple' => true,
                            'size' => 2,
                            'tl_class' => 'w50 clr'
                     ),
                     'sql' => "varchar(255) NOT NULL default ''"
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
        *  Pfad ab TL_ROOT ins Bildverzeichnis
        * @var string
        */
       public $uploadPath;

       /**
        * bool
        * bei eingeschränkten Usern wird der Wert auf true gesetzt
        */
       public $restrictedUser = false;


       public function __construct()
       {

              parent::__construct();

              $this->import('BackendUser', 'User');
              $this->import('Files');

              //relativer Pfad zum Upload-Dir fuer safe-mode-hack
              $this->uploadPath = GALLERY_CREATOR_UPLOAD_PATH;

              //parse Backend Template Hook registrieren
              $GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array(
                     'tl_gallery_creator_pictures',
                     'myParseBackendTemplate'
              );

              // set the referer when redirecting from import files from the filesystem
              if (\Input::get('filesImported'))
              {
                     $this->import('Session');
                     $session = $this->Session->get('referer');
                     $session[TL_REFERER_ID]['current'] = 'contao/main.php?do=gallery_creator';
                     $this->Session->set('referer', $session);
              }

              switch (Input::get('mode'))
              {

                     case 'imagerotate' :

                            $objPic = $this->Database->prepare('SELECT path FROM tl_gallery_creator_pictures WHERE id=?')->execute(Input::get('imgId'));
                            // Rotate image anticlockwise
                            $angle = 270;
                            GalleryCreator\GcHelpers::imageRotate($objPic->path, $angle);
                            Dbafs::addResource($objPic->path, true);
                            $this->redirect('contao/main.php?do=gallery_creator&table=tl_gallery_creator_pictures&id=' . Input::get('id'));
                            break;
                     default :
                            break;
              }//end switch

              switch (Input::get('act'))
              {
                     case 'create' :
                            //Neue Bilder können ausschliesslich über einen Bildupload realisiert werden
                            $this->Redirect('contao/main.php?do=gallery_creator&table=tl_gallery_creator_pictures&id=' . Input::get('pid'));
                            break;

                     case 'select' :
                            if (!$this->User->isAdmin)
                            {
                                   $GLOBALS['TL_DCA']['tl_gallery_creator_pictures']['list']['sorting']['filter'] = array(
                                          array(
                                                 'owner=?',
                                                 $this->User->id
                                          )
                                   );
                            }

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

              //get the CLIPBOARD settings from the current Session
              $arrClipboard = $this->Session->get('CLIPBOARD');
              $arrClipboard = $arrClipboard['tl_gallery_creator_pictures'];

              if (!$arrClipboard['mode'] && Input::get('act') != 'paste')
              {
                     return null;
              }

              if ((Input::get('act') == 'paste' && Input::get('mode') == 'cut') || $arrClipboard['mode'])
              {
                     if ($row['id'] == Input::get('id'))
                     {
                            return null;
                     }
                     //generate the icon
                     $pasteAfterIcon = $this->generateImage($icon, sprintf($label, $row['id']), 'class="blink"');

                     //replace 'cut' with 'cutAll' when moving several images
                     $href = $arrClipboard['mode'] == 'cutAll' ? str_replace('cut', 'cutAll', $href) : $href;

                     $url = $this->addToUrl(sprintf('%s&id=%d&pid=%d', $href, Input::get('id'), $row['id']));
                     return sprintf('<a href="%s" title="%s" %s>%s</a> ', $url, specialchars($title), $attributes, $pasteAfterIcon);
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

              return ($this->User->isAdmin || $this->User->id == $objImg->owner || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) ? '<a href="' . $this->addToUrl($href . '&imgId=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $this->generateImage($icon, $label) . '</a> ' : $this->generateImage($icon, $label);
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
              if (!is_file(TL_ROOT . "/" . $arrRow['path']))
              {
                     return "";
              }

              $objFile = new File($arrRow['path']);
              if ($objFile->isGdImage)
              {
                     //if dataset contains a link to movie file...
                     $hasMovie = NULL;
                     $src = $objFile->path;
                     $src = trim($arrRow['socialMediaSRC']) != "" ? trim($arrRow['socialMediaSRC']) : $src;
                     $src = trim($arrRow['localMediaSRC']) != "" ? trim($arrRow['localMediaSRC']) : $src;
                     if (trim($arrRow['socialMediaSRC']) != "" or trim($arrRow['localMediaSRC']) != "")
                     {
                            $type = trim($arrRow['localMediaSRC']) == "" ? ' embeded local-media: ' : ' embeded social media: ';
                            $iconSrc = 'system/modules/gallery_creator/assets/images/film.png';
                            $movieIcon = $this->generateImage($iconSrc);
                            $hasMovie = sprintf('<div class="block">%s%s<a href="%s" data-lightbox="gc_album_%s">%s</a></div>', $movieIcon, $type, $src, Input::get('id'), $src);
                     }
                     //generate icon/thumbnail
                     if ($GLOBALS['TL_CONFIG']['thumbnails'])
                     {
                            $src = Image::get($objFile->path, "100", "", "center_center");
                     }
                     //return html
                     $return = sprintf('<div class="cte_type %s"><strong>%s</strong> - %s [%s x %s px, %s]</div>', $key, $arrRow['headline'], $arrRow['name'], $objFile->width, $objFile->height, $this->getReadableSize($objFile->filesize));
                     $return .= $hasMovie;
                     $return .= $image ? '<div class="block"><img src="<?php echo $src; ?>" width="100"></div>' : NULL;
                     $return .= sprintf('<div class="limit_height%s block">%s</div>', ($GLOBALS['TL_CONFIG']['thumbnails'] ? ' h64' : ''), specialchars($arrRow['comment']));
                     return $return;
              }
       }


       /**
        * input-field-callback generate image
        * Returns the html-img-tag
        * @return string
        */
       public function inputFieldCbGenerateImage()
       {

              $objImg = $this->Database->prepare('SELECT path,name,pid FROM tl_gallery_creator_pictures WHERE id=?')->limit(1)->execute(Input::get('id'));
              $src = $objImg->path;
              return '

<div class="w50 easyExclude easyExcludeFN_picture" style="height:200px;">
	<h3><label for="ctrl_picture">' . $objImg->name . '</label></h3>
	<a href="' . $src . '" data-lightbox="gc_image_' . Input::get('id') . '"><img src="' . Image::get($src, '180', '180', 'crop') . '" width="100"></a>
</div>
		';
       }


       /**
        * input-field-callback generate image information
        * Returns the html-table-tag containing some picture informations
        * @return string
        */
       public function inputFieldCbGenerateImageInformation(DataContainer $dc)
       {

              $objImg = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE id=?')->execute($dc->id);
              $objUser = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objImg->owner);
              $output = '
			<div class="album_infos">
			<br><br>
			<table cellpadding="0" cellspacing="0" width="100%" summary="">
				<tr class="odd">
					<td style="width:20%"><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['pid'][0] . ': </strong></td>
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
					<td>' . (trim($objImg->video_href_local) != '' ? trim($objImg->video_href_local) : '-') . '</td>
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

              if (Input::get('table') == 'tl_gallery_creator_pictures')
              {
                     //da alle neuen Bilder (neue Datensaetze) nur über fileupload oder importImages realisiert werden, ist der "Create-Button" obsolet
                     //entfernt den Create-Button aus den den global operations
                     $pattern = '|<a href="[^"]*tl_gallery_creator_pictures[^"]*mode=create[^"]*"[^>]*></a>|Usi';
                     $strContent = preg_replace($pattern, '', $strContent);

                     //entfernt den Create-Button aus den den operations
                     $pattern = '|<a href="[^"]*tl_gallery_creator_pictures[^"]*act=create[^"]*"[^>]*><img[^>]*></a>|Usi';
                     $strContent = preg_replace($pattern, '', $strContent);

                     //Bei einigen Browsern überragt die textarea den unteren Seitenrand, deshalb eine weitere leere clearing-box
                     $strContent = str_replace('</fieldset>', '<div class="clr" style="clear:both"><p> </p><!-- clearing Box --></div></fieldset>', $strContent);
              }

              if (Input::get('table') == 'tl_gallery_creator_pictures' && Input::get('act') == 'select')
              {
                     //saveNcreate button-entfernen
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
                     //saveNclose button-entfernen
                     //$strContent=preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/','',$strContent);
                     //copy button-entfernen
                     $strContent = preg_replace('/<input(.*?)copy(.*?)submit(.*?)>/', '', $strContent);
                     //saveNback button-entfernen
                     //$strContent=preg_replace('/<input type=\"submit\" name=\"saveNback\"((\r|\n|.)+?)>/','',$strContent);
              }
              return $strContent;
       }


       /**
        * ondelete-callback
        * prevents deleting images by unauthorised users
        */
       public function ondeleteCb(DC_Table $dc)
       {

              $objImg = $this->Database->prepare('SELECT id,owner,path,name FROM tl_gallery_creator_pictures WHERE id=?')->execute($dc->id);

              if ($objImg->owner == $this->User->id || $this->User->isAdmin || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
              {
                     //Nur Bilder innerhalb des gallery_creator_albums und wenn sie nicht in einem anderen Datensatz noch Verwendung finden, werden vom Server geloescht
                     $objDeleteItem = $this->Database->prepare('DELETE FROM tl_gallery_creator_pictures WHERE id=?')->execute($objImg->id);
                     $objImgNumRows = $this->Database->prepare('SELECT id FROM tl_gallery_creator_pictures WHERE path=? AND name=?')->execute($objImg->path, $objImg->name);

                     if (strstr($objImg->path, $this->uploadPath) && $objImgNumRows->numRows < 1)
                     {
                            //Datei vom Server loeschen
                            $this->Files->delete($objImg->path);
                            //Datensatz aus tl_files loeschen
                            Dbafs::deleteResource($objImg->path);
                            Dbafs::addResource($this->uploadPath);
                     }
              }
              if (!$this->User->isAdmin && $objImg->owner != $this->User->id)
              {
                     $this->log('Datensatz mit ID ' . $dc->id . ' wurde vom  Benutzer mit ID ' . $this->User->id . ' versucht aus tl_gallery_creator_pictures zu loeschen.', __METHOD__, TL_ERROR);
                     $this->redirect('contao/main.php?do=error');
              }

       }


       /**
        * child-record-callback
        * @param array
        * @return string
        */
       public function onloadCbCheckPermission()
       {

              // admin hat keine Einschraenkungen
              if ($this->User->isAdmin)
              {
                     return;
              }

              //Nur der Ersteller hat keine Einschraenkungen

              if (Input::get('act') == 'edit')
              {
                     $objUser = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_pictures WHERE id=?')->execute(Input::get('id'));

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
}
