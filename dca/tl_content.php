<?php
if (!defined('TL_ROOT'))
	die('You can not access this file directly!');

/*
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Marko Cupic 2010
 * @author     Marko Cupic, Oberkirch, Switzerland ->  mailto: m.cupic@gmx.ch
 * @package    gallery_creator
 * @license    GNU/LGPL
 * @filesource
 */

/**
 * Add palettes to tl_content
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = array(
	'ce_gallery_creator',
	'onloadCbSetUpPalettes'
);

$GLOBALS['TL_DCA']['tl_content']['palettes']['gallery_creator'] = 'name,type,headline;{miscellaneous_legend},gc_publish_all_albums,gc_publish_albums,gc_AlbumsPerPage,gc_ThumbsPerPage,gc_rows,gc_activateThumbSlider,gc_redirectSingleAlb;{sorting},gc_sorting,gc_sorting_direction;{picture_sorting},gc_picture_sorting,gc_picture_sorting_direction;{thumb_legend},gc_size_albumlist,gc_size_detailview,imagemargin,gc_fullsize;{template_legend:hide},gc_template;{protected_legend:hide},protected;{expert_legend:hide},align,space,cssID';

/**
 * Add fields to tl_content
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_rows'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_rows'],
	'exclude' => true,
	'default' => '4',
	'inputType' => 'select',
	'options' => range(0, 30),
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_template'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_template'],
	'exclude' => true,
	'inputType' => 'select',
	'options_callback' => array(
		'ce_gallery_creator',
		'getTemplates'
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_hierarchicalOutput'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_hierarchicalOutput'],
	'exclude' => true,
	'default' => false,
	'inputType' => 'checkbox',
	'eval' => array('submitOnChange' => true)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_sorting'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_sorting'],
	'exclude' => true,
	'options' => explode(',', 'date,sorting,id,tstamp,name,alias,comment'),
	'default' => 'date',
	'inputType' => 'select',
	'eval' => array(
		'tl_class' => '',
		'submitOnChange' => true
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_sorting_direction'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_sorting_direction'],
	'exclude' => true,
	'options' => explode(',', 'DESC,ASC'),
	'default' => 'DESC',
	'inputType' => 'select',
	'eval' => array(
		'tl_class' => '',
		'submitOnChange' => true
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_picture_sorting'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_picture_sorting'],
	'exclude' => true,
	'options' => explode(',', 'sorting,id,tstamp,name,owner,comment,title'),
	'default' => 'date',
	'inputType' => 'select',
	'eval' => array(
		'tl_class' => '',
		'submitOnChange' => false
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_picture_sorting_direction'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_picture_sorting_direction'],
	'exclude' => true,
	'options' => explode(',', 'DESC,ASC'),
	'default' => 'DESC',
	'inputType' => 'select',
	'eval' => array(
		'tl_class' => '',
		'submitOnChange' => false
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_activateThumbSlider'] = array(
	'exclude' => true,
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_activateThumbSlider'],
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_redirectSingleAlb'] = array(
	'exclude' => true,
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_redirectSingleAlb'],
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_AlbumsPerPage'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_AlbumsPerPage'],
	'exclude' => true,
	'inputType' => 'text',
	'eval' => array(
		'rgxp' => 'digit',
		'tl_class' => ''
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_size_detailview'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_size_detailview'],
	'exclude' => true,
	'inputType' => 'imageSize',
	'options' => explode(',', 'crop,proportional,box'),
	'reference' => &$GLOBALS['TL_LANG']['MSC'],
	'eval' => array(
		'rgxp' => 'digit',
		'nospace' => true,
		'tl_class' => ''
	)
);
if (version_compare(VERSION, '2.10', '>'))
{
	$GLOBALS['TL_DCA']['tl_content']['fields']['gc_size_detailview']['options'] = $GLOBALS['TL_CROP'];
}

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_size_albumlist'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_size_albumlist'],
	'exclude' => true,
	'inputType' => 'imageSize',
	'options' => explode(',', 'crop,proportional,box'),
	'reference' => &$GLOBALS['TL_LANG']['MSC'],
	'eval' => array(
		'rgxp' => 'digit',
		'nospace' => true,
		'tl_class' => ''
	)
);
if (version_compare(VERSION, '2.10', '>'))
{
	$GLOBALS['TL_DCA']['tl_content']['fields']['gc_size_albumlist']['options'] = $GLOBALS['TL_CROP'];
}

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_fullsize'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_fullsize'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_ThumbsPerPage'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_ThumbsPerPage'],
	'default' => 0,
	'exclude' => true,
	'inputType' => 'text',
	'eval' => array(
		'rgxp' => 'digit',
		'tl_class' => ''
	)
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
		'tl_class' => ''
	)
);

$GLOBALS['TL_DCA']['tl_content']['fields']['gc_publish_all_albums'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_publish_all_albums'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array(
		'tl_class' => '',
		'submitOnChange' => true
	)
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
	 * @param object
	 * @return array
	 */
	public function getTemplates(DataContainer $dc)
	{
		// Get the page ID
		$objArticle = $this->Database->prepare("SELECT pid FROM tl_article WHERE id=?")->limit(1)->execute($dc->activeRecord->pid);

		// Inherit the page settings
		$objPage = $this->getPageDetails($objArticle->pid);

		// Get the theme ID
		$objLayout = $this->Database->prepare("SELECT pid FROM tl_layout WHERE id=?")->limit(1)->execute($objPage->layout);

		// Return all gallery templates
		return $this->getTemplateGroup('ce_gc_', $objLayout->pid);
	}


	/**
	 * options_callback fuer die Albumauflistung
	 * @return string
	 */
	public function optionsCallbackListAlbums()
	{
		$objContent = $this->Database->prepare('SELECT gc_sorting, gc_sorting_direction FROM tl_content WHERE id=?')->execute($this->Input->get('id'));

		$str_sorting = $objContent->gc_sorting == '' || $objContent->gc_sorting_direction == '' ? 'date DESC' : $objContent->gc_sorting . ' ' . $objContent->gc_sorting_direction;

		$db = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_albums WHERE published=? ORDER BY ' . $str_sorting)->execute('1');

		$arrOpt = array();
		while ($db->next())
		{
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
		$objContent = $this->Database->prepare('SELECT gc_publish_all_albums FROM tl_content WHERE id=?')->execute($this->Input->get('id'));
		if ($objContent->gc_publish_all_albums)
		{
			$GLOBALS['TL_DCA']['tl_content']['palettes']['gallery_creator'] = str_replace('gc_publish_albums,', '', $GLOBALS['TL_DCA']['tl_content']['palettes']['gallery_creator']);
		}
	}


}
?>