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

/**
 * Add to palette
 */

$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{gallery_creator_legend:hide},gc_error404_thumb,gc_disable_backend_edit_protection,gc_album_import_copy_files,gc_read_exif';

/**
 * Add fields
 */

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_disable_backend_edit_protection'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_disable_backend_edit_protection'],
	'inputType' => 'checkbox',
	'eval'      => array('fieldType' => 'checkbox', 'tl_class' => 'clr')
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_album_import_copy_files'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_album_import_copy_files'],
	'inputType' => 'checkbox'
);

$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_read_exif'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_settings']['gc_read_exif'],
	'inputType' => 'checkbox'
);
$GLOBALS['TL_DCA']['tl_settings']['fields']['gc_error404_thumb'] = array(
	'label'         => &$GLOBALS['TL_LANG']['tl_settings']['gc_error404_thumb'],
	'inputType'     => 'fileTree',
	'eval'          => array('fieldType' => 'radio', 'extensions' => 'jpg,jpeg,png,gif', 'filesOnly' => true, 'files' => true, 'mandatory' => false, 'tl_class' => 'clr')
);


