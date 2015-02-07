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

$this->import('BackendUser', 'User');

$GLOBALS['TL_DCA']['tl_gallery_creator_albums'] = array(
       // Config
       'config'      => array(
              'ctable'            => array('tl_gallery_creator_pictures'),
              'doNotCopyRecords'  => true,
              'enableVersioning'  => true,
              'dataContainer'     => 'Table',
              'onload_callback'   => array(
                     array('tl_gallery_creator_albums', 'onloadCbFileupload'),
                     array('tl_gallery_creator_albums', 'onloadCbSetUpPalettes'),
                     array('tl_gallery_creator_albums', 'onloadCbCheckFolderSettings'),
                     array('tl_gallery_creator_albums', 'onloadCbImportFromFilesystem'),
                     array('tl_gallery_creator_albums', 'onloadCbGetGcCteElements'),
                     array('tl_gallery_creator_albums', 'onloadCbReviseTable')
              ),
              'ondelete_callback' => array(
                     array('tl_gallery_creator_albums', 'ondeleteCb')
              ),
              'sql'               => array(
                     'keys' => array(
                            'id'    => 'primary',
                            'pid'   => 'index',
                            'alias' => 'unique'
                     )
              )
       ),
       // List
       'list'        => array(
              'sorting'           => array(
                     'panelLayout'           => 'limit,sort',
                     'mode'                  => 5,
                     'paste_button_callback' => array('tl_gallery_creator_albums', 'buttonCbPastePicture')
              ),
              'label'             => array(
                     'fields'         => array('name'),
                     'format'         => '<span style="#padding-left#"><a href="#href#" title="#title#"><img src="#icon#"></span> #datum# <span style="color:#b3b3b3; padding-left:3px;">[%s] [#count_pics# images]</span></a>',
                     'label_callback' => array('tl_gallery_creator_albums', 'labelCb')
              ),
              'global_operations' => array(
                     'all'      => array(
                            'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                            'href'       => 'act=select',
                            'class'      => 'header_edit_all',
                            'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
                     ),
                     'clean_db' => array(
                            'label'      => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['clean_db'],
                            'href'       => 'href is set in $this->setUpPalettes',
                            'class'      => 'icon_clean_db',
                            'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
                     )
              ),
              'operations'        => array(

                     'edit'          => array(
                            'label'           => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['list_pictures'],
                            'href'            => 'table=tl_gallery_creator_pictures',
                            'icon'            => 'system/modules/gallery_creator/assets/images/text_list_bullets.png',
                            'attributes'      => 'class="contextmenu"',
                            'button_callback' => array('tl_gallery_creator_albums', 'buttonCbEdit')
                     ),
                     'delete'        => array(
                            'label'           => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['delete'],
                            'href'            => 'act=delete',
                            'icon'            => 'delete.gif',
                            'attributes'      => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
                            'button_callback' => array('tl_gallery_creator_albums', 'buttonCbDelete')
                     ),
                     'toggle'        => array(
                            'label'           => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['toggle'],
                            'icon'            => 'visible.gif',
                            'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                            'button_callback' => array('tl_gallery_creator_albums', 'toggleIcon')
                     ),
                     'upload_images' => array(
                            'label'           => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['upload_images'],
                            'icon'            => 'system/modules/gallery_creator/assets/images/image_add.png',
                            'button_callback' => array('tl_gallery_creator_albums', 'buttonCbAddImages')
                     ),
                     'import_images' => array(
                            'label'           => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['import_images'],
                            'icon'            => 'system/modules/gallery_creator/assets/images/folder_picture.png',
                            'button_callback' => array('tl_gallery_creator_albums', 'buttonCbImportImages')
                     ),
                     'cut'           => array(
                            'label'           => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['cut'],
                            'href'            => 'act=paste&mode=cut',
                            'icon'            => 'cut.gif',
                            'attributes'      => 'onclick="Backend.getScrollOffset();"',
                            'button_callback' => array('tl_gallery_creator_albums', 'buttonCbCutPicture')
                     )
              )
       ),
       // Palettes
       'palettes'    => array(
              '__selector__'    => array('protected'),
              'default'         => '{album_info},published,name,alias,assignedDir,album_info,displ_alb_in_this_ce,owner,date,event_location,sortBy,comment,visitors;{album_preview_thumb_legend},thumb;{insert_article},insert_article_pre,insert_article_post;{protection:hide},protected',
              'restricted_user' => '{album_info},link_edit_images,album_info',
              'fileupload'      => '{upload_settings},preserve_filename,img_resolution,img_quality;{uploader_legend},uploader,fileupload',
              'import_images'   => '{upload_settings},preserve_filename,multiSRC',
              'clean_db'        => '{maintenance},clean_db'
       ),
       // Subpalettes
       'subpalettes' => array('protected' => 'groups'),
       // Fields
       'fields'      => array(
              'id'                   => array('sql' => "int(10) unsigned NOT NULL auto_increment"),
              'pid'                  => array('foreignKey' => 'tl_gallery_creator_albums.alias', 'sql' => "int(10) unsigned NOT NULL default '0'", 'relation' => array('type' => 'belongsTo', 'load' => 'lazy')),
              'sorting'              => array('sql' => "int(10) unsigned NOT NULL default '0'"),
              'tstamp'               => array('sql' => "int(10) unsigned NOT NULL default '0'"),
              'published'            => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['published'],
                     'inputType' => 'checkbox',
                     'eval'      => array('submitOnChange' => true),
                     'sql'       => "char(1) NOT NULL default '1'"
              ),
              'date'                 => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['date'],
                     'inputType' => 'text',
                     'default'   => time(),
                     'eval'      => array('mandatory' => true, 'datepicker' => true, 'rgxp' => 'date', 'tl_class' => 'w50 wizard', 'submitOnChange' => false),
                     'sql'       => "int(10) unsigned NOT NULL default '0'"
              ),
              'owner'                => array(
                     'label'      => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owner'],
                     'default'    => \BackendUser::getInstance()->id,
                     'foreignKey' => 'tl_user.name',
                     'inputType'  => 'select',
                     'eval'       => array('includeBlankOption' => true, 'blankOptionLabel' => 'noName', 'doNotShow' => true, 'nospace' => true, 'tl_class' => 'w50'),
                     'sql'        => "int(10) unsigned NOT NULL default '0'",
                     'relation'   => array('type' => 'hasOne', 'load' => 'eager')
              ),
              'assignedDir'          => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['assignedDir'],
                     'exclude'   => true,
                     'inputType' => 'fileTree',
                     'eval'      => array('mandatory' => false, 'fieldType' => 'radio', 'tl_class' => 'clr'),
                     'sql'       => "binary(16) NULL"
              ),
              'owners_name'          => array(
                     'label'   => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['owners_name'],
                     'default' => \BackendUser::getInstance()->name,
                     'eval'    => array('doNotShow' => true, 'tl_class' => 'w50 readonly'),
                     'sql'     => "text NULL"
              ),
              'event_location'       => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['event_location'],
                     'exclude'   => true,
                     'inputType' => 'text',
                     'eval'      => array('mandatory' => false, 'tl_class' => 'w50', 'submitOnChange' => false),
                     'sql'       => "varchar(255) NOT NULL default ''"
              ),
              'name'                 => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['name'],
                     'inputType' => 'text',
                     'eval'      => array('mandatory' => true, 'tl_class' => 'w50', 'submitOnChange' => false),
                     'sql'       => "varchar(255) NOT NULL default ''"
              ),
              'alias'                => array(
                     'label'         => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['alias'],
                     'inputType'     => 'text',
                     'eval'          => array('doNotShow' => false, 'doNotCopy' => true, 'maxlength' => 50, 'tl_class' => 'w50', 'unique' => true),
                     'save_callback' => array(array('tl_gallery_creator_albums', 'saveCbGenerateAlias')),
                     'sql'           => "varbinary(128) NOT NULL default ''"
              ),
              'comment'              => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['comment'],
                     'exclude'   => true,
                     'inputType' => 'textarea',
                     'eval'      => array('tl_class' => 'clr long', 'style' => 'height:7em;', 'allowHtml' => false, 'submitOnChange' => false, 'wrap' => 'soft'),
                     'sql'       => "text NULL"
              ),
              'thumb'                => array(
                     'label'                => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['thumb'],
                     'inputType'            => 'radio',
                     'input_field_callback' => array('tl_gallery_creator_albums', 'inputFieldCbThumb'),
                     'eval'                 => array('doNotShow' => true, 'includeBlankOption' => true, 'nospace' => true, 'rgxp' => 'digit', 'maxlength' => 64, 'tl_class' => 'clr', 'submitOnChange' => true),
                     'sql'                  => "varchar(255) NOT NULL default ''"
              ),
              'fileupload'           => array(
                     'label'                => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['fileupload'],
                     'input_field_callback' => array('tl_gallery_creator_albums', 'inputFieldCbGenerateUploaderMarkup'),
                     'eval'                 => array('doNotShow' => true)
              ),
              'album_info'           => array(
                     'input_field_callback' => array('tl_gallery_creator_albums', 'inputFieldCbGenerateAlbumInformations'),
                     'eval'                 => array('doNotShow' => true)
              ),
              'displ_alb_in_this_ce' => array(
                     'label'            => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['displ_alb_in_this_ce'],
                     'exclude'          => true,
                     'inputType'        => 'checkbox',
                     'options_callback' => array('tl_gallery_creator_albums', 'optionsCbDisplAlbInThisContentElements'),
                     'save_callback'    => array(array('tl_gallery_creator_albums', 'saveCbDisplAlbInThisContentElements')),
                     'eval'             => array('multiple' => true, 'doNotShow' => false, 'submitOnChange' => false),
                     'sql'              => "text NULL"
              ),
              // save value in tl_user
              'uploader'             => array(
                     'label'         => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['uploader'],
                     'default'       => 'be_gc_jumploader',
                     'inputType'     => 'select',
                     'load_callback' => array(array('tl_gallery_creator_albums', 'loadCbGetUploader')),
                     'save_callback' => array(array('tl_gallery_creator_albums', 'saveCbSaveUploader')),
                     'options'       => array('be_gc_jumploader', 'be_gc_html5_uploader'),
                     'eval'          => array('doNotShow' => true, 'tl_class' => 'clr', 'submitOnChange' => true),
                     'sql'           => "varchar(32) NOT NULL default 'be_gc_jumploader'"
              ),
              // save value in tl_user
              'img_resolution'       => array(
                     'label'         => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_resolution'],
                     'default'       => '600',
                     'inputType'     => 'select',
                     'load_callback' => array(array('tl_gallery_creator_albums', 'loadCbGetImageResolution')),
                     'save_callback' => array(array('tl_gallery_creator_albums', 'saveCbSaveImageResolution')),
                     'options'       => array_merge(array('no_scaling'), range(100, 3500, 50)),
                     'reference'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['reference'],
                     'eval'          => array('doNotShow' => true, 'tl_class' => 'w50', 'submitOnChange' => true),
                     'sql'           => "smallint(5) unsigned NOT NULL default '600'"
              ),
              // save value in tl_user
              'img_quality'          => array(
                     'label'         => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['img_quality'],
                     'default'       => '100',
                     'inputType'     => 'select',
                     'load_callback' => array(array('tl_gallery_creator_albums', 'loadCbGetImageQuality')),
                     'save_callback' => array(array('tl_gallery_creator_albums', 'saveCbSaveImageQuality')),
                     'options'       => range(10, 100, 10),
                     'eval'          => array('doNotShow' => true, 'tl_class' => 'w50', 'submitOnChange' => true),
                     'sql'           => "smallint(3) unsigned NOT NULL default '100'"
              ),
              'preserve_filename'    => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['preserve_filename'],
                     'inputType' => 'checkbox',
                     'default'   => true,
                     'eval'      => array('doNotShow' => true, 'submitOnChange' => true),
                     'sql'       => "char(1) NOT NULL default ''"
              ),
              'multiSRC'             => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_content']['multiSRC'],
                     'exclude'   => true,
                     'inputType' => 'fileTree',
                     'eval'      => array('doNotShow' => true, 'multiple' => true, 'fieldType' => 'checkbox', 'files' => true, 'mandatory' => true),
                     'sql'       => "blob NULL"
              ),
              'protected'            => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['protected'],
                     'exclude'   => true,
                     'inputType' => 'checkbox',
                     'eval'      => array('doNotShow' => true, 'submitOnChange' => true, 'tl_class' => 'clr'),
                     'sql'       => "char(1) NOT NULL default ''"
              ),
              'groups'               => array(
                     'label'      => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['groups'],
                     'inputType'  => 'checkbox',
                     'foreignKey' => 'tl_member_group.name',
                     'eval'       => array('doNotShow' => true, 'mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'),
                     'sql'        => "blob NULL"
              ),
              'insert_article_pre'   => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['insert_article_pre'],
                     'inputType' => 'text',
                     'eval'      => array('doNotShow' => false, 'rgxp' => 'digit', 'tl_class' => 'w50',),
                     'sql'       => "int(10) unsigned NOT NULL default '0'",
              ),
              'insert_article_post'  => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['insert_article_post'],
                     'inputType' => 'text',
                     'eval'      => array('doNotShow' => false, 'rgxp' => 'digit', 'tl_class' => 'w50',),
                     'sql'       => "int(10) unsigned NOT NULL default '0'",
              ),
              'clean_db'             => array(
                     'input_field_callback' => array('tl_gallery_creator_albums', 'inputFieldCbCleanDb'),
                     'eval'                 => array('doNotShow' => true)
              ),
              'visitors_details'     => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['visitors_details'],
                     'inputType' => 'textarea',
                     'sql'       => "blob NULL"
              ),
              'visitors'             => array(
                     'label'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['visitors'],
                     'inputType' => 'text',
                     'eval'      => array('maxlength' => 10, 'tl_class' => 'w50', 'rgxp' => 'digit'),
                     'sql'       => "int(10) unsigned NOT NULL default '0'"
              ),
              'sortBy'               => array(
                     'label'         => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['sortBy'],
                     'exclude'       => true,
                     'save_callback' => array(array('tl_gallery_creator_albums', 'saveCbSortAlbum')),
                     'inputType'     => 'select',
                     'default'       => 'custom',
                     'options'       => array('custom', 'name_asc', 'name_desc', 'date_asc', 'date_desc'),
                     'reference'     => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums'],
                     'eval'          => array('tl_class' => 'w50'),
                     'sql'           => "varchar(32) NOT NULL default ''"
              )
       )
);

/**
 * Class tl_gallery_creator_albums
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Marko Cupic
 * @author     Marko Cupic
 * @package    Controller
 */
class tl_gallery_creator_albums extends Backend
{

       public $restrictedUser = false;

       /**
        *  Pfad ab TL_ROOT ins Bildverzeichnis
        *
        * @var string
        */
       public $uploadPath;

       public function __construct()
       {

              parent::__construct();
              $this->import('BackendUser', 'User');
              $this->import('Files');

              // path to the gallery_creator upload-directory
              $this->uploadPath = GALLERY_CREATOR_UPLOAD_PATH;

              // register the parseBackendTemplate Hook
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
        *
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
              return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . ' style="margin-right:5px">' . Image::getHtml($icon, $label) . '</a>';
       }

       /**
        * Return the "toggle visibility" button
        *
        * @param array
        * @param string
        * @param string
        * @param string
        * @param string
        * @param string
        * @return string
        */
       public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
       {

              if (strlen(Input::get('tid')))
              {
                     $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));
                     $this->redirect($this->getReferer());
              }

              // Check permissions AFTER checking the tid, so hacking attempts are logged
              if (!$this->User->isAdmin && $row['owner'] != $this->User->id && !$GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
              {
                     return '';
              }

              $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

              if (!$row['published'])
              {
                     $icon = 'invisible.gif';
              }

              $this->Database->prepare("SELECT * FROM tl_gallery_creator_albums WHERE id=?")->limit(1)->execute($row['id']);

              if (!$this->User->isAdmin && $row['owner'] != $this->User->id && !$GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
              {
                     return Image::getHtml($icon) . ' ';
              }

              return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
       }

       /**
        * toggle visibility of a certain album
        *
        * @param integer
        * @param boolean
        */
       public function toggleVisibility($intId, $blnVisible)
       {


              $objAlbum = GalleryCreatorAlbumsModel::findByPk($intId);

              // Check permissions to publish
              if (!$this->User->isAdmin && $objAlbum->owner != $this->User->id && !$GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
              {
                     $this->log('Not enough permissions to publish/unpublish tl_gallery_creator_albums ID "' . $intId . '"', __METHOD__, TL_ERROR);
                     $this->redirect('contao/main.php?act=error');
              }

              $objVersions = new Versions('tl_gallery_creator_albums', $intId);
              $objVersions->initialize();

              // Trigger the save_callback
              if (is_array($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['published']['save_callback']))
              {
                     foreach ($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['published']['save_callback'] as $callback)
                     {
                            if (is_array($callback))
                            {
                                   $this->import($callback[0]);
                                   $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, $this);
                            }
                            elseif (is_callable($callback))
                            {
                                   $blnVisible = $callback($blnVisible, $this);
                            }
                     }
              }

              // Update the database
              $this->Database->prepare("UPDATE tl_gallery_creator_albums SET tstamp=" . time() . ", published='" . ($blnVisible ? 1 : '') . "' WHERE id=?")
                     ->execute($intId);

              $objVersions->create();
              $this->log('A new version of record "tl_gallery_creator_albums.id=' . $intId . '" has been created.', __METHOD__, TL_GENERAL);
       }

       /**
        * Return the cut-picture-button
        *
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
              $objAlb = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute($row['id']);
              return (($this->User->id == $objAlb->owner || $this->User->isAdmin || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) ? ' <a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : ' ' . Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ');
       }

       /**
        * Return the delete-button
        *
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

              $objAlb = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute($row['id']);
              return ($this->User->isAdmin || $this->User->id == $objAlb->owner || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection']) ? '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
       }

       /**
        * Return the edit-button
        *
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

              return '<a href="' . $this->addToUrl($href . '&id=' . $row['id'], 1) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
       }

       /**
        * Return the import-images button
        *
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
              return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a>';
       }

       /**
        * Return the paste-picture-button
        *
        * @param DataContainer $dc
        * @param $row
        * @param $table
        * @param $cr
        * @param bool $arrClipboard
        * @return string
        */
       public function buttonCbPastePicture(DataContainer $dc, $row, $table, $cr, $arrClipboard = false)
       {

              $disablePA = false;
              $disablePI = false;
              // Disable all buttons if there is a circular reference
              if ($this->User->isAdmin && $arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'],
                                                                                                                                                                                                                      $arrClipboard['id'])))
              )
              {
                     $disablePA = true;
                     $disablePI = true;
              }
              // Return the buttons
              $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']), 'class="blink"');
              $imagePasteInto = Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']), 'class="blink"');

              if ($row['id'] > 0)
              {
                     $return = $disablePA ? Image::getHtml('pasteafter_.gif', '', 'class="blink"') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&mode=1&pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteAfter . '</a> ';
              }

              return $return . ($disablePI ? Image::getHtml('pasteinto_.gif', '', 'class="blink"') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&mode=2&pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset();">' . $imagePasteInto . '</a> ');
       }

       /**
        * Checks if the current user obtains full rights or only restricted rights on the selected album
        */
       public function checkUserRole()
       {

              $objUser = $this->Database->prepare('SELECT owner FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute(Input::get('id'));
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
              // ...so the current user is the album owner
              $this->restrictedUser = false;
       }

       /**
        * return the level of an album or subalbum (level_0, level_1, level_2,...)
        *
        * @param integer
        * @return integer
        */
       private function getLevel($pid)
       {

              $level = 0;
              if ($pid == '0')
              {
                     return $level;
              }
              $hasParent = true;
              while ($hasParent)
              {
                     $level++;
                     $mysql = $this->Database->prepare('SELECT pid FROM tl_gallery_creator_albums WHERE id=?')->execute($pid);
                     if ($mysql->pid < 1)
                     {
                            $hasParent = false;
                     }
                     $pid = $mysql->pid;
              }
              return $level;
       }

       /**
        * return the album upload path
        *
        * @return string
        */
       public static function getUplaodPath()
       {

              return self::uploadPath;
       }

       /**
        * Input-field-callback
        * return the html
        *
        * @return string
        */
       public function inputFieldCbCleanDb()
       {

              $output = '
<div class="clean_db">
<br><br>
       	<input type="checkbox" name="clean_db">
		<label for="clean_db">' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['messages']['revise_database'] . '</label>
</div>
			';
              return $output;
       }

       /**
        * Input-field-callback
        * return the html-table with the album-information for restricted users
        *
        * @return string
        */
       public function inputFieldCbGenerateAlbumInformations()
       {

              $objAlb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')
                     ->execute(Input::get('id'));
              $objUser = $this->Database->prepare('SELECT name FROM tl_user WHERE id=?')->execute($objAlb->owner);
              // check User Role
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
		<td>' . Date::parse("Y-m-d", $objAlb->date) . '</td>
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
        * return the markup for the fileuploader
        *
        * @return string
        */
       public function inputFieldCbGenerateUploaderMarkup()
       {

              return GalleryCreator\GcHelpers::generateUploader(Input::get('id'), $this->User->gc_be_uploader_template);
       }

       /**
        * check if album has subalbums
        *
        * @param integer
        * @return bool
        */
       private function isNode($id)
       {

              $mysql = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums WHERE pid=?')->execute($id);
              if ($mysql->numRows > 0)
              {
                     return true;
              }
              return false;
       }

       /**
        * label-callback for the albumlisting
        *
        * @param array
        * @param string
        * @return string
        */
       public function labelCb($row, $label)
       {

              $mysql = $this->Database->prepare('SELECT count(id) as countImg FROM tl_gallery_creator_pictures WHERE pid=?')
                     ->execute($row['id']);
              $label = str_replace('#count_pics#', $mysql->countImg, $label);
              $label = str_replace('#datum#', date('Y-m-d', $row['date']), $label);
              $image = $row['published'] ? 'picture_edit.png' : 'picture_edit_1.png';
              $label = str_replace('#icon#', "system/modules/gallery_creator/assets/images/" . $image, $label);
              $href = sprintf("contao/main.php?do=gallery_creator&table=tl_gallery_creator_albums&id=%s&act=edit&rt=%s&ref=%s", $row['id'], REQUEST_TOKEN, TL_REFERER_ID);
              $label = str_replace('#href#', $href, $label);
              $label = str_replace('#title#', sprintf($GLOBALS['TL_LANG']['tl_gallery_creator_albums']['edit_album'][1], $row['id']), $label);
              $padding = $this->isNode($row["id"]) ? 3 * $this->getLevel($row["pid"]) : 20 + (3 * $this->getLevel($row["pid"]));
              $label = str_replace('#padding-left#', 'padding-left:' . $padding . 'px;', $label);
              return $label;
       }

       /**
        * load-callback for uploader type
        *
        * @return string
        */
       public function loadCbGetUploader()
       {

              return $this->User->gc_be_uploader_template;
       }

       /**
        * load-callback for image-quality
        *
        * @return string
        */
       public function loadCbGetImageQuality()
       {

              return $this->User->gc_img_quality;
       }

       /**
        * load-callback for image-resolution
        *
        * @return string
        */
       public function loadCbGetImageResolution()
       {

              return $this->User->gc_img_resolution;
       }

       /**
        * Parse Backend Template Hook
        *
        * @param string
        * @param string
        * @return string
        */
       public function myParseBackendTemplate($strContent, $strTemplate)
       {

              if (Input::get('mode') == 'clean_db')
              {
                     // remove buttons
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
              }
              if (Input::get('act') == 'select')
              {
                     // remove buttons
                     if (Input::get('table') != 'tl_gallery_creator_pictures')
                     {
                            $strContent = preg_replace('/<input type=\"submit\" name=\"delete\"((\r|\n|.)+?)>/', '', $strContent);
                     }
                     $strContent = preg_replace('/<input type=\"submit\" name=\"cut\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"copy\"((\r|\n|.)+?)>/', '', $strContent);
              }
              if (Input::get('mode') == 'fileupload')
              {
                     // form encode
                     $strContent = str_replace('application/x-www-form-urlencoded', 'multipart/form-data', $strContent);
                     // remove buttons
                     $strContent = preg_replace('/<input type=\"submit\" name=\"save\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNclose\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);
                     $strContent = preg_replace('/<input type=\"submit\" name=\"uploadNback\"((\r|\n|.)+?)>/', '', $strContent);
              }
              if (Input::get('mode') == 'import_images')
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

              if (Input::get('act') != 'deleteAll')
              {
                     $this->checkUserRole();
                     if ($this->restrictedUser)
                     {
                            $this->log('Datensatz mit ID ' . Input::get('id') . ' wurde von einem nicht authorisierten Benutzer versucht aus tl_gallery_creator_albums zu loeschen.', __METHOD__, TL_ERROR);
                            $this->redirect('contao/main.php?do=error');
                     }
                     // also delete the child element
                     $arrDeletedAlbums = GalleryCreator\GcHelpers::getAllSubalbums(Input::get('id'));
                     $arrDeletedAlbums = array_merge(array(Input::get('id')), $arrDeletedAlbums);
                     foreach ($arrDeletedAlbums as $idDelAlbum)
                     {
                            $objAlb = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($idDelAlbum);
                            if ($this->User->isAdmin || $objAlb->owner == $this->User->id || true === $GLOBALS['TL_CONFIG']['gc_disable_backend_edit_protection'])
                            {
                                   // remove all pictures from tl_gallery_creator_pictures
                                   $this->Database->prepare('DELETE FROM tl_gallery_creator_pictures WHERE pid=?')->execute($idDelAlbum);
                                   // remove the albums from tl_gallery_creator_albums
                                   $this->Database->prepare('DELETE FROM tl_gallery_creator_albums WHERE id=?')->execute($idDelAlbum);
                                   // remove the directory from the filesystem
                                   $oFolder = FilesModel::findByUuid($objAlb->assignedDir);
                                   if ($oFolder !== null)
                                   {
                                          $folder = new Folder($oFolder->path, true);
                                          $folder->delete();
                                   }
                            }
                            else
                            {
                                   // do not delete childalbums, which the user does not owns
                                   $this->Database->prepare('UPDATE tl_gallery_creator_albums SET pid=? WHERE id=?')->execute('0', $idDelAlbum);
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
              new Folder($this->uploadPath);
              Dbafs::addResource($this->uploadPath, false);
              if (!is_writable(TL_ROOT . '/' . $this->uploadPath))
              {
                     $_SESSION['TL_ERROR'][] = sprintf($GLOBALS['TL_LANG']['ERR']['dirNotWriteable'], $this->uploadPath);
              }

              // create upload directory for all albums and store the uuid in the album settings
              $objAlbum = $this->Database->execute('SELECT * FROM tl_gallery_creator_albums');
              while ($objAlbum->next())
              {
                     $ok = false;
                     $objFolder = FilesModel::findByUuid($objAlbum->assignedDir);
                     if ($objFolder !== null)
                     {
                            if (is_dir(TL_ROOT . '/' . $objFolder->path))
                            {
                                   $ok = true;
                            }
                     }
                     if ($ok === false)
                     {
                            new Folder($this->uploadPath . '/' . $objAlbum->alias);
                            Dbafs::addResource($this->uploadPath . '/' . $objAlbum->alias, false);
                            $objDir = FilesModel::findByPath($this->uploadPath . '/' . $objAlbum->alias);
                            $oAlbum = GalleryCreatorAlbumsModel::findByPk($objAlbum->id);
                            if ($oAlbum !== null)
                            {
                                   $oAlbum->assignedDir = $objDir->uuid;
                                   $oAlbum->save();
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

              if (Input::get('mode') != 'fileupload')
              {
                     return;
              }

              // Load language file
              $this->loadLanguageFile('tl_files');

              // Album ID
              $intAlbumId = Input::get('id');

              // Save uploaded files in $_FILES['file']
              $strName = 'file';

              // Get the album object
              $blnNoAlbum = false;
              $objAlb = GalleryCreatorAlbumsModel::findById($intAlbumId);
              if ($objAlb === null)
              {
                     Message::addError('Album with ID ' . $intAlbumId . ' does not exist.');
                     $blnNoAlbum = true;
              }

              // Check for a valid upload directory
              $blnNoUploadDir = false;
              $objUploadDir = FilesModel::findByUuid($objAlb->assignedDir);
              if ($objUploadDir === null || !is_dir(TL_ROOT . '/' . $objUploadDir->path))
              {
                     Message::addError('No upload directory defined in the album settings!');
                     $blnNoUploadDir = true;
              }

              // Exit if there is no upload or the upload directory is missing
              if (!is_array($_FILES[$strName]) || $blnNoUploadDir || $blnNoAlbum)
              {
                     return;
              }

              // Call the uploader script
              $arrUpload = GcHelpers::fileupload($intAlbumId, $strName);

              foreach ($arrUpload as $strFileSrc)
              {
                     // Add  new datarecords into tl_gallery_creator_pictures
                     GcHelpers::createNewImage($objAlb->id, $strFileSrc);
              }

              // Do not exit script if html5_uploader is selected and Javascript is disabled
              if (!Input::post('submit'))
              {
                     exit;
              }
       }

       /**
        * onload-callback
        * return an array with the ids of all gallery_creator content-elements where the album with the id "Input::get('id')" is selected
        *
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
              while ($objDb->next())
              {
                     $arrAlbumIds[] = $objDb->id;
              }
              foreach ($arrAlbumIds as $albumId)
              {
                     $arrGcContentElements = array();
                     $objDb = $this->Database->prepare('SELECT id, gc_publish_albums FROM tl_content WHERE type=?')->execute('gallery_creator');
                     while ($objDb->next())
                     {
                            $arrPublAlbums = $objDb->gc_publish_albums != "" ? deserialize($objDb->gc_publish_albums) : array();
                            if (in_array($albumId, $arrPublAlbums))
                            {
                                   $arrGcContentElements[] = $objDb->id;
                            }
                     }
                     // update tl_gallery_creator_albums.gc_articles
                     $arrGcContentElements = count($arrGcContentElements) > 0 ? serialize($arrGcContentElements) : '';
                     $this->Database->prepare('UPDATE tl_gallery_creator_albums SET displ_alb_in_this_ce=? WHERE id=?')->execute($arrGcContentElements, $albumId);
              }
       }

       /**
        * onload-callback
        * import images from an external directory to an existing album
        */
       public function onloadCbImportFromFilesystem()
       {

              if (Input::get('mode') != 'import_images')
              {
                     return;
              }
              // load language file
              $this->loadLanguageFile('tl_content');
              if (!$this->Input->post('FORM_SUBMIT'))
              {
                     return;
              }
              $blnPreserveFilename = Input::post('preserve_filename');
              $intAlbumId = Input::get('id');
              // comma separated list with folder uuid's => 10585872-5f1f-11e3-858a-0025900957c8,105e9de0-5f1f-11e3-858a-0025900957c8,105e9dd6-5f1f-11e3-858a-0025900957c8
              $strMultiSRC = $this->Input->post('multiSRC');
              if (strlen(trim($strMultiSRC)))
              {
                     $this->Database->prepare('UPDATE tl_gallery_creator_albums SET preserve_filename=? WHERE id=?')->execute($blnPreserveFilename, $intAlbumId);
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['preserve_filename']['eval']['submitOnChange'] = false;
                     // import Images from filesystem and write entries to tl_gallery_creator_pictures
                     GalleryCreator\GcHelpers::importFromFilesystem($intAlbumId, $strMultiSRC);
              }
              $this->redirect('contao/main.php?do=gallery_creator&table=tl_gallery_creator_pictures&id=' . $intAlbumId . '&ref=' . TL_REFERER_ID . '&filesImported=true');
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
              if (!$this->User->isAdmin)
              {
                     unset($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['all']);
                     unset($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['clean_db']);
              }
              // for security reasons give only readonly rights to these fields
              $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['id']['eval']['style'] = '" readonly="readonly';
              $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['owners_name']['eval']['style'] = '" readonly="readonly';
              // create the jumploader palette
              if (Input::get('mode') == 'fileupload')
              {
                     if ($this->User->gc_img_resolution == 'no_scaling')
                     {
                            $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['fileupload'] = str_replace(',img_quality', '', $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['fileupload']);
                     }
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['fileupload'];
                     return;
              }
              // create the import_images palette
              if (Input::get('mode') == 'import_images')
              {
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['import_images'];
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['fields']['preserve_filename']['eval']['submitOnChange'] = false;
                     return;
              }
              // the palette for admins
              if ($this->User->isAdmin)
              {
                     $objAlb = $this->Database->prepare('SELECT id FROM tl_gallery_creator_albums')->limit(1)->execute();
                     if ($objAlb->next())
                     {
                            $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['clean_db']['href'] = 'act=edit&table&mode=clean_db&id=' . $objAlb->id;
                     }
                     else
                     {
                            unset($GLOBALS['TL_DCA']['tl_gallery_creator_albums']['list']['global_operations']['clean_db']);
                     }
                     if (Input::get('mode') == 'clean_db')
                     {
                            if ($this->Input->post('FORM_SUBMIT') && $this->Input->post('clean_db'))
                            {
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
              if ($objAlb->owner != $this->User->id && true == $this->restrictedUser)
              {
                     $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['default'] = $GLOBALS['TL_DCA']['tl_gallery_creator_albums']['palettes']['restricted_user'];
              }
       }

       /**
        * displ_alb_in_this_ce  - options_callback
        *
        * @return array
        */
       public function optionsCbDisplAlbInThisContentElements()
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
        * Options Callback for the selection of the preview thumb
        * return an array with all filenames (imagenames) of specified album
        * @return string
        */
       public function inputFieldCbThumb()
       {

              $objAlbum = \GalleryCreator\GalleryCreatorAlbumsModel::findByPk(Input::get('id'));

              // Save input
              if (Input::post('FORM_SUBMIT') == 'tl_gallery_creator_albums')
              {
                     if (Input::post('thumb') == intval(Input::post('thumb')))
                     {
                            $objAlbum->thumb = Input::post('thumb');
                            $objAlbum->save();
                     }
              }

              // Generate picture list
              $html = '<div class="preview_thumb">';
              $html .= '<h3><label for="ctrl_thumb">' . $GLOBALS['TL_LANG']['tl_gallery_creator_albums']['thumb']['0'] . '</label></h3>';
              $html .= '<ul>';

              $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=? ORDER BY id')->execute(Input::get('id'));
              while ($objPicture->next())
              {
                     $oFile = FilesModel::findByUuid($objPicture->uuid);
                     if ($oFile !== null)
                     {
                            if (file_exists(TL_ROOT . '/' . $oFile->path))
                            {

                                   $src = Image::get($oFile->path, 80, 80, 'crop');
                                   $checked = $objAlbum->thumb == $objPicture->id ? ' checked' : '';
                                   $class = $checked != '' ? 'class="checked"' : '';
                                   $html .= '<li ' . $class . '><input type="radio" name="thumb" value="' . $objPicture->id . '"' . $checked . '>' . '<img src="' . $src . '" height="80" width="80" alt=""></li>' . "\r\n";
                            }
                     }
              }
              $arrSubalbums = GalleryCreator\GcHelpers::getAllSubalbums(Input::get('id'));
              if (count($arrSubalbums))
              {
                     foreach ($arrSubalbums as $albId)
                     {
                            $objPicture = $this->Database->prepare('SELECT * FROM tl_gallery_creator_pictures WHERE pid=? ORDER BY id')
                                   ->execute($albId);
                            while ($objPicture->next())
                            {
                                   $objAlbName = $this->Database->prepare('SELECT * FROM tl_gallery_creator_albums WHERE id=?')->execute($albId);
                                   while ($objPicture->next())
                                   {
                                          $oFile = FilesModel::findByUuid($objPicture->uuid);
                                          if ($oFile !== null)
                                          {
                                                 if (file_exists(TL_ROOT . '/' . $oFile->path))
                                                 {
                                                        $checked = $objAlbum->thumb == $objPicture->id ? ' checked' : '';
                                                        $class = $checked != '' ? 'class="checked"' : '';
                                                        $html .= '<li ' . $class . '><input type="radio" name="thumb" value="' . $objPicture->id . '"' . $checked . '>' . '<img src="' . $src . '" height="80" width="80" alt=""></li>' . "\r\n";
                                                 }
                                          }
                                   }
                            }
                     }
              }

              // Add javascript
              $script = '
<script>
       window.addEvent("domready", function() {
           $$(".preview_thumb li").addEvent("click", function(event){
              this.getChildren("input")[0].setProperty("checked","checked");
              $$(".preview_thumb .checked").removeClass("checked");
              this.addClass("checked");
           });
       });
</script>
';
              $html . '</ul>';
              $html .= '<div style="clear:both"></div>';
              $html .= '</div>';

              // Return html
              return $html . $script;
       }

       /**
        * sortBy  - save_callback
        *
        * @param $varValue
        * @param DataContainer $dc
        */
       public function saveCbSortAlbum($varValue, DataContainer $dc)
       {

              if ($varValue == 'custom')
              {
                     return $varValue;
              }

              $objPictures = GalleryCreatorPicturesModel::findByPid($dc->id);
              if ($objPictures === null)
              {
                     return 'custom';
              }

              $files = array();
              $auxDate = array();

              while ($objPictures->next())
              {
                     $oFile = FilesModel::findByUuid($objPictures->uuid);
                     $objFile = new \File($oFile->path, true);
                     $files[$oFile->path] = array(
                            'id' => $objPictures->id
                     );
                     $auxDate[] = $objFile->mtime;
              }

              switch ($varValue)
              {
                     case 'custom':
                            break;
                     case 'name_asc':
                            uksort($files, 'basename_natcasecmp');
                            break;
                     case 'name_desc':
                            uksort($files, 'basename_natcasercmp');
                            break;
                     case 'date_asc':
                            array_multisort($files, SORT_NUMERIC, $auxDate, SORT_ASC);
                            break;

                     case 'date_desc':
                            array_multisort($files, SORT_NUMERIC, $auxDate, SORT_DESC);
                            break;
              }

              $sorting = 0;
              foreach ($files as $arrFile)
              {
                     $sorting += 10;
                     $objPicture = GalleryCreatorPicturesModel::findByPk($arrFile['id']);
                     $objPicture->sorting = $sorting;
                     $objPicture->save();
              }
              // return default value
              return 'custom';
       }

       /**
        * generate an albumalias based on the albumname and create a directory of the same name
        * and register the directory in tl files
        *
        * @param $strAlias
        * @param DataContainer $dc
        * @return mixed|string
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
              $objAlbum = GalleryCreator\GalleryCreatorAlbumsModel::findByPk($dc->activeRecord->id);
              $strAlias = strlen($objAlbum->alias) ? $objAlbum->alias : $strAlias;

              // if a new album was created
              $createDir = true;
              $oFolder = FilesModel::findByUuid($objAlbum->assignedDir);
              if ($oFolder !== null)
              {
                     if (is_dir(TL_ROOT . '/' . $oFolder->path))
                     {
                            $createDir = false;
                     }
              }

              if ($createDir === true)
              {
                     // create the new folder and register it in tl_files
                     $objFolder = new Folder ($this->uploadPath . '/' . $strAlias);
                     Dbafs::addResource($objFolder->path, true);
                     $oFolder = FilesModel::findByPath($objFolder->path);
                     $objAlbum->assignedDir = $oFolder->uuid;
              }

              return $strAlias;
       }

       /**
        * displ_alb_in_this_ce  - save_callback
        *
        * @param $varValue
        * @param DataContainer $dc
        * @return mixed
        */
       public function saveCbDisplAlbInThisContentElements($varValue, DataContainer $dc)
       {

              $albumId = $dc->id;
              $arrSelectedElements = !$varValue ? array() : deserialize($varValue);

              $objContent = $this->Database->prepare('SELECT * FROM tl_content WHERE type=?')->execute('gallery_creator');
              // update tl_content.gc_publish_albums in each gallery_creator content element
              while ($objContent->next())
              {

                     $arrPublAlbums = is_array($objContent->gc_publish_albums) ? $objContent->gc_publish_albums : deserialize($objContent->gc_publish_albums);
                     $arrPublAlbums = count($arrPublAlbums) > 0 ? $arrPublAlbums : array();
                     if (in_array($objContent->id, $arrSelectedElements))
                     {
                            // add to list
                            $arrPublAlbums[] = $albumId;
                     }
                     else
                     {

                            // remove from list
                            if (count($arrPublAlbums))
                            {
                                   if (array_search($albumId, $arrPublAlbums) !== false)
                                   {
                                          unset($arrPublAlbums[array_search($albumId, $arrPublAlbums)]);
                                          $arrPublAlbums = array_values($arrPublAlbums);
                                   }
                            }
                     }
                     $arrPublAlbums = array_unique($arrPublAlbums);
                     if (count($arrPublAlbums))
                     {

                            // set the new album in a correct order
                            $query = sprintf('SELECT id FROM tl_gallery_creator_albums WHERE id IN(%s) ORDER BY %s %s',
                                             implode(',', $arrPublAlbums), $objContent->gc_sorting, $objContent->gc_sorting_direction);
                            $objAlbum = $this->Database->execute($query);
                            if ($objAlbum->numRows)
                            {
                                   $arrPublAlbums = $objAlbum->fetchEach('id');
                            }
                     }

                     $this->Database->prepare('UPDATE tl_content SET gc_publish_albums=? WHERE id=?')->execute(serialize($arrPublAlbums), $objContent->id);
                     $this->log('A new version of record "tl_content.id=' . $objContent->id . '" has been created', __METHOD__, GENERAL);
              }
              return $varValue;
       }

       /**
        * save_callback for the uploader
        *
        * @param $value
        */
       public function saveCbSaveUploader($value)
       {

              $this->Database->prepare('UPDATE tl_user SET gc_be_uploader_template=? WHERE id=?')->execute($value, $this->User->id);
       }

       /**
        * save_callback for the image quality above the jumploader applet
        *
        * @param $value
        */
       public function saveCbSaveImageQuality($value)
       {

              $this->Database->prepare('UPDATE tl_user SET gc_img_quality=? WHERE id=?')->execute($value, $this->User->id);
       }

       /**
        * save_callback for the image resolution above the jumploader applet
        *
        * @param $value
        */
       public function saveCbSaveImageResolution($value)
       {

              $this->Database->prepare('UPDATE tl_user SET gc_img_resolution=? WHERE id=?')->execute($value, $this->User->id);
       }
}