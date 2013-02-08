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
                            'onloadCbFileupload'
                     ),
                     array(
                            'tl_gallery_creator_albums',
                            'onloadCbSetUpPalettes'
                     ),

                     array(
                            'tl_gallery_creator_albums',
                            'onloadCbCheckFolderSettings'
                     ),
                     array(
                            'tl_gallery_creator_albums',
                            'onloadCbImportFromFilesystem'
                     ),
                     array(
                            'tl_gallery_creator_albums',
                            'onloadCbGetGcCteElements'
                     ),
                     array(
                            'tl_gallery_creator_albums',
                            'onloadCbReviseTable'
                     )
              ),
              'ondelete_callback' => array(
                     array(
                            'tl_gallery_creator_albums',
                            'ondeleteCb'
                     )
              ),
              'sql' => array(
                     'keys' => array(
                            'id' => 'primary',
                            'pid' => 'index',
                            'alias' => 'index',
                            'folder_id' => 'index'
                     )
              )
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
              'global_operations' => array(
                     'all' => array(
                            'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                            'href' => 'act=select',
                            'class' => 'header_edit_all',
                            'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
                     ),
                     'clean_db' => array(
                            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['clean_db'],
                            'href' => 'href is set in $this->setUpPalettes',
                            'class' => 'led_clean_db',
                            'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
                     )
              ),
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
                            'icon' => 'system/modules/gallery_creator/assets/images/photo.png',
                            'button_callback' => array(
                                   'tl_gallery_creator_albums',
                                   'buttonCbAddImages'
                            )
                     ),
                     'import_images' => array(
                            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['import_images'],
                            'icon' => 'system/modules/gallery_creator/assets/images/photo_album.png',
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
              'import_images' => '{upload_settings},preserve_filename,multiSRC',
              'clean_db' => '{maintenance},clean_db'
       ),

       // Subpalettes
       'subpalettes' => array('protected' => 'groups'),

       // Fields
       'fields' => array(
              'id' => array('sql' => "int(10) unsigned NOT NULL auto_increment"),
              'pid' => array(
                     'foreignKey' => 'tl_gallery_creator_albums.alias',
                     'sql' => "int(10) unsigned NOT NULL default '0'",
                     'relation' => array(
                            'type' => 'belongsTo',
                            'load' => 'lazy'
                     )
              ),
              'sorting' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
              'tstamp' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
              'folder_id' => array(
                     'foreignKey' => 'tl_files.name',
                     'sql' => "int(10) unsigned NOT NULL default '0'",
                     'relation' => array(
                            'type' => 'hasOne',
                            'load' => 'eager'
                     )
              ),

              'published' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['published'],
                     'inputType' => 'checkbox',
                     'eval' => array('submitOnChange' => true),
                     'sql' => "char(1) NOT NULL default '1'"
              ),

              'date' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['date'],
                     'inputType' => 'text',
                     'default' => time(),
                     'eval' => array(
                            'mandatory' => true,
                            'maxlength' => 10,
                            'datepicker' => true,
                            'submitOnChange' => true,
                            'rgxp' => 'date',
                            'tl_class' => 'w50 wizard m12',
                            'submitOnChange' => false
                     ),
                     'sql' => "int(10) unsigned NOT NULL default '0'"
              ),

              'owner' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owner'],
                     'default' => $this->User->id,
                     'foreignKey' => 'tl_user.name',
                     'inputType' => 'select',
                     'eval' => array(
                            'includeBlankOption' => true,
                            'blankOptionLabel' => 'noName',
                            'doNotShow' => true,
                            'nospace' => true,
                            'tl_class' => 'w50 m12'
                     ),
                     'sql' => "int(10) NOT NULL default '0'",
                     'relation' => array(
                            'type' => 'hasOne',
                            'load' => 'eager'
                     )
              ),

              'owners_name' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owners_name'],
                     'default' => $this->User->name,
                     'eval' => array(
                            'doNotShow' => true,
                            'tl_class' => 'clr w50 m12 readonly'
                     ),
                     'sql' => "text NULL"
              ),

              'event_location' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['event_location'],
                     'exclude' => true,
                     'inputType' => 'text',
                     'eval' => array(
                            'mandatory' => false,
                            'tl_class' => 'clr w50 m12',
                            'submitOnChange' => false
                     ),
                     'sql' => "varchar(255) NOT NULL default ''"
              ),

              'name' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['name'],
                     'inputType' => 'text',
                     'eval' => array(
                            'mandatory' => true,
                            'tl_class' => 'w50 m12',
                            'submitOnChange' => false
                     ),
                     'sql' => "varchar(255) NOT NULL default ''"
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
                     'save_callback' => array(
                            array(
                                   'tl_gallery_creator_albums',
                                   'saveCbGenerateAlias'
                            )
                     ),
                     'sql' => "varbinary(128) NOT NULL default ''"
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
                     ),
                     'sql' => "text NULL"
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
                     ),
                     'sql' => "varchar(255) NOT NULL default ''"
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
                     'save_callback' => array(
                            array(
                                   'tl_gallery_creator_albums',
                                   'saveCbDisplAlbInThisContentElements'
                            )
                     ),
                     'eval' => array(
                            'multiple' => true,
                            'doNotShow' => false,
                            'submitOnChange' => false
                     ),
                     'sql' => "text NULL"
              ),
              
              // save value in tl_user
              'img_resolution' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_resolution'],
                     'default' => '600',
                     'inputType' => 'select',
                     'load_callback' => array(
                            array(
                                   'tl_gallery_creator_albums',
                                   'loadCbGetImageResolution'
                            )
                     ),
                     'save_callback' => array(
                            array(
                                   'tl_gallery_creator_albums',
                                   'saveCbSaveImageResolution'
                            )
                     ),
                     'options' => array_merge(array('no_scaling'), range(100, 3500, 50)),
                     'reference' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['reference'],
                     'eval' => array(
                            'doNotShow' => true,
                            'tl_class' => 'w50',
                            'submitOnChange' => true
                     ),
                     'sql' => "smallint(5) unsigned NOT NULL default '600'"
              ),

              // save value in tl_user
              'img_quality' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_quality'],
                     'default' => '1000',
                     'inputType' => 'select',
                     'load_callback' => array(
                            array(
                                   'tl_gallery_creator_albums',
                                   'loadCbGetImageQuality'
                            )
                     ),
                     'save_callback' => array(
                            array(
                                   'tl_gallery_creator_albums',
                                   'saveCbSaveImageQuality'
                            )
                     ),
                     'options' => range(100, 1000, 100),
                     'eval' => array(
                            'doNotShow' => true,
                            'tl_class' => 'w50',
                            'submitOnChange' => true
                     ),
                     'sql' => "smallint(4) unsigned NOT NULL default '1000'"
              ),

              'preserve_filename' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['preserve_filename'],
                     'inputType' => 'checkbox',
                     'default' => true,
                     'eval' => array(
                            'doNotShow' => true,
                            'submitOnChange' => true
                     ),
                     'sql' => "char(1) NOT NULL default ''"
              ),

              'multiSRC' => array
              (
                     'label' => &$GLOBALS['TL_LANG']['tl_content']['multiSRC'],
                     'exclude' => true,
                     'inputType' => 'fileTree',
                     'eval' => array(
                            'doNotShow' => true,
                            'multiple' => true,
                            'fieldType' => 'checkbox',
                            'files' => true,
                            'mandatory' => true
                     ),
                     'sql' => "blob NULL"
              ),

              'protected' => array(
                     'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['protected'],
                     'exclude' => true,
                     'inputType' => 'checkbox',
                     'eval' => array(
                            'doNotShow' => true,
                            'submitOnChange' => true,
                            'tl_class' => 'clr'
                     ),
                     'sql' => "char(1) NOT NULL default ''"
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
                     ),
                     'sql' => "blob NULL"
              ),

              'clean_db' => array(
                     'input_field_callback' => array(
                            'tl_gallery_creator_albums',
                            'inputFieldCbCleanDb'
                     ),
                     'eval' => array('doNotShow' => true)
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
        *  Pfad ab TL_ROOT ins Bildverzeichnis
        * @var string
        */
       public $uploadPath;

       public function __construct()
       {
              parent::__construct();
              $this->import('BackendUser', 'User');
              // path to the gallery_creator upload-directory
              $this->uploadPath = GALLERY_CREATOR_UPLOAD_PATH;

              // register the parseBackendTemplate Hook
              $GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array(
                     'tl_gallery_creator_albums',
                     'myParseBackendTemplate'
              );

              if ($_SESSION['BE_DATA']['CLIPBOARD']['tl_gallery_creator_albums']['mode'] == 'copyAll') {
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
              // enable cutting albums to album-owners and admins only
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
              if ($this->User->isAdmin && $arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id'])))) {
                     $disablePA = true;
                     $disablePI = true;
              }

              // Return the buttons
              $imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']), 'class="blink"');
              $imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']), 'class="blink"');

              if ($row['id'] > 0) {
                     $return = $disablePA ? $this->generateImage('pasteafter_.gif', '', 'class="blink"') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&mode=1&pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteAfter . '</a> ';
              }
              return $return . ($disablePI ? $this->generateImage('pasteinto_.gif', '', 'class="blink"') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&mode=2&pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteInto . '</a> ');
       }

       /**
        * Checks if the current user obtains full rights or only restricted rights on the selected album
        */
       public function checkUserRole()
       {
              $objUser = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_albums WHERE id=?')->execute(Input::get('id'));

              if ($this->User->isAdmin || true == $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) {
                     $this->restrictedUser = false;
                     return;
              }

              if ($objUser->owner != $this->User->id) {
                     $this->restrictedUser = true;
                     return;
              }
              // ...so the current user is the album owner
              $this->restrictedUser = false;
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
              while ($hasParent) {
                     $level++;
                     $mysql = $this->Database->prepare('SELECT pid FROM tl_gallery_creator_albums WHERE id=?')->executeUncached($pid);
                     if ($mysql->pid < 1)
                            $hasParent = false;
                     $pid = $mysql->pid;
              }
              return $level;
       }

       /**
        * return the album upload path
        * @return string
        */
       public static function getUplaodPath()
       {
              return self::uploadPath;
       }

       /**
        * Input-field-callback
        * return the html
        * @return string
        */
       public function inputFieldCbCleanDb()
       {

              $output = '
<div class="clean_db">
<br /><br />
		<input type="checkbox" name="clean_db">
		<label for="clean_db">Clean the database from damaged/invalid/orphaned entries</label>
</div>
			';
              return $output;
       }

       /**
        * Input-field-callback
        * return the html-table with the album-information for restricted users
        * @return string
        */
       public function inputFieldCbGenerateAlbumInformations()
       {
              $objAlb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute(Input::get('id'));
              $objUser = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objAlb->owner);

              // check User Role
              $this->checkUserRole();
              if (false == $this->restrictedUser) {
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
              } else {
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
              return GalleryCreator\GcHelpers::generateUploader(Input::get('id'));
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
              $label = str_replace('#icon#', "system/modules/gallery_creator/assets/images/slides.png", $label);
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
              if (Input::get('mode') == 'clean_db') {
                     // remove buttons
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
              }

              if (Input::get('act') == 'select') {
                     // remove buttons
                     if (Input::get('table') != 'tl_gallery_creator_pictures') {
                            $strContent = preg_replace('/<input type=\"submit\" name=\"delete\"((\r|\n|.)+?)>/', '', $strContent);
                     }
                     $strContent = preg_replace('/<input type=\"submit\" name=\"cut\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"copy\"((\r|\n|.)+?)>/', '', $strContent);
              }

              if (Input::get('mode') == 'fileupload') {
                     // form encode
                     $strContent = str_replace('application/x-www-form-urlencoded', 'multipart/form-data', $strContent);
                     // remove buttons
                     $strContent = preg_replace('/<input type=\"submit\" name=\"save\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"uploadNback\"((\r|\n|.)+?)>/', '', $strContent);
              }

              if (Input::get('mode') == 'import_images') {
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
              if (Input::get('act') != 'deleteAll') {
                     $this->checkUserRole();
                     if ($this->restrictedUser) {
                            $this->log('Datensatz mit ID ' . Input::get('id') . ' wurde von einem nicht authorisierten Benutzer versucht aus tl_gallery_creator_albums zu loeschen.', __METHOD__, TL_ERROR);
                            $this->redirect('contao/main.php?do=error');
                     }

                     // also delete the child element
                     $arrDeletedAlbums = GalleryCreator\GcHelpers::getAllSubalbums(Input::get('id'));
                     $arrDeletedAlbums = array_merge(array(Input::get('id')), $arrDeletedAlbums);

                     foreach ($arrDeletedAlbums as $idDelAlbum) {
                            $objAlb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($idDelAlbum);
                            if ($this->User->isAdmin || $objAlb->owner == $this->User->id || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) {
                                   // remove the deleted directory from tl_files
                                   GalleryCreator\GcHelpers::deleteFromFilesystem($this->uploadPath . '/' . $objAlb->alias);

                                   // remove all pictures from tl_files
                                   $objFile = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=?')->execute($idDelAlbum);
                                   while ($objFile->next()) {
                                          // preserve pictures from external directories
                                          if (strstr($objFile->path, $this->uploadPath)) {
                                                 GalleryCreator\GcHelpers::deleteFromFilesystem($objFile->path);
                                          }
                                   }
                                   // remove all pictures from tl_gallery_creator_pictures
                                   $this->Database->prepare('DELETE FROM tl_gallery_creator_pictures WHERE pid=?')->execute($idDelAlbum);

                                   // remove the albums from tl_gallery_creator_albums
                                   $this->Database->prepare('DELETE FROM tl_gallery_creator_albums WHERE id=?')->execute($idDelAlbum);

                                   // remove the directory from the filesystem
                                   $folder = new Folder($this->uploadPath . '/' . $objAlb->alias);
                                   Files::getInstance()->chmod($this->uploadPath . '/' . $objAlb->alias, 0777);
                                   $folder->delete($this->uploadPath . '/' . $objAlb->alias);
                            } else {
                                   // do not delete childalbums, which the user do not owns
                                   $objFolder = FilesModel::findByPath('files/gallery_creator_albums');
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
              // create the upload directory if it doesn't already exists
              $folder = new Folder($this->uploadPath);

              GalleryCreator\GcHelpers::registerInFilesystem($this->uploadPath);

              
              Files::getInstance()->chmod($this->uploadPath, 0777);
              if (!is_writable(TL_ROOT . '/' . $this->uploadPath)) {
                     $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['dirNotWriteable'], $this->uploadPath);
              }
       }

       /**
        * onload-callback
        * initiate the fileupload
        */
       public function onloadCbFileupload()
       {

              if (Input::get('mode') != 'fileupload' || !$_FILES['file'])
                     return;

              $objAlb = GalleryCreatorAlbumsModel::findById(Input::get('id'));

              // move uploaded file in the album-directory
              if ($arrUploadedFile = GalleryCreator\GcHelpers::fileupload($objAlb->id, \Input::post('fileName'))) {
                     // write the new entry in tl_gallery_creator_pictures
                     $strFileSrc = $arrUploadedFile['strFileSrc'];
                     GalleryCreator\GcHelpers::createNewImage($objAlb->id, $strFileSrc);
              }
              exit();
       }

       /**
        * onload-callback
        * return an array with the ids of all gallery_creator content-elements where the album with the id "Input::get('id')" is selected
        * @return array
        */
       public function onloadCbGetGcCteElements()
       {
              if (Input::get('act') != '')
              {
                     return;
              }       
              $objDb = $this->Database->execute('SELECT id FROM tl_gallery_creator_albums');
              $arrAlbumIds = array();
              while ($objDb->next()) {
                     $arrAlbumIds[] = $objDb->id;
              }
              foreach ($arrAlbumIds as $albumId) {
                     $arrGcArticles = array();
                     $objDb = $this->Database->prepare('SELECT id, gc_publish_albums FROM tl_content WHERE type=?')->execute('gallery_creator');
                     while ($objDb->next()) {
                            $arrPublAlbums = $objDb->gc_publish_albums != "" ? unserialize($objDb->gc_publish_albums) : array();
                            if (in_array($albumId, $arrPublAlbums)) {
                                   $arrGcArticles[] = $objDb->id;
                            }
                     }

                     // update tl_gallery_creator_albums.gc_articles
                     $arrGcArticles = count($arrGcArticles) > 0 ? serialize($arrGcArticles) : '';
                     $objDbUpdate = $this->Database->prepare('UPDATE tl_gallery_creator_albums SET displ_alb_in_this_ce=? WHERE id=?')->execute($arrGcArticles, $albumId);
              }
       }

       /**
        * onload-callback
        * import images from an external directory to an existing album
        */
       public function onloadCbImportFromFilesystem()
       {
              if (Input::get('mode') != 'import_images')
                     return;

              // load language file
              $this->loadLanguageFile('tl_content');

              if (!$this->Input->post('FORM_SUBMIT'))
              {
                     return;
              }

              $blnPreserveFilename = Input::post('preserve_filename');
              $intAlbumId = Input::get('id');
              $strMultiSRC = $this->Input->post('multiSRC');

              if (strlen(trim($strMultiSRC))) {
                     $this->Database->prepare('UPDATE tl_gallery_creator_albums SET preserve_filename=? WHERE id=?')->execute($blnPreserveFilename, $intAlbumId);
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['preserve_filename']['eval']['submitOnChange'] = false;

                     // import Images from filesystem and write entries to tl_gallery_creator_pictures
                     GalleryCreator\GcHelpers::importFromFilesystem($intAlbumId, $strMultiSRC);
              }
              $this->redirect('contao/main.php?do=gallery_creator&table=tl_gallery_creator_pictures&id=' . $intAlbumId);
       }

       /**
        * onload-callback
        * revise table
        */
       public function onloadCbReviseTable()
       {
              GalleryCreator\GcHelpers::reviseTable();
       }

       /**
        * onload-callback
        * create the palette
        */
       public function onloadCbSetUpPalettes()
       {
              // global_operations for admin only
              if (!$this->User->isAdmin) {
                     unset($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['all']);
                     unset($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['clean_db']);
              }
              
              // for security reasons give only readonly rights to these fields
              $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['id']['eval']['style'] = '" readonly="readonly';
              $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['owners_name']['eval']['style'] = '" readonly="readonly';

              // create the jumploader palette
              if (Input::get('mode') == 'fileupload') {
                     if ($this->User->gc_img_resolution == 'no_scaling') 
                     {
                            $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['fileupload'] = str_replace(',img_quality','',$GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['fileupload']);
                     }
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['fileupload'];
                     return;
              }

              // create the import_images palette
              if (Input::get('mode') == 'import_images') {
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['import_images'];
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['preserve_filename']['eval']['submitOnChange'] = false;
                     return;
              }

              // the palette for admins
              if ($this->User->isAdmin) {
                     $objAlb = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums')->limit(1)->execute();
                     if ($objAlb->next()) {
                            $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['clean_db']['href'] = 'act=edit&table&mode=clean_db&id=' . $objAlb->id;

                     } else {
                            unset($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['clean_db']);
                     }

                     if (Input::get('mode') == 'clean_db') {
                            if ($this->Input->post('FORM_SUBMIT') && $this->Input->post('clean_db')) {
                                   GalleryCreator\GcHelpers::reviseTable(true);
                                   $this->redirect('contao/main.php?do=gallery_creator');
                                   exit();
                            }

                            $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['clean_db'];
                            return;
                     }

                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['owner']['eval']['doNotShow'] = false;
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['protected']['eval']['doNotShow'] = false;
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['groups']['eval']['doNotShow'] = false;
                     return;
              }

              $objAlb = $this->Database->prepare('SELECT id, owner FROM tl_gallery_creator_albums WHERE id=?')->execute(Input::get('id'));
              // only adminstrators and album-owners obtains writing-access for these fields
              $this->checkUserRole();
              if ($objAlb->owner != $this->User->id && true == $this->restrictedUser) {
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['restricted_user'];
              }
       }

       /**
        * displ_alb_in_this_ce  - options_callback
        * @return array
        */
       protected function optionsCbDisplAlbInThisContentElements()
       {
              $objDb = $this->Database->prepare('SELECT tl_content.id AS id, tl_article.title as title, tl_page.title as pagename FROM tl_content, tl_article, tl_page  WHERE tl_article.id=tl_content.pid AND tl_page.id=tl_article.pid AND tl_content.type=?')->execute('gallery_creator');
              $opt = array();
              while ($objDb->next()) {
                     $opt[$objDb->id] = sprintf($GLOBALS['TL_LANG']['tl_gallery_creator_albums']['reference']['displ_alb_in_this_ce'], $objDb->id, $objDb->title, $objDb->pagename);
              }
              return $opt;
       }

       /**
        * Options Callback for the selection of the preview thumb
        * return an array with all filenames (imagenames) of specified album
        * @return array
        */
       public function optionsCbThumb()
       {
              $objDb = $this->Database->prepare('SELECT id,name FROM tl_gallery_creator_pictures WHERE pid=? ORDER BY id')->execute(Input::get('id'));
              $arrThumbId = array();
              while ($objDb->next()) {
                     $arrThumbId[$objDb->id] = $objDb->name;
              }
              $arrSubalbums = GalleryCreator\GcHelpers::getAllSubalbums(Input::get('id'));

              if (count($arrSubalbums)) {
                     foreach ($arrSubalbums as $albId) {
                            $objPic = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_pictures WHERE pid=? ORDER BY id')->execute($albId);
                            if ($objPic->numRows) {
                                   $objAlbName = $this->Database->prepare('SELECT name, alias FROM tl_gallery_creator_albums WHERE id=?')->execute($albId);
                                   $arrThumbId["Subalbum: " . $objAlbName->alias] = "--- Subalbum: " . $objAlbName->name . " ---";
                                   while ($objPic->next()) {
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
              while ($objDb->next()) {
                     $arrContentElements[] = $objDb->id;
              }

              // update tl_content.gc_publish_albums in each gallery_creator content element
              foreach ($arrContentElements as $currentCteId) {
                     $objSelect = $this->Database->prepare('SELECT gc_publish_albums FROM tl_content WHERE id=?')->executeUncached($currentCteId);
                     // !important!!! ->executeUncached
                     $arrPublAlbums = is_array($objSelect->gc_publish_albums) ? $objSelect->gc_publish_albums : unserialize($objSelect->gc_publish_albums);
                     $arrPublAlbums = count($arrPublAlbums) > 0 ? $arrPublAlbums : array();

                     if (in_array($currentCteId, $arrGcArticles)) {
                            $arrPublAlbums[] = $albumId;
                     } else {
                            if (count($arrPublAlbums)) {
                                   $arrPublAlbums = array_flip($arrPublAlbums);
                                   unset($arrPublAlbums[$albumId]);
                                   $arrPublAlbums = array_flip($arrPublAlbums);
                            }
                     }
                     $arrPublAlbums = array_unique($arrPublAlbums);
                     $objDbUpd = $this->Database->prepare('UPDATE tl_content SET gc_publish_albums=? WHERE id=?')->executeUncached(serialize($arrPublAlbums), $currentCteId);
                     $this->log('A new version of record "tl_content.id=' . $currentCteId . '" has been created', __METHOD__, GENERAL);
              }
              return $varValue;
       }

       /**
        * generate an albumalias based on the albumname and create a directory of the same name
        * and register the directory in tl files
        * @param mixed
        * @param object
        * @return string
        */
       public function saveCbGenerateAlias($strAlias, DataContainer $dc)
       {
              $strAlias = standardize($strAlias);
              // if there isn't an existing albumalias generate one from the albumname
              if (!strlen($strAlias))
              {
                     $strAlias = standardize($dc->activeRecord->name);
              }

              // limit alias to 50 characters
              $strAlias = substr($strAlias, 0, 43);

              // remove invalid characters
              $strAlias = preg_replace("/[^a-z0-9\_\-]/", "", $strAlias);

              // if alias already exists add the album-id to the alias
              $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id!=? AND alias=?')->execute($dc->activeRecord->id, $strAlias);
              if ($objAlbum->numRows)
              {
                     $strAlias = 'id-' . $dc->activeRecord->id . '-' . $strAlias;
              }
              
              // get current row
              $objAlbum = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->executeUncached($dc->activeRecord->id);              
         
              // if a new album was created
              if (!strlen($objAlbum->alias))
              {
                     // create the new folder and register it in tl_files
                     $objFolder = new Folder ($this->uploadPath . '/' . $strAlias);
                     GalleryCreator\GcHelpers::registerInFilesystem($objFolder->path);
                     
                     // chmod
                     Files::getInstance()->chmod($objFolder->path, 0777);
                     
                     // return the new albumalias
                     return $strAlias;
              }
              
              // if alias was renamed, update the pathes of each pictures in tl_gallery_creator_pictures
              if ($objAlbum->alias != $strAlias)
              {
                     if (is_dir(TL_ROOT . '/' . $this->uploadPath . '/' . $objAlbum->alias))
                     {
                           $objPic = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_pictures WHERE pid=? AND externalFile=?')->execute($dc->activeRecord->id, "");
                            while ($objPic->next())
                            {
                                   // update the paths in tl_gallery_creator_pictures
                                   $newFilePath = $this->uploadPath . '/' . $strAlias . '/' . $objPic->name;
                                   $this->Database->prepare('UPDATE tl_gallery_creator_pictures SET path=? WHERE id=?')->execute($newFilePath, $objPic->id);

                                   // update the path of each file in tl_files
                                   $oldFilePath = $this->uploadPath . '/' . $objAlbum->alias . '/' . $objPic->name;
                                   GalleryCreator\GcHelpers::registerInFilesystem($oldFilePath, $newFilePath);
                            }
                     }

                     $oldFolderPath = $this->uploadPath . '/' . $objAlbum->alias;
                     $newFolderPath = $this->uploadPath . '/' . $strAlias;
                     GalleryCreator\GcHelpers::registerInFilesystem($oldFolderPath, $newFolderPath);

                     // rename the dir to the new albumalias
                     Files::getInstance()->chmod($this->uploadPath . '/' . $objAlbum->alias, 0777);
                     Files::getInstance()->rename($this->uploadPath . '/' . $objAlbum->alias, $this->uploadPath . '/' . $strAlias);
              }

              return $strAlias;
       }

       /**
        * save_callback for the image quality above the jumploader applet
        * @return string
        */
       public function saveCbSaveImageQuality($value)
       {
              $db = $this->Database->prepare('UPDATE tl_user SET gc_img_quality=? WHERE id=?')->execute($value, $this->User->id);
       }

       /**
        * save_callback for the image resolution above the jumploader applet
        * @return string
        */
       public function saveCbSaveImageResolution($value)
       {
              $db = $this->Database->prepare('UPDATE tl_user SET gc_img_resolution=? WHERE id=?')->execute($value, $this->User->id);
       }

}

?>