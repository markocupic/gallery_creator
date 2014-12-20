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
 * Add to palette
 */

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{gallery_creator_legend:hide},gc_watermark_path,gc_watermark_opacity,gc_watermark_valign,gc_watermark_halign,gc_disable_backend_edit_protection,gc_album_import_copy_files,gc_read_exif';

/**
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_watermark_opacity'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_opacity'],
	'inputType' => 'select',
	'options'   => array(
		'100',
		'90',
		'80',
		'70',
		'60',
		'50',
		'40',
		'30',
		'20',
		'10',
		'0'
	),
	'default'   => '100',
	'eval'      => array('tl_class' => 'm12')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_watermark_valign'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_valign'],
	'inputType' => 'select',
	'options'   => array(
		'bottom',
		'top'
	),
	'default'   => 'bottom',
	'eval'      => array('tl_class' => 'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_watermark_halign'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_halign'],
	'inputType' => 'select',
	'options'   => array(
		'right',
		'left'
	),
	'default'   => 'right',
	'eval'      => array('tl_class' => 'w50')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_watermark_path'] = array(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['gc_watermark_path'],
	'inputType'     => 'fileTree',
	'save_callback' => array
	(
		array('tl_gallery_creator_settings', 'saveCbWatermark')
	),
	'load_callback' => array
	(
		array('tl_gallery_creator_settings', 'loadCbWatermark')
	),
	'eval'          => array(
		'fieldType'  => 'radio',
		'extensions' => 'bmp,jpg,jpeg,png,gif',
		'filesOnly'  => true,
		'files'      => true,
		'mandatory'  => false,
		'tl_class'   => 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_disable_backend_edit_protection'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_disable_backend_edit_protection'],
	'inputType' => 'checkbox',
	'eval'      => array(
		'fieldType' => 'checkbox',
		'tl_class'  => 'clr'
	)
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_album_import_copy_files'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_album_import_copy_files'],
	'inputType' => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_read_exif'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_read_exif'],
	'inputType' => 'checkbox'
);


/**
 * Class tl_gallery_creator_settings
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Marko Cupic
 * @author     Marko Cupic
 * @package    Controller
 */
class tl_gallery_creator_settings extends Backend
{

	/**
	 * @param $singleSRC
	 * @return string
	 */
	public function saveCbWatermark($singleSRC)
	{
		return base64_encode($singleSRC);
	}


	/**
	 * @param $singleSRC
	 * @return string
	 */
	public function loadCbWatermark($singleSRC)
	{
		return base64_decode($singleSRC);
	}
}


