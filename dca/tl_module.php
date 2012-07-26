<?php
if (!defined('TL_ROOT'))
	die('You can not access this file directly!');

/**
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
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['gallery_creator'] = 'name,type,headline;{thumb_legend},gc_size_albumlist,gc_size_detailview,gc_imagemargin,gc_fullsize;{image_legend},gc_AlbumsPerPage,gc_ThumbsPerPage,gc_rows,gc_activateThumbSlider,gc_redirectSingleAlb,gc_hierarchicalOutput;{template_legend:hide},gc_template;{protected_legend:hide},protected;{expert_legend:hide},align,space,cssID';

/**
 * Add fields to tl_module
 */

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_rows'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_rows'],
	'default' => '4',
	'inputType' => 'select',
	'options' => range(0, 30),
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_detailview'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_size_detailview'],
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
	$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_detailview']['options'] = $GLOBALS['TL_CROP'];
}

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_albumlist'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_size_albumlist'],
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
	$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_albumlist']['options'] = $GLOBALS['TL_CROP'];
}

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_fullsize'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_fullsize'],
	'exclude' => true,
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_hierarchicalOutput'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_hierarchicalOutput'],
	'exclude' => true,
	'default' => true,
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_template'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_template'],
	'exclude' => true,
	'inputType' => 'select',
	'options_callback' => array(
		'mod_gallery_creator',
		'getTemplates'
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_imagemargin'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_content']['imagemargin'],
	'exclude' => true,
	'inputType' => 'trbl',
	'options' => explode(',', 'px,%,em,pt,pc,in,cm,mm'),
	'eval' => array(
		'includeBlankOption' => true,
		'tl_class' => ''
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_activateThumbSlider'] = array(
	'exclude' => true,
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_activateThumbSlider'],
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_redirectSingleAlb'] = array(
	'exclude' => true,
	'label' => &$GLOBALS['TL_LANG']['tl_content']['gc_redirectSingleAlb'],
	'inputType' => 'checkbox',
	'eval' => array('tl_class' => '')
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_AlbumsPerPage'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_AlbumsPerPage'],
	'exclude' => true,
	'inputType' => 'text',
	'eval' => array(
		'rgxp' => 'digit',
		'tl_class' => ''
	)
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_ThumbsPerPage'] = array(
	'label' => &$GLOBALS['TL_LANG']['tl_module']['gc_ThumbsPerPage'],
	'default' => 0,
	'exclude' => true,
	'inputType' => 'text',
	'eval' => array(
		'rgxp' => 'digit',
		'tl_class' => ''
	)
);

/**
 * Class mod_gallery_creator
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 * @copyright  Leo Feyer 2005-2010
 * @author     Leo Feyer <http://www.contao.org>
 */
class mod_gallery_creator extends Backend
{
	/**
	 * Return all gallery_creator frontent-templates as array
	 * @param object
	 * @return array
	 */
	public function getTemplates(DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;
		return $this->getTemplateGroup('ce_gc_', $intPid);
	}


}
?>