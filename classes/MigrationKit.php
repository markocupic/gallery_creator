<?php

namespace Markocupic\GalleryCreator;


use Contao\GalleryCreatorGalleriesModel;
use Contao\GalleryCreatorAlbumsModel;
use Contao\GalleryCreatorPicturesModel;
use Contao\Database;

class MigrationKit
{

    public function migrate()
    {
        $objGalleries = Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_galleries');
        if (!$objGalleries->numRows)
        {
            $objDb = Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_albums');
            $arrPids = $objDb->fetchEach('pid');
            $arrPids = array_unique($arrPids);
            foreach ($arrPids as $pid)
            {
                // Create parent gallery container
                if($pid == 0)
                {
                    $title =  'Auto-generated after migration (pid=0).';
                }
                else
                {
                    $objParentAlbum = GalleryCreatorAlbumsModel::findByPk($pid);
                    if($objParentAlbum !== null){
                        $title = $objParentAlbum->name;
                    }else{
                        $title = 'Auto-generated after migration (pid=' . $pid .').';
                    }
                }
                $objGallery = new GalleryCreatorGalleriesModel();
                $objGallery->title = $title;
                $objGallery->tstamp = time();
                $objGallery->save();
                $currentGalleryId =  $objGallery->id;



                $objAlbum = Database::getInstance()->execute('SELECT * FROM tl_gallery_creator_albums WHERE pid=?')->execute($pid);
                while ($objAlbum->next())
                {
                   $objAlbumModel = GalleryCreatorAlbumsModel::findByPid($pid);
                   if($objAlbumModel !== null)
                   {
                       $objAlbumModel->pid = $currentGalleryId;
                       $objAlbumModel->source = 'default';
                       $objAlbumModel->save();
                   }
                }
            }
        }
    }
}
