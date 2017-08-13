## Wichtig! Neukonzeptionierung von Gallery Creator
### Was sind die Neuerungen?
Grob gesagt gibt es zwei Neuerungen.
1. Die Module für die Albenauflistung und Albumdetailseite sind getrennt. Das heisst, es ist nötig zwei Frontend-Module anzulegen. So wie bei Contao üblich z.B. im News-/Kalendermodul.
2. Die hierarchische Anzeige wird aufgegeben. Hingegen kommt ein weiterer Container hinzu. Damit lassen sich 2 Hierarchiestufen abbilden. Galerie -> Album -> Bild

### Vor Installation Backup anlegen
Die Version 6 befindet sich noch im Alpha Stadium und es können Fehler enthalten sein. Falls von einer älteren Gallery Creator auf diese Version (6.x) gewechselt wird, sollte zuvor unbedingt ein Backup der Datenbank und des Dateisystems gemacht werden.

## Frontend Modul für Contao 3.5.x
Mit dem Modul lassen sich Alben verwalten und erstellen. Das Modul ist sehr flexibel und bietet eine Albenübersicht und eine Detailansicht.

## "gc_generateFrontendTemplate"-Hook
Mit dem "gc_generateFrontendTemplate"-Hook lässt sich die Frontend-Ausgabe anpassen.
Der "gc_generateFrontendTemplate"-Hook wird vor der Aufbereitung des Gallery-Creator-Frontend-Templates ausgeführt. Er übergibt das Modul-Objekt und in der Detailansicht das aktuelle Album-Objekt. Als Rückgabewert wird das Template-Objekt erwartet. Hinzugefügt in Version 4.8.0.

```php
<?php
// config.php
$GLOBALS['TL_HOOKS']['gc_generateFrontendTemplate'][] = array('MyGalleryCreatorClass', 'doSomething');

// MyGalleryCreatorClass.php
class MyGalleryCreatorClass extends \System
{

       /**
        * Do some custom modifications
        * @param Module $objModule
        * @param null $objAlbum
        * @return mixed
        */
       public function doSomething(\Module $objModule, $objAlbum=null)
       {
              global $objPage;
              $objPage->pageTitle = 'Bildergalerie';
              if($objAlbum !== null)
              {
                     // display the album name in the head section of your page (title tag)
                     $objPage->pageTitle = specialchars($objAlbum->name);
                     // display the album comment in the head section of your page (description tag)
                     $objPage->description = specialchars(strip_tags($objAlbum->comment));
                     // add the album name to the keywords in the head section of your page (keywords tag)
                     $GLOBALS['TL_KEYWORDS'] .= ',' . specialchars($objAlbum->name) . ',' . specialchars($objAlbum->event_location);
              }
              return $objModule->Template;
       }
}
```


Viel Spass mit Gallery Creator!!!

