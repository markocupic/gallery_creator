<?php

/**
 * Gallery Creator for Contao Open Source CMS
 *
 * Copyright (C) 2008-2018 Marko Cupic
 *
 * @package    Galery Creator
 * @link       https://github.com/markocupic/gallery_creator/
 * @license    https://opensource.org/licenses/lgpl-3.0.html LGPL
 */

use Contao\GalleryCreatorAlbumsModel;
use Contao\GalleryCreatorPicturesModel;
use Markocupic\GalleryCreator\ModuleGalleryCreator;
use Contao\BackendUser;
use Contao\Input;
use Contao\Image;
use Contao\FilesModel;
use Contao\Dbafs;
use Contao\Versions;
use Contao\File;


$GLOBALS['TL_DCA']['tl_gallery_creator_pictures'] = array(
    // Config
    'config' => array(
        'ptable' => 'tl_gallery_creator_albums',
        'enableVersioning' => true,
        'dataContainer' => 'Table',
        'onload_callback' => array(
            array(
                'tl_gallery_creator_pictures',
                'checkPermission',
            ),
        ),
        'ondelete_callback' => array(
            array(
                'tl_gallery_creator_pictures',
                'ondeleteCb',
            ),
        ),
        'oncut_callback' => array(
            array(
                'tl_gallery_creator_pictures',
                'oncutCb',
            ),
        ),
        'sql' => array(
            'keys' => array(
                'id' => 'primary',
                'pid' => 'index',
                'uuid' => 'index',
            ),
        ),
    ),
    //list
    'list' => array(
        'sorting' => array(
            'mode' => 4,
            'fields' => array('sorting'),
            'panelLayout' => 'filter;search,limit',
            'headerFields' => array('id', 'date', 'owners_name', 'name', 'comment', 'thumb'),
            'child_record_callback' => array('tl_gallery_creator_pictures', 'childRecordCb'),
        ),
        'global_operations' => array(
            'fileupload' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['fileupload'],
                'href' => 'act=edit&table=tl_gallery_creator_albums&mode=fileupload',
                'class' => 'icon_image_add',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ),
            'all' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ),
        ),
        'operations' => array(
            'edit' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ),
            'delete' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['gcDeleteConfirmPicture'] . '\')) return false; Backend.getScrollOffset();"',
                'button_callback' => array('tl_gallery_creator_pictures', 'buttonCbDeletePicture'),
            ),
            'cut' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
            ),
            'imagerotate' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['imagerotate'],
                'href' => 'mode=imagerotate',
                'icon' => 'system/modules/gallery_creator/assets/images/arrow_rotate_clockwise.png',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
                'button_callback' => array('tl_gallery_creator_pictures', 'buttonCbRotateImage'),
            ),
            'toggle' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_albums']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_gallery_creator_pictures', 'toggleIcon'),
            ),
        ),
    ),
    // Palettes
    'palettes' => array(
        '__selector__' => array('addCustomThumb'),
        'default' => 'published,picture,owner,date,image_info,addCustomThumb,title,comment;{media_integration:hide},socialMediaSRC,localMediaSRC;{expert_legend:hide},cssID',
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
            'relation' => array('type' => 'belongsTo', 'load' => 'lazy'),
            'eval' => array('doNotShow' => true),
        ),
        'path' => array('sql' => "varchar(255) NOT NULL default ''"),
        'uuid' => array('sql' => "binary(16) NULL"),
        'sorting' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
        'tstamp' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
        'published' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['published'],
            'inputType' => 'checkbox',
            'filter' => true,
            'eval' => array('isBoolean' => true, 'submitOnChange' => true, 'tl_class' => 'long'),
            'sql' => "char(1) NOT NULL default '1'",
        ),
        'image_info' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['image_info'],
            'input_field_callback' => array('tl_gallery_creator_pictures', 'inputFieldCbGenerateImageInformation'),
            'eval' => array('tl_class' => 'clr',),
        ),
        'title' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['title'],
            'exclude' => true,
            'inputType' => 'text',
            'filter' => true,
            'search' => true,
            'eval' => array('maxlength' => 255),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
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
            'eval' => array('decodeEntities' => true, 'tl_class' => 'clr'),
            'sql' => "text NULL",
        ),
        'picture' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['picture'],
            'input_field_callback' => array('tl_gallery_creator_pictures', 'inputFieldCbGenerateImage'),
            'eval' => array('tl_class' => 'clr'),
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
                'datepicker' => true,
                'rgxp' => 'date',
                'tl_class' => 'clr wizard ',
                'submitOnChange' => false
            ),
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'addCustomThumb' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['addCustomThumb'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => array('submitOnChange' => true,),
            'sql' => "char(1) NOT NULL default ''",
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
            'sql' => "blob NULL",
        ),
        'owner' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['owner'],
            'default' => BackendUser::getInstance()->id,
            'foreignKey' => 'tl_user.name',
            'inputType' => 'select',
            'filter' => true,
            'search' => true,
            'eval' => array(
                'includeBlankOption' => true,
                'blankOptionLabel' => 'noName',
                'doNotShow' => true,
                'nospace' => true,
                'tl_class' => 'clr w50'
            ),
            'sql' => "int(10) NOT NULL default '0'",
            'relation' => array('type' => 'hasOne', 'load' => 'eager'),
        ),
        'socialMediaSRC' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['socialMediaSRC'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('tl_class' => 'clr'),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
        'localMediaSRC' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['localMediaSRC'],
            'exclude' => true,
            'filter' => true,
            'search' => true,
            'inputType' => 'fileTree',
            'eval' => array('files' => true, 'filesOnly' => true, 'fieldType' => 'radio'),
            'sql' => "binary(16) NULL",
        ),
        'cssID' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['cssID'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => array('multiple' => true, 'size' => 2, 'tl_class' => 'w50 clr'),
            'sql' => "varchar(255) NOT NULL default ''",
        ),
    ),
);


/**
 * Class tl_gallery_creator_pictures
 */
class tl_gallery_creator_pictures extends Backend
{

    /**
     * tl_gallery_creator_pictures constructor.
     */
    public function __construct()
    {

        parent::__construct();

        $this->import('BackendUser', 'User');
        $this->import('Files');
        $this->import('Session');



        // Set the referer when redirecting from import files from the filesystem
        if (Input::get('filesImported'))
        {
            $session = $this->Session->get('referer');
            $session[TL_REFERER_ID]['current'] = 'contao/main.php?do=gallery_creator';
            $this->Session->set('referer', $session);
        }


        // Imagerotate
        if (Input::get('mode') == 'imagerotate')
        {
            $objPic = GalleryCreatorPicturesModel::findById(Input::get('imgId'));
            $objFile = FilesModel::findByUuid($objPic->uuid);
            if ($objFile !== null)
            {
                // Rotate image anticlockwise
                $angle = 270;
                ModuleGalleryCreator::imageRotate($objFile->path, $angle);
                Dbafs::addResource($objFile->path, true);
                $this->redirect($this->getReferer());
            }
        }


        // Get the source album id when moving pictures to an other album
        // Save the album id in the session an use it in the oncut callback
        if (Input::get('act') == 'paste' && Input::get('mode') == 'cut')
        {
            $objPicture = GalleryCreatorPicturesModel::findByPk(Input::get('id'));
            if ($objPicture !== null)
            {
                $session = $this->Session->get('gallery_creator');
                $session['onCutAlbumSourceId'] = $objPicture->pid;
                $this->Session->set('gallery_creator', $session);
            }
        }
        if (Input::get('act') == 'select')
        {
            $session = $this->Session->get('gallery_creator');
            $session['onCutAlbumSourceId'] = Input::get('id');
            $this->Session->set('gallery_creator', $session);
        }


		// Parse Backend Template Hook
		$GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('tl_gallery_creator_pictures', 'parseBackendTemplate');
    }

    /**
     * Check permissions to edit table tl_gallery_creator_pictures
     */
    public function checkPermission()
    {

        // Check current action
        switch (Input::get('act'))
        {
            case 'copy':
            case 'copyAll':
            case 'create':
                // Create new items by uploading an image file
                $this->log('Create gallery-creator-picture by uploading an image. Not enough permissions to create gallery-creator pictures in album ID "' . Input::get('pid') . '"',
                    __METHOD__, TL_ERROR);
                $this->redirect('contao/main.php?act=error');
                break;
            default:
                //
                break;
        }


        if ($this->User->isAdmin)
        {
            return;
        }

        // Set the root IDs
        if (!is_array($this->User->gallery_creator) || empty($this->User->gallery_creator))
        {
            $allowedAlbums = array(0);
        }
        else
        {
            $objAlbum = $this->Database->execute('SELECT * FROM tl_gallery_creator_albums WHERE pid IN(' . implode(',',
                    $this->User->gallery_creator) . ')');
            if ($objAlbum->numRows)
            {
                $allowedAlbums = $objAlbum->fetchEach('id');
            }
            else
            {
                $allowedAlbums = array(0);
            }
        }


        $id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

        // Check current action
        switch (Input::get('act'))
        {
            case 'paste':
                // Allow
                break;

            case 'cut':
            case 'edit':
            case 'delete':
            case 'toggle':
                //case 'feature':
                $objPicture = $this->Database->prepare("SELECT pid FROM tl_gallery_creator_pictures WHERE id=?")->limit(1)->execute($id);

                if ($objPicture->numRows < 1)
                {
                    $this->log('Invalid gallery-creator-picture-item ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                if (!in_array($objPicture->pid, $allowedAlbums))
                {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' news item ID "' . $id . '" of news archive ID "' . $objPicture->pid . '"',
                        __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
                if (!in_array($id, $allowedAlbums))
                {
                    $this->log('Not enough permissions to access album ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                $objPicture = $this->Database->prepare("SELECT id FROM tl_gallery_creator_pictures WHERE pid=?")->execute($id);

                if ($objPicture->numRows < 1)
                {
                    $this->log('Invalid album archive ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                $session = $this->Session->getData();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objPicture->fetchEach('id'));
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act')))
                {
                    $this->log('Invalid command "' . Input::get('act') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                elseif (!in_array($id, $allowedAlbums))
                {
                    $this->log('Not enough permissions to access gallery-creator-picture ID ' . $id, __METHOD__,
                        TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function buttonCbDeletePicture($row, $href, $label, $title, $icon, $attributes)
    {
        return '<a href="' . $this->addToUrl($href . '&id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon,
                $label) . '</a> ';
    }


    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function buttonCbRotateImage($row, $href, $label, $title, $icon, $attributes)
    {

        return '<a href="' . $this->addToUrl($href . '&imgId=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon,
                $label) . '</a> ';
    }

    /**
     * @param $arrRow
     * @return string
     */
    public function childRecordCb($arrRow)
    {

        $key = ($arrRow['published'] == '1') ? 'published' : 'unpublished';

        $oFile = FilesModel::findByUuid($arrRow['uuid']);
        if (!is_file(TL_ROOT . "/" . $oFile->path))
        {
            return "";
        }

        $objFile = new File($oFile->path);
        if ($objFile->isGdImage)
        {
            // If datarecord contains a link to a movie file...
            $hasMovie = null;
            $src = $objFile->path;
            $src = trim($arrRow['socialMediaSRC']) != "" ? trim($arrRow['socialMediaSRC']) : $src;

            // local media (movies, etc.)
            if (Validator::isUuid($arrRow['localMediaSRC']))
            {
                $lmSRC = FilesModel::findByUuid($arrRow['localMediaSRC']);
                if ($lmSRC !== null)
                {
                    $src = $lmSRC->path;
                }
            }

            if (trim($arrRow['socialMediaSRC']) != "" or $lmSRC !== null)
            {
                $type = trim($arrRow['localMediaSRC']) == "" ? ' embeded local-media: ' : ' embeded social media: ';
                $iconSrc = 'system/modules/gallery_creator/assets/images/film.png';
                $movieIcon = Image::getHtml($iconSrc);
                $hasMovie = sprintf('<div class="block">%s%s<a href="%s" data-lightbox="gc_album_%s">%s</a></div>',
                    $movieIcon, $type, $src, Input::get('id'), $src);
            }
            $blnShowThumb = false;
            $src = '';
            // Generate icon/thumbnail
            if ($GLOBALS['TL_CONFIG']['thumbnails'] && $oFile !== null)
            {
                $src = Image::get($oFile->path, "100", "", "center_center");
                $blnShowThumb = true;
            }
            // Return html
            $return = sprintf('<div class="cte_type %s"><strong>%s</strong> - %s [%s x %s px, %s]</div>', $key,
                $arrRow['headline'], basename($oFile->path), $objFile->width, $objFile->height,
                $this->getReadableSize($objFile->filesize));
            $return .= $hasMovie;
            $return .= $blnShowThumb ? '<div class="block"><img src="' . $src . '" width="100"></div>' : null;
            $return .= sprintf('<div class="limit_height%s block">%s</div>',
                ($GLOBALS['TL_CONFIG']['thumbnails'] ? ' h64' : ''), specialchars($arrRow['comment']));
            return $return;
        }
        return '';
    }

    /**
     * @param DC_Table $dc
     */
    public function onCutCb(DC_Table $dc)
    {

        $session = $this->Session->get('gallery_creator');
        if (!isset($session['onCutAlbumSourceId']) || empty($session['onCutAlbumSourceId']))
        {
            return;
        }
        $sourceAlbumId = $session['onCutAlbumSourceId'];


        // Get sourceAlbumObject
        $objSourceAlbum = GalleryCreatorAlbumsModel::findByPk($sourceAlbumId);

        // Get pictureToMoveObject
        $objPictureToMove = GalleryCreatorPicturesModel::findByPk($dc->id);
        if ($objSourceAlbum === null || $objPictureToMove === null)
        {
            return;
        }

        if (Input::get('mode') == '1')
        {
            // Paste after existing file
            $objTargetAlbum = GalleryCreatorPicturesModel::findByPk(Input::get('pid'))->getRelated('pid');
        }
        elseif (Input::get('mode') == '2')
        {
            // Paste on top
            $objTargetAlbum = GalleryCreatorAlbumsModel::findByPk(Input::get('pid'));
        }

        if ($objTargetAlbum === null)
        {
            return;
        }

        if ($objSourceAlbum->id == $objTargetAlbum->id)
        {
            return;
        }

        $objFile = FilesModel::findByUuid($objPictureToMove->uuid);
        $objTargetFolder = FilesModel::findByUuid($objTargetAlbum->assignedDir);
        $objSourceFolder = FilesModel::findByUuid($objSourceAlbum->assignedDir);

        if ($objFile === null || $objTargetFolder === null || $objSourceFolder === null)
        {
            return;
        }

        // Return if it is an external file
        if (false === strpos($objFile->path, $objSourceFolder->path))
        {
            return;
        }

        $strDestination = $objTargetFolder->path . '/' . basename($objFile->path);
        if ($strDestination != $objFile->path)
        {
            $oFile = new File($objFile->path);
            // Move file to the target folder
            if ($oFile->renameTo($strDestination))
            {
                $objPictureToMove->path = $strDestination;
                $objPictureToMove->save();
            }
        }
    }

    /**
     * @param DataContainer $dc
     * @return string
     */
    public function inputFieldCbGenerateImage(DataContainer $dc)
    {
        $objImg = GalleryCreatorPicturesModel::findByPk($dc->id);
        $oFile = FilesModel::findByUuid($objImg->uuid);
        if ($oFile !== null)
        {
            $src = $oFile->path;
            $basename = basename($oFile->path);
            return '
                     <div style="height:auto;">
                         <h3><label for="ctrl_picture">' . $basename . '</label></h3>
                         <img src="' . Image::get($src, '380', '', 'proportional') . '" style="max-width:100%; max-height:300px;">
                     </div>
		             ';
        }
        return '';
    }

    /**
     * @param DataContainer $dc
     * @return string
     */
    public function inputFieldCbGenerateImageInformation(DataContainer $dc)
    {

        $objImg = GalleryCreatorPicturesModel::findByPk($dc->id);
        $oFile = FilesModel::findByUuid($objImg->uuid);

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
					<td>' . $oFile->path . '</td>
				</tr>

				<tr class="odd">
					<td><strong>' . $GLOBALS['TL_LANG']['tl_gallery_creator_pictures']['filename'][0] . ': </strong></td>
					<td>' . basename($oFile->path) . '</td>
				</tr></table>
			</div>';
        return $output;
    }

    /**
     * @param $strContent
     * @param $strTemplate
     * @return mixed
     */
    public function parseBackendTemplate($strContent, $strTemplate)
    {

        if (Input::get('table') == 'tl_gallery_creator_pictures')
        {
            // Insert new pictres via fileupload or file import, the create buttons are obsolete
            // Remove create button from the global operations
            $pattern = '|<a href="[^"]*tl_gallery_creator_pictures[^"]*mode=create[^"]*"[^>]*></a>|Usi';
            $strContent = preg_replace($pattern, '', $strContent);

            // Remove create button from the operations
            $pattern = '|<a href="[^"]*tl_gallery_creator_pictures[^"]*act=create[^"]*"[^>]*><img[^>]*></a>|Usi';
            $strContent = preg_replace($pattern, '', $strContent);

            //Bei einigen Browsern überragt die textarea den unteren Seitenrand, deshalb eine weitere leere clearing-box
            //$strContent = str_replace('</fieldset>', '<div class="clr" style="clear:both"><p> </p><!-- clearing Box --></div></fieldset>', $strContent);
        }

        if (Input::get('table') == 'tl_gallery_creator_pictures' && Input::get('act') == 'select')
        {
            // Remove saveNcreate button
            $strContent = preg_replace('/<input type=\"submit\" name=\"saveNcreate\"((\r|\n|.)+?)>/', '', $strContent);

            // remove copy button
            $strContent = preg_replace('/<input type="submit" name="copy"(.*?)>/', '', $strContent);

        }
        return $strContent;
    }

    /**
     * @param \Contao\DC_Table $dc
     */
    public function ondeleteCb(\Contao\DC_Table $dc)
    {

        $objImg = GalleryCreatorPicturesModel::findByPk($dc->id);
        $pid = $objImg->pid;

        // Delete the datarecord from tl_files
        $uuid = $objImg->uuid;
        $objImg->delete();


        // Do only delete image from filesystem if it isn't assigned to an other album
        $objPictureModel = GalleryCreatorPicturesModel::findByUuid($uuid);
        if ($objPictureModel === null)
        {
            // Wenn nein darf gelöscht werden...
            $oFile = FilesModel::findByUuid($uuid);

            $objAlbum = GalleryCreatorAlbumsModel::findByPk($pid);
            $oFolder = FilesModel::findByUuid($objAlbum->assignedDir);

            // Delete image only if it is stored in the assigned album directory
            // Do not delete exteranl pictures
            if ($oFile !== null && strstr($oFile->path, $oFolder->path))
            {
                // delete file from filesystem
                $file = new File($oFile->path, true);
                $file->delete();
            }
        }

    }

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_gallery_creator_pictures::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon,
                $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }

    /**
     * @param $intId
     * @param $blnVisible
     * @param DataContainer|null $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc)
        {
            $dc->id = $intId; // see #8043
        }

        $this->checkPermission();

        // Check the field access
        if (!$this->User->hasAccess('tl_gallery_creator_pictures::published', 'alexf'))
        {
            $this->log('Not enough permissions to publish/unpublish gallery-creator picture item ID "' . $intId . '"',
                __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }
        $objVersions = new Versions('tl_gallery_creator_pictures', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_gallery_creator_pictures']['fields']['published']['save_callback']))
        {
            foreach ($GLOBALS['TL_DCA']['tl_gallery_creator_pictures']['fields']['published']['save_callback'] as $callback)
            {
                if (is_array($callback))
                {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                }
                elseif (is_callable($callback))
                {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_gallery_creator_pictures SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);

        $objVersions->create();

    }
}
