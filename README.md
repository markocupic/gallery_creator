# Gallery Creator

Frontend Modul für Contao 3
Mit dem Modul lassen sich Alben verwalten und erstellen. Das Modul ist sehr flexibel und bietet eine Albenübersicht und eine Detailansicht.

## "gc_generateFrontendTemplate"-Hook
Mit dem "gc_generateFrontendTemplate"-Hook lässt sich die Frontend-Ausgabe anpassen.
Der "gc_generateFrontendTemplate"-Hook wird vor der Aufbereitung des Gallery-Creator-Frontend-Templates ausgeführt. Er übergibt das Modul-Objekt und in der Detailansicht das aktuelle Album-Objekt. Der Hook verlangt keinen Rückgabewert. Hinzugefügt in Version 4.8.0.

```php
<?php
// config.php
$GLOBALS['TL_HOOKS']['addComment'][] = array('MyGalleryCreatorClass', 'doSomething');

// MyGalleryCreatorClass.php
class MyGalleryCreatorClass extends \System {

       /**
        * Do some custom modifications
        * @param Module $objModule
        * @param null $objAlbum
        */
       public function doSomething(Module $objModule, $objAlbum=null)
       {

              global $objPage;
              $objPage->pageTitle = '4ae Bildergalerie';
              if($objAlbum !== null)
              {
                     // $objPage->rootPageTitle = 'Root Page Title';
                     $objPage->pageTitle = $objAlbum->name;
                     $objPage->description = $objAlbum->event_location;
                     $GLOBALS['TL_KEYWORDS'] = $objAlbum->event_location;
              }

       }
}
```


Viel Spass mit Gallery Creator!!!

