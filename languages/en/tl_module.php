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
 * module config
 */

//Legends
$GLOBALS['TL_LANG']['tl_module']['pagination_legend'] = 'Pagination settings';
$GLOBALS['TL_LANG']['tl_module']['module_legend'] = 'Module configuration';
$GLOBALS['TL_LANG']['tl_module']['album_listing_legend'] = 'Album listing legend';
$GLOBALS['TL_LANG']['tl_module']['picture_listing_legend'] = 'Picture listing legend';


//Fields
$GLOBALS['TL_LANG']['tl_module']['gc_readerModule'] = array('Reader Module', 'Select the reader module.');
$GLOBALS['TL_LANG']['tl_module']['gc_galleries'] = array('Galleries', 'Select the galleries.');
$GLOBALS['TL_LANG']['tl_module']['gc_rows'] = array('Thumbnails per row', 'Select the number of thumbnails per row. (0=As much as possible)');
$GLOBALS['TL_LANG']['tl_module']['gc_publish_albums'] = array('Publish these albums only', 'Selected albums will be displayed in the frontend.');
$GLOBALS['TL_LANG']['tl_module']['gc_publish_single_album'] = array('Publish this album', 'The selected album will be displayed in the frontend.');
$GLOBALS['TL_LANG']['tl_module']['gc_publish_all_albums'] = array('Publish all given albums in the frontend');
$GLOBALS['TL_LANG']['tl_module']['gc_hierarchicalOutput'] = array('Hierarchically Frontend-Album-Output', 'Hierarchically Frontend-Album-Output (Albums and Subalbums)');
$GLOBALS['TL_LANG']['tl_module']['gc_template'] = array('Gallery template', 'Select a personal gallery template.');
$GLOBALS['TL_LANG']['tl_module']['gc_activateThumbSlider'] = array('Activate Ajax-Thumb-Slider', 'Activate Ajax-Thumb-Slider on mouseover in the album listing?');
$GLOBALS['TL_LANG']['tl_module']['gc_redirectSingleAlb'] = array('Redirection in case of a single album', 'Should be automatically redirected to the detail-view, in case of single-album-choice?');
$GLOBALS['TL_LANG']['tl_module']['gc_albumsPerPage'] = array('Items per page in the albumlisting', 'The number of items per page in the albumlisting. Set to 0 to disable pagination.');
$GLOBALS['TL_LANG']['tl_module']['gc_thumbsPerPage'] = array('Thumbs per page in the detailview', 'The number of thumbnails per page in the detailview. Set to 0 to disable pagination.');
$GLOBALS['TL_LANG']['tl_module']['gc_paginationNumberOfLinks'] = array('Number of links in the pagination navigation', 'Set the number of links in the pagination navigation. Default to 7.');
$GLOBALS['TL_LANG']['tl_module']['gc_sorting'] = array('Album sorting', 'According to which field the albums should be sorted?');
$GLOBALS['TL_LANG']['tl_module']['gc_sorting_direction'] = array('Sort sequence', 'DESC: descending, ASC: ascending');
$GLOBALS['TL_LANG']['tl_module']['gc_picture_sorting'] = array('Picture sorting', 'According to which field the pictures in a single album should be sorted?');
$GLOBALS['TL_LANG']['tl_module']['gc_picture_sorting_direction'] = array('Sort sequence', 'DESC: descending, ASC: ascending');
$GLOBALS['TL_LANG']['tl_module']['gc_size_detailview'] = array('Detailview: Thumbnail width and height', 'Here you can set the image dimensions and the resize mode.');
$GLOBALS['TL_LANG']['tl_module']['gc_size_albumlisting'] = array('Albumlist: Thumbnail width and height', 'Here you can set the image dimensions and the resize mode.');
$GLOBALS['TL_LANG']['tl_module']['gc_imagemargin'] = array('Image margin', 'Here you can enter the top, right, bottom and left margin.');
$GLOBALS['TL_LANG']['tl_module']['gc_fullsize'] = array('Full-size view/new window', 'Open the full-size image in a lightbox or the link in a new browser window.');

// References
$GLOBALS['TL_LANG']['tl_module']['gc_sortingDirection']['DESC'] = "Ascending";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingDirection']['ASC'] = "Descending";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['sorting'] = "Backend-module sorting (sorting)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['id'] = "ID";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['date'] = "Date of creation (date)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['name'] = "Name (name)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['owner'] = "Owner (owner)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['comment'] = "Comment/Caption (comment)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['title'] = "Image-title (title)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['tstamp'] = "Revision date (tstamp)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['alias'] = "Albumalias (alias)";
$GLOBALS['TL_LANG']['tl_module']['gc_sortingField']['visitors'] = "Number of visitors (visitors)";