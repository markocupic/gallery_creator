-- ********************************************************
-- *                                                      *
-- * IMPORTANT NOTE                                       *
-- *                                                      *
-- * Do not import this file manually but use the Contao  *
-- * install tool to create and maintain database tables! *
-- *                                                      *
-- ********************************************************

-- 
-- Table `tl_gallery_creator_albums`
-- 

CREATE TABLE `tl_gallery_creator_albums` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `tstamp` int(10) NOT NULL default '0',
  `date` int(10) NOT NULL default '0',
  `published` int(1) NOT NULL default '1',
  `displ_alb_in_this_ce` text NOT NULL,
  `owner` int(10) NOT NULL default '0',
  `owners_name` text NOT NULL,
  `event_location` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `alias` varbinary(128) NOT NULL default '',
  `comment` text NOT NULL,
  `preserve_filename` int(1) NOT NULL default '0',
  `thumb` varchar(255) NOT NULL default '',
  `img_resolution` smallint(5) unsigned NOT NULL default '600',
  `img_quality` smallint(4) unsigned NOT NULL default '1000',
  `protected` char(1) NOT NULL default '',
  `groups` blob NULL,
   PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Table `tl_gallery_creator_pictures`
-- 
CREATE TABLE `tl_gallery_creator_pictures` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) NOT NULL default '0',
  `sorting` int(10) NOT NULL default '0',
  `tstamp` int(10) NOT NULL default '0',
  `date` int(10) NOT NULL default '0',
  `addCustomThumb` char(1) NOT NULL default '',
  `customThumb` varchar(255) NOT NULL default '',
  `published` char(1) NOT NULL default '1',
  `owner` int(10) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `path` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `comment` text NOT NULL,
  `externalFile` char(1) NOT NULL default '',
  `socialMediaSRC` varchar(255) NOT NULL default '',
  `localMediaSRC` varchar(255) NOT NULL default '',
  `cssID` varchar(255) NOT NULL default '',

  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


-- 
-- Table `tl_module`
-- 
CREATE TABLE `tl_module` (
  `gc_rows` smallint(5) unsigned NOT NULL default '4',
  `gc_template` varchar(64) NOT NULL default '',
  `gc_imagemargin` varchar(128) NOT NULL default '',
  `gc_activateThumbSlider` char(1) NOT NULL default '',
  `gc_redirectSingleAlb` char(1) NOT NULL default '',
  `gc_size_albumlist` varchar(64) NOT NULL default '',
  `gc_size_detailview` varchar(64) NOT NULL default '',
  `gc_fullsize` char(1) NOT NULL default '1',
  `gc_ThumbsPerPage` smallint(5) unsigned NOT NULL default '0',
  `gc_AlbumsPerPage` smallint(5) unsigned NOT NULL default '0',
  `gc_hierarchicalOutput` int(1) unsigned NOT NULL default '1',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


-- 
-- Table `tl_content`
-- 

CREATE TABLE `tl_content` (
  `gc_rows` smallint(5) unsigned NOT NULL default '4',
  `gc_publish_all_albums` char(1) NOT NULL default '',
  `gc_publish_albums` text NOT NULL,
  `gc_template` varchar(64) NOT NULL default '',
  `gc_activateThumbSlider` char(1) NOT NULL default '',
  `gc_redirectSingleAlb` char(1) NOT NULL default '',
  `gc_size_albumlist` varchar(64) NOT NULL default '',
  `gc_size_detailview` varchar(64) NOT NULL default '',
  `gc_fullsize` char(1) NOT NULL default '1',
  `gc_ThumbsPerPage` smallint(5) unsigned NOT NULL default '0',
  `gc_AlbumsPerPage` smallint(5) unsigned NOT NULL default '0',
  `gc_sorting` varchar(64) NOT NULL default '',
  `gc_picture_sorting` varchar(64) NOT NULL default '',
  `gc_sorting_direction` varchar(64) NOT NULL default '',
  `gc_picture_sorting_direction` varchar(64) NOT NULL default '',
  `gc_hierarchicalOutput` int(1) unsigned NOT NULL default '0',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------


-- 
-- Table `tl_user`
-- 

CREATE TABLE `tl_user` (
  `gc_img_resolution` smallint(5) unsigned NOT NULL default '600',
  `gc_img_quality` smallint(4) unsigned NOT NULL default '1000',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------


