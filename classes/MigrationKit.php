<?php

namespace Markocupic\GalleryCreator;


use Contao\GalleryCreatorGalleriesModel;
use Contao\GalleryCreatorAlbumsModel;
use Contao\Database;
use Contao\Message;

class MigrationKit
{

    /**
     * Migrate from version 5.x to 6.x
     * Store albums in galleries
     * @param $table
     * @param $new_records
     * @param $parent_table
     * @param $child_tables
     * @return bool
     */
    public function migrate($table, $new_records, $parent_table, $child_tables)
    {
        $objAlbums = Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_albums');
        $objGalleries = Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_galleries');

        if (!$objGalleries->numRows && $objAlbums->numRows)
        {
            $objDb = Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_albums');
            $arrPids = $objDb->fetchEach('pid');
            $arrPids = array_unique($arrPids);
            foreach ($arrPids as $pid)
            {
                // Create parent gallery container
                if ($pid == 0)
                {
                    $title = 'Auto generated gallery container after migration.';
                }
                else
                {
                    $objParentAlbum = GalleryCreatorAlbumsModel::findByPk($pid);
                    if ($objParentAlbum !== null)
                    {
                        $title = $objParentAlbum->name;
                    }
                    else
                    {
                        $title = 'Auto generated gallery container after migration.';
                    }
                }
                $objGallery = new GalleryCreatorGalleriesModel();
                $objGallery->title = $title;
                $objGallery->tstamp = time();
                $objGallery->save();
                $currentGalleryId = $objGallery->id;


                $objAlbum = Database::getInstance()->prepare('SELECT * FROM tl_gallery_creator_albums WHERE pid=?')->execute($pid);
                while ($objAlbum->next())
                {
                    $objAlbumModel = GalleryCreatorAlbumsModel::findByPid($pid);
                    if ($objAlbumModel !== null)
                    {
                        $objAlbumModel->pid = $currentGalleryId;
                        $objAlbumModel->source = 'default';
                        $objAlbumModel->save();
                        Message::addInfo(sprintf("Gallery Creator Update: Stored album '%s' into gallery container '%s'.", $objAlbum->name, $objGallery->title));
                    }
                }
            }
            // Reload the page (reviseTable hook)
            return true;
        }
    }
}