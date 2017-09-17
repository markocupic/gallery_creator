<?php

use Contao\Input;
use Contao\Image;


$this->import('BackendUser', 'User');

/**
 * Table tl_gallery_creator_albums
 */
/**
 * Table tl_gallery_creator_gallery
 */
$GLOBALS['TL_DCA']['tl_gallery_creator_galleries'] = array
(

    // Config
    'config' => array
    (
        'dataContainer' => 'Table',
        'ctable' => array('tl_gallery_creator_albums'),
        'switchToEdit' => true,
        'enableVersioning' => true,
        'onload_callback' => array
        (
            array('tl_gallery_creator_galleries', 'checkPermission'),
            array('tl_gallery_creator_galleries', 'onAjax'),
            array('tl_gallery_creator_galleries', 'setUpPalettes'),

        ),
        'onsubmit_callback' => array
        (//
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode' => 1,
            'fields' => array('title'),
            'flag' => 1,
            'panelLayout' => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields' => array('title'),
            'format' => '%s'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['edit'],
                'href' => 'table=tl_gallery_creator_albums',
                'icon' => 'edit.gif'
            ),
            'editheader' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif',
                'button_callback' => array('tl_gallery_creator_galleries', 'editHeader')
            ),
            'copy' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif',
                'button_callback' => array('tl_gallery_creator_galleries', 'copyGallery')
            ),
            'delete' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
                'button_callback' => array('tl_gallery_creator_galleries', 'deleteGallery')
            ),
            'show' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__' => array('protected'),
        'default' => '{title_legend},title,jumpTo;{protected_legend:hide},protected;{comments_legend:hide},allowComments',
    ),

    // Subpalettes
    'subpalettes' => array
    (
        'protected' => 'groups'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'maxlength' => 255),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'jumpTo' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['jumpTo'],
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => array('mandatory' => true, 'fieldType' => 'radio'),
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => array('type' => 'hasOne', 'load' => 'eager')
        ),
        'protected' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['protected'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => array('submitOnChange' => true),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'groups' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_gallery_creator_galleries']['groups'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => array('mandatory' => true, 'multiple' => true),
            'sql' => "blob NULL",
            'relation' => array('type' => 'hasMany', 'load' => 'lazy')
        )

    )
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Leo Feyer <https://github.com/leofeyer>
 */
class tl_gallery_creator_galleries extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * handle ajax requests
     */
    public function onAjax()
    {
        //
    }


    /**
     * Check permissions to edit table tl_gallery_creator_galleries
     */
    public function checkPermission()
    {
        if ($this->User->isAdmin)
        {
            return;
        }

        // Set root IDs
        if (!is_array($this->User->gallery_creator) || empty($this->User->gallery_creator))
        {
            $root = array(0);
        }
        else
        {
            $root = $this->User->gallery_creator;
        }

        $GLOBALS['TL_DCA']['tl_gallery_creator_galleries']['list']['sorting']['root'] = $root;

        // Check permissions to add calendars
        if (!$this->User->hasAccess('create', 'gallery_creatorp'))
        {
            $GLOBALS['TL_DCA']['tl_gallery_creator_galleries']['config']['closed'] = true;
        }

        // Check current action
        switch (Input::get('act'))
        {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(Input::get('id'), $root))
                {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_gallery_creator_galleries']) && in_array(Input::get('id'), $arrNew['tl_gallery_creator_galleries']))
                    {
                        // Add the permissions on group level
                        if ($this->User->inherit != 'custom')
                        {
                            $objGroup = $this->Database->execute("SELECT id, gallery_creator, gallery_creatorp FROM tl_user_group WHERE id IN(" . implode(',', array_map('intval', $this->User->groups)) . ")");

                            while ($objGroup->next())
                            {
                                $arrGalleryCreatorp = deserialize($objGroup->gallery_creatorp);

                                if (is_array($arrGalleryCreatorp) && in_array('create', $arrGalleryCreatorp))
                                {
                                    $arrGalleryCreator = deserialize($objGroup->gallery_creator, true);
                                    $arrGalleryCreator[] = Input::get('id');

                                    $this->Database->prepare("UPDATE tl_user_group SET gallery_creator=? WHERE id=?")
                                        ->execute(serialize($arrGalleryCreator), $objGroup->id);
                                }
                            }
                        }

                        // Add the permissions on user level
                        if ($this->User->inherit != 'group')
                        {
                            $objUser = $this->Database->prepare("SELECT gallery_creator, gallery_creatorp FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrGalleryCreatorp = deserialize($objUser->gallery_creatorp);

                            if (is_array($arrGalleryCreatorp) && in_array('create', $arrGalleryCreatorp))
                            {
                                $arrGalleryCreator = deserialize($objUser->gallery_creator, true);
                                $arrGalleryCreator[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET gallery_creator=? WHERE id=?")
                                    ->execute(serialize($arrGalleryCreator), $this->User->id);
                            }
                        }

                        // Add the new element to the user object
                        $root[] = Input::get('id');
                        $this->User->gallery_creator = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'gallery_creatorp')))
                {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' gallery ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'gallery_creatorp'))
                {
                    $session['CURRENT']['IDS'] = array();
                }
                else
                {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act')))
                {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' gallery_creator', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }


    /**
     * Return the edit header button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->canEditFieldsOf('tl_gallery_creator_galleries') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }


    /**
     * Return the copy galery button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function copyGallery($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('create', 'gallery_creatorp') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }


    /**
     * Return the delete gallery button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function deleteGallery($row, $href, $label, $title, $icon, $attributes)
    {
        return $this->User->hasAccess('delete', 'gallery_creatorp') ? '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ' : Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)) . ' ';
    }

    /**
     * onload-callback
     * create the palette
     */
    public function setUpPalettes()
    {
        //
    }
}
