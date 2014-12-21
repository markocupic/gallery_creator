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
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['gallery_creator'] = 'name,type,headline;{excluded_albums_legend:hide},gc_excludedAlbums;{thumb_legend},gc_size_albumlisting,gc_imagemargin_albumlisting,gc_size_detailview,gc_imagemargin_detailview,gc_fullsize;{pagination_legend},gc_AlbumsPerPage,gc_ThumbsPerPage,gc_PaginationNumberOfLinks;{image_legend},gc_rows,gc_activateThumbSlider,gc_redirectSingleAlb,gc_hierarchicalOutput;{template_legend:hide},gc_template;{protected_legend:hide},protected;{expert_legend:hide},align,space,cssID';

/**
 * Add fields to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_rows'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_rows'],
	'default'   => '4',
	'inputType' => 'select',
	'options'   => range(0, 30),
	'eval'      => array('tl_class' => ''),
	'sql'       => "smallint(5) unsigned NOT NULL default '4'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_albumlisting'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_size_albumlisting'],
	'exclude'   => true,
	'inputType' => 'imageSize',
	'options'   => $GLOBALS['TL_CROP'],
	'reference' => &$GLOBALS['TL_LANG']['MSC'],
	'eval'      => array('rgxp' => 'digit', 'nospace' => true, 'tl_class' => 'clr'),
	'sql'       => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_imagemargin_albumlisting'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_imagemargin_albumlisting'],
	'exclude'   => true,
	'inputType' => 'trbl',
	'options'   => array('px', '%', 'em', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'),
	'eval'      => array('includeBlankOption' => true, 'tl_class' => 'clr'),
	'sql'       => "varchar(128) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_detailview'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_size_detailview'],
	'exclude'   => true,
	'inputType' => 'imageSize',
	'options'   => $GLOBALS['TL_CROP'],
	'reference' => &$GLOBALS['TL_LANG']['MSC'],
	'eval'      => array('rgxp' => 'digit', 'nospace' => true, 'tl_class' => ''),
	'sql'       => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_imagemargin_detailview'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_imagemargin_detailview'],
	'exclude'   => true,
	'inputType' => 'trbl',
	'options'   => array('px', '%', 'em', 'ex', 'pt', 'pc', 'in', 'cm', 'mm'),
	'eval'      => array('includeBlankOption' => true, 'tl_class' => 'clr'),
	'sql'       => "varchar(128) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_fullsize'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_fullsize'],
	'exclude'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => ''),
	'sql'       => "char(1) NOT NULL default '1'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_hierarchicalOutput'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_hierarchicalOutput'],
	'exclude'   => true,
	'default'   => true,
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => ''),
	'sql'       => "char(1) NOT NULL default '1'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_template'] = array(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['gc_template'],
	'exclude'          => true,
	'inputType'        => 'select',
	'options_callback' => array('mod_gallery_creator', 'getTemplates'),
	'sql'              => "varchar(64) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_imagemargin'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
	'exclude'   => true,
	'inputType' => 'trbl',
	'options'   => explode(',', 'px,%,em,pt,pc,in,cm,mm'),
	'eval'      => array('includeBlankOption' => true, 'tl_class' => ''),
	'sql'       => "varchar(128) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_activateThumbSlider'] = array(
	'exclude'   => true,
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_activateThumbSlider'],
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => ''),
	'sql'       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_redirectSingleAlb'] = array(
	'exclude'   => true,
	'label'     => &$GLOBALS['TL_LANG']['tl_content']['gc_redirectSingleAlb'],
	'inputType' => 'checkbox',
	'eval'      => array('tl_class' => ''),
	'sql'       => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_AlbumsPerPage'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_AlbumsPerPage'],
	'exclude'   => true,
	'inputType' => 'text',
	'eval'      => array('rgxp' => 'digit', 'tl_class' => ''),
	'sql'       => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_ThumbsPerPage'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_ThumbsPerPage'],
	'default'   => 0,
	'exclude'   => true,
	'inputType' => 'text',
	'eval'      => array('rgxp' => 'digit', 'tl_class' => ''),
	'sql'       => "smallint(5) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_PaginationNumberOfLinks'] = array(
	'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_PaginationNumberOfLinks'],
	'exclude'   => true,
	'inputType' => 'text',
	'eval'      => array('rgxp' => 'digit', 'tl_class' => 'clr'),
	'sql'       => "smallint(5) unsigned NOT NULL default '7'"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_excludedAlbums'] = array(
	'label'            => &$GLOBALS['TL_LANG']['tl_module']['gc_excludedAlbums'],
	'default'          => 0,
	'exclude'          => true,
	'inputType'        => 'checkbox',
	'options_callback' => array('mod_gallery_creator', 'listAlbums'),
	'eval'             => array('multiple' => true, 'tl_class' => 'clr'),
	'sql'              => "blob NULL"
);

/**
 * Class mod_gallery_creator
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @copyright  Leo Feyer 2005-2010
 * @author     Leo Feyer <http://www.contao.org>
 */
class mod_gallery_creator extends Backend
{

	/**
	 * Return all gallery_creator frontent-templates as array
	 *
	 * @return array
	 */
	public function getTemplates()
	{

		return $this->getTemplateGroup('ce_gc_');
	}


	public function listAlbums()
	{
		$objAlbum = \Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_albums ORDER BY sorting');
		$arrAlbums = array();
		while($objAlbum->next())
		{
			$arrAlbums[$objAlbum->id] = $objAlbum->name;
		}
		return $arrAlbums;
	}

}
