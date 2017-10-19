<?php
use Contao\Input;
use Contao\System;


/**
 * Add palettes to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array(
    'tl_module_gallery_creator',
    'onloadCbSetUpPalettes',
);


$GLOBALS['TL_DCA']['tl_module']['palettes']['gallery_creator_list'] = 'name,type,headline;{module_legend},gc_readerModule,gc_galleries;{pagination_legend},gc_albumsPerPage,gc_paginationNumberOfLinks;
{album_listing_legend},gc_sorting,gc_sorting_direction,gc_size_albumlisting,gc_imagemargin;
{template_legend:hide},customTpl;
{protected_legend:hide},protected;
{expert_legend:hide},align,space,cssID';

$GLOBALS['TL_DCA']['tl_module']['palettes']['gallery_creator_reader'] = 'name,type,headline;
{module_legend},gc_galleries;
{pagination_legend},gc_thumbsPerPage,gc_paginationNumberOfLinks;
{picture_listing_legend},gc_rows,gc_fullsize,gc_picture_sorting,gc_picture_sorting_direction,gc_size_detailview,gc_imagemargin;
{template_legend:hide},customTpl;
{protected_legend:hide},protected;
{expert_legend:hide},align,space,cssID';


/**
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['gc_galleries'] = array(
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['gc_galleries'],
    'exclude'          => true,
    'inputType'        => 'checkbox',
    'options_callback' => array('tl_module_gallery_creator', 'getGalleries'),
    'eval'             => array('mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'),
    'sql'              => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_readerModule'] = array(
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['gc_readerModule'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => array('tl_module_gallery_creator', 'getReaderModules'),
    'reference'        => &$GLOBALS['TL_LANG']['tl_module'],
    'eval'             => array('includeBlankOption' => true, 'tl_class' => 'clr'),
    'sql'              => "int(10) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_rows'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_rows'],
    'exclude'   => true,
    'default'   => '4',
    'inputType' => 'select',
    'options'   => range(0, 30),
    'eval'      => array('tl_class' => 'clr'),
    'sql'       => "smallint(5) unsigned NOT NULL default '4'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_sorting'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_sorting'],
    'exclude'   => true,
    'options'   => explode(',', 'date,sorting,id,tstamp,name,alias,comment,visitors'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['gc_sortingField'],
    'default'   => 'date',
    'inputType' => 'select',
    'eval'      => array('tl_class' => 'w50', 'submitOnChange' => true),
    'sql'       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_sorting_direction'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_sorting_direction'],
    'exclude'   => true,
    'options'   => explode(',', 'DESC,ASC'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['gc_sortingDirection'],
    'default'   => 'DESC',
    'inputType' => 'select',
    'eval'      => array('tl_class' => 'w50', 'submitOnChange' => true),
    'sql'       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_picture_sorting'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_picture_sorting'],
    'exclude'   => true,
    'options'   => explode(',', 'sorting,id,date,name,owner,comment,title'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['gc_sortingField'],
    'default'   => 'date',
    'inputType' => 'select',
    'eval'      => array('tl_class' => 'w50', 'submitOnChange' => false),
    'sql'       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_picture_sorting_direction'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_picture_sorting_direction'],
    'exclude'   => true,
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['gc_sortingDirection'],
    'options'   => explode(',', 'DESC,ASC'),
    'default'   => 'DESC',
    'inputType' => 'select',
    'eval'      => array('tl_class' => 'w50', 'submitOnChange' => false),
    'sql'       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_albumsPerPage'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_albumsPerPage'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('rgxp' => 'digit', 'tl_class' => 'w50'),
    'sql'       => "smallint(5) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_paginationNumberOfLinks'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_paginationNumberOfLinks'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('rgxp' => 'digit', 'tl_class' => 'w50'),
    'sql'       => "smallint(5) unsigned NOT NULL default '7'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_detailview'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_size_detailview'],
    'exclude'   => true,
    'inputType' => 'imageSize',
    'options'   => System::getImageSizes(),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'),
    'sql'       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_imagemargin'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_imagemargin'],
    'exclude'   => true,
    'inputType' => 'trbl',
    'options'   => $GLOBALS['TL_CSS_UNITS'],
    'eval'      => array('includeBlankOption' => true, 'tl_class' => 'w50'),
    'sql'       => "varchar(128) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_size_albumlisting'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_size_albumlisting'],
    'exclude'   => true,
    'inputType' => 'imageSize',
    'options'   => System::getImageSizes(),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'),
    'sql'       => "varchar(64) NOT NULL default ''",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_fullsize'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_fullsize'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'clr'),
    'sql'       => "char(1) NOT NULL default '1'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_thumbsPerPage'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_thumbsPerPage'],
    'default'   => 0,
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => array('rgxp' => 'digit', 'tl_class' => 'clr'),
    'sql'       => "smallint(5) unsigned NOT NULL default '0'",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_publish_albums'] = array(
    'label'                => &$GLOBALS['TL_LANG']['tl_module']['gc_publish_albums'],
    'inputType'            => 'checkbox',
    'exclude'              => true,
    'input_field_callback' => array('tl_module_gallery_creator', 'inputFieldCallbackListAlbums'),
    'eval'                 => array('multiple' => true, 'mandatory' => false, 'tl_class' => 'clr'),
    'sql'                  => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_publish_single_album'] = array(
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['gc_publish_single_album'],
    'inputType'        => 'radio',
    'exclude'          => true,
    'options_callback' => array('tl_module_gallery_creator', 'optionsCallbackListAlbums'),
    'eval'             => array('mandatory' => false, 'multiple' => false, 'tl_class' => 'clr'),
    'sql'              => "blob NULL",
);

$GLOBALS['TL_DCA']['tl_module']['fields']['gc_publish_all_albums'] = array(
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['gc_publish_all_albums'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'eval'      => array('tl_class' => 'clr', 'submitOnChange' => true),
    'sql'       => "char(1) NOT NULL default ''",
);

/**
 * Class tl_module_gallery_creator
 */
class tl_module_gallery_creator extends Backend
{
    /**
     * tl_module_gallery_creator constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * @return array
     */
    public function getGalleries()
    {
        if (!$this->User->isAdmin && !is_array($this->User->gallery_creator))
        {
            return array();
        }

        $arrGalleries = array();
        $objGalleries = $this->Database->execute("SELECT id,title FROM tl_gallery_creator_galleries ORDER BY title");

        while ($objGalleries->next())
        {
            if ($this->User->hasAccess($objGalleries->id, 'gallery_creator'))
            {
                $arrGalleries[$objGalleries->id] = $objGalleries->title;
            }
        }

        return $arrGalleries;
    }


    /**
     * @return array
     */
    public function getReaderModules()
    {
        $arrModules = array();
        $objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.type='gallery_creator_reader' ORDER BY t.name, m.name");

        while ($objModules->next())
        {
            $arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
        }

        return $arrModules;
    }


    /**
     * @return array
     */
    public function optionsCallbackListAlbums()
    {

        $objModule = $this->Database->prepare('SELECT gc_sorting, gc_sorting_direction FROM tl_module WHERE id=?')->execute(Input::get('id'));

        $str_sorting = $objModule->gc_sorting == '' || $objModule->gc_sorting_direction == '' ? 'date DESC' : $objModule->gc_sorting . ' ' . $objModule->gc_sorting_direction;

        $db = $this->Database->prepare('SELECT id, name FROM tl_gallery_creator_albums WHERE published=? ORDER BY ' . $str_sorting)->execute('1');

        $arrOpt = array();
        while ($db->next())
        {
            $arrOpt[$db->id] = '[ID ' . $db->id . '] ' . $db->name;
        }

        return $arrOpt;
    }


    /**
     * @return string
     */
    public function inputFieldCallbackListAlbums()
    {
        if (Input::post('FORM_SUBMIT') == 'tl_module')
        {

            if (!Input::post('gc_publish_all_albums'))
            {
                $albums = array();
                if (Input::post('gc_publish_albums'))
                {
                    foreach (deserialize(Input::post('gc_publish_albums'), true) as $album)
                    {
                        $albums[] = $album;
                    }
                }
                $set = array('gc_publish_albums' => serialize($albums));
                $this->Database->prepare('UPDATE tl_module %s WHERE id=? ')->set($set)->execute(Input::get('id'));
            }
        }

        $html = '
<div class="clr">
  <fieldset id="ctrl_gc_publish_albums" class="tl_checkbox_container">
        <legend>Folgende Alben im Frontend anzeigen</legend>
        <input type="hidden" name="gc_publish_albums" value="">
        <input type="checkbox" id="check_all_gc_publish_albums" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this,\'ctrl_gc_publish_albums\')"> <label for="check_all_gc_publish_albums" style="color:#a6a6a6"><em>Alle ausw&auml;hlen</em></label>
        <br><br>
        %s
        <p class="tl_help tl_tip" title="">Ausgew&auml;hlte Alben werden im Frontend angezeigt.</p>
    </fieldset>
</div>';

        return sprintf($html, $this->getSubalbumsAsUnorderedList(0));

    }


    /**
     *
     */
    public function onloadCbSetUpPalettes()
    {

        $objModule = $this->Database->prepare('SELECT gc_publish_all_albums FROM tl_module WHERE id=?')->execute(Input::get('id'));
        if ($objModule->gc_publish_all_albums)
        {
            $GLOBALS['TL_DCA']['tl_module']['palettes']['gallery_creator_list'] = str_replace('gc_publish_albums,', '', $GLOBALS['TL_DCA']['tl_module']['palettes']['gallery_creator_list']);
        }
    }

}
