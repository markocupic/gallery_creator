<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2015 Leo Feyer
 *
 * @package Gallery Creator
 * @link    http://www.contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

$GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('fop;', 'fop;{calendars_legend},gallery_creator,gallery_creatorp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
$GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('fop;', 'fop;{gallery_creator_legend},gallery_creator,gallery_creatorp;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);

/**
 * Add fields to tl_user
 */

$GLOBALS['TL_DCA']['tl_user']['fields']['gc_img_resolution'] = array(
       'sql' => "varchar(12) NOT NULL default 'no_scaling'"
);

$GLOBALS['TL_DCA']['tl_user']['fields']['gc_img_quality'] = array(
       'sql' => "smallint(3) unsigned NOT NULL default '100'"
);

$GLOBALS['TL_DCA']['tl_user']['fields']['gc_be_uploader_template'] = array(
       'sql' => "varchar(32) NOT NULL default 'be_gc_html5_uploader'"
);


$GLOBALS['TL_DCA']['tl_user']['fields']['gallery_creator'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['gallery_creator'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'foreignKey'              => 'tl_gallery_creator_galleries.title',
    'eval'                    => array('multiple'=>true),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user']['fields']['gallery_creatorp'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['gallery_creatorp'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'options'                 => array('create', 'delete'),
    'reference'               => &$GLOBALS['TL_LANG']['MSC'],
    'eval'                    => array('multiple'=>true),
    'sql'                     => "blob NULL"
);


