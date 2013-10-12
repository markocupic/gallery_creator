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
 * Add palettes to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array(
    'ce_gallery_creator',
    'onloadCbSetUpPalettes'
);

$GLOBALS['TL_DCA']['tl_content']['palettes']['gallery_creator'] = 'name,type,headline;{miscellaneous_legend},gc_publish_all_albums,gc_publish_albums,gc_AlbumsPerPage,gc_ThumbsPerPage,gc_rows,gc_activateThumbSlider,gc_redirectSingleAlb;{sorting_legend},gc_sorting,gc_sorting_direction;{picture_sorting_legend},gc_picture_sorting,gc_picture_sorting_direction;{thumb_legend},gc_size_albumlisting,gc_imagemargin_albumlisting,gc_size_detailview,gc_imagemargin_detailview,gc_fullsize;{template_legend:hide},gc_template;{protected_legend:hide},protected;{expert_legend:hide},align,space,cssID';

/**
 * Add fields to tl_content
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_rows'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_rows'],
    'exclude' => true,
    'default' => '4',
    'inputType' => 'select',
    'options' => range(0, 30),
    'eval' => array('tl_class' => 'clr'),
    'sql' => "smallint(5) unsigned NOT NULL default '4'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_template'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_template'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array(
        'ce_gallery_creator',
        'getTemplates'
    ),
    'eval' => array('tl_class' => 'clr'),
    'sql' => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_hierarchicalOutput'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_hierarchicalOutput'],
    'exclude' => true,
    'default' => false,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true, 'tl_class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_sorting'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_sorting'],
    'exclude' => true,
    'options' => explode(',', 'date,sorting,id,tstamp,name,alias,comment,visitors'),
    'default' => 'date',
    'inputType' => 'select',
    'eval' => array(
        'tl_class' => 'clr',
        'submitOnChange' => true
    ),
    'sql' => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_sorting_direction'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_sorting_direction'],
    'exclude' => true,
    'options' => explode(',', 'DESC,ASC'),
    'default' => 'DESC',
    'inputType' => 'select',
    'eval' => array(
        'tl_class' => 'clr',
        'submitOnChange' => true
    ),
    'sql' => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_picture_sorting'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_picture_sorting'],
    'exclude' => true,
    'options' => explode(',', 'sorting,id,tstamp,name,owner,comment,title'),
    'default' => 'date',
    'inputType' => 'select',
    'eval' => array(
        'tl_class' => 'clr',
        'submitOnChange' => false
    ),
    'sql' => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_picture_sorting_direction'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_picture_sorting_direction'],
    'exclude' => true,
    'options' => explode(',', 'DESC,ASC'),
    'default' => 'DESC',
    'inputType' => 'select',
    'eval' => array(
        'tl_class' => 'clr',
        'submitOnChange' => false
    ),
    'sql' => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_activateThumbSlider'] = array(
    'exclude' => true,
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_activateThumbSlider'],
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_redirectSingleAlb'] = array(
    'exclude' => true,
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_redirectSingleAlb'],
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_AlbumsPerPage'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_AlbumsPerPage'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'rgxp' => 'digit',
        'tl_class' => 'clr'
    ),
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_size_detailview'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_size_detailview'],
    'exclude' => true,
    'inputType' => 'imageSize',
    'options' => $GLOBALS['TL_CROP'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array(
        'rgxp' => 'digit',
        'nospace' => true,
        'tl_class' => 'clr'
    ),
    'sql' => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_imagemargin_detailview'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_imagemargin_detailview'],
    'exclude' => true,
    'inputType' => 'trbl',
    'options' => array('px', '%', 'em', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'),
    'eval' => array('includeBlankOption' => true, 'tl_class' => 'clr'),
    'sql' => "varchar(128) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_size_albumlisting'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_size_albumlisting'],
    'exclude' => true,
    'inputType' => 'imageSize',
    'options' => $GLOBALS['TL_CROP'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array(
        'rgxp' => 'digit',
        'nospace' => true,
        'tl_class' => 'clr'
    ),
    'sql' => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_imagemargin_albumlisting'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_imagemargin_albumlisting'],
    'exclude' => true,
    'inputType' => 'trbl',
    'options' => array('px', '%', 'em', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'),
    'eval' => array('includeBlankOption' => true, 'tl_class' => 'clr'),
    'sql' => "varchar(128) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_fullsize'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_fullsize'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr'),
    'sql' => "char(1) NOT NULL default '1'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_ThumbsPerPage'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_ThumbsPerPage'],
    'default' => 0,
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array(
        'rgxp' => 'digit',
        'tl_class' => 'clr'
    ),
    'sql' => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_publish_albums'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_publish_albums'],
    'inputType' => 'checkbox',
    'exclude' => true,
    'options_callback' => array(
        'ce_gallery_creator',
        'optionsCallbackListAlbums'
    ),
    'eval' => array(
        'multiple' => true,
        'tl_class' => 'clr'
    ),
    'sql' => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_publish_all_albums'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_publish_all_albums'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array(
        'tl_class' => 'clr',
        'submitOnChange' => true
    ),
    'sql' => "char(1) NOT NULL default ''"
);

/**
 * Class ce_gallery_creator
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Marko Cupic
 * @author     Marko Cupic
 */

class ce_gallery_creator extends Backend
{
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Return all gallery_creator frontent-templates as array
     * @return array
     */
    public function getTemplates()
    {
        return $this->getTemplateGroup('ce_gc_');
    }

    /**
     * options_callback fuer die Albumauflistung
     * @return string
     */
    public function optionsCallbackListAlbums()
    {
        $objContent = $this->Database->prepare('SELECT gc_sorting, gc_sorting_direction FROM tl_content WHERE id=?')->execute(Input::get('id'));

        $str_sorting = $objContent->gc_sorting == '' || $objContent->gc_sorting_direction == '' ? 'date DESC' : $objContent->gc_sorting . ' ' . $objContent->gc_sorting_direction;

        $db = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_albums WHERE published=? ORDER BY ' . $str_sorting)->execute('1');

        $arrOpt = array();
        while ($db->next()) {
            $arrOpt[$db->id] = '[ID ' . $db->id . '] ' . $db->name;
        }
        return $arrOpt;
    }

    /**
     * onload_callback onloadCbSetUpPalettes
     * @return string
     */
    public function onloadCbSetUpPalettes()
    {
        $objContent = $this->Database->prepare('SELECT gc_publish_all_albums FROM tl_content WHERE id=?')->execute(Input::get('id'));
        if ($objContent->gc_publish_all_albums) {
            $GLOBALS['TL_DCA']['tl_content']['palettes']['gallery_creator'] = str_replace('gc_publish_albums,', '', $GLOBALS['TL_DCA']['tl_content']['palettes']['gallery_creator']);
        }
    }

}

?>