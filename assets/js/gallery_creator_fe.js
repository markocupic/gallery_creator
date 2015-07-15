/**
 * Class GalleryCreatorFe
 *
 * Provide methods to handle GalleryCreator-output.
 * @copyright  Marko Cupic 2015
 * @author     Marko Cupic <m.cupic@gmx.ch>
 */

// Dollar Safe Mode
(function ($) {
    window.addEvent('domready', function () {
        //Create the global GalleryCreatorFe-object
        objGalleryCreator = new GalleryCreatorFe();
    });


    GalleryCreatorFe = new Class({
        initialize: function () {
            //constructor
            this.thumbOpacity = 1;
        },

        initThumbSlide: function (el, albumId, countPictures) {
            var self = this;
           
            //set some class-vars
            this.currentDiv = document.id(el);
            this.thumb = document.id(el).getElement('img.thumb');
            this.albumId = albumId;
            this.countPictures = countPictures;
            this.currentPic = 0;
            this.defaultThumbSrc = this.thumb.getProperty('src');
            var currentTime = new Date();
            this.eventId = currentTime.getTime();
            this.lastSlide = currentTime.getTime();
            //add the onmouseout-event
            this.currentDiv.addEvent('mouseout', function () {
                self.stopThumbSlide();
            });

            //slide thumbs after a delay of xxx milliseconds
            this.startThumbSlide(this.eventId);
        },

        stopThumbSlide: function () {
            this.eventId = null;
            if (this.thumb.getProperty('src') != this.defaultThumbSrc) {
                this.thumb.fade(this.thumbOpacity);
                this.thumb.set('opacity', this.thumbOpacity);
                this.thumb.setProperty('src', this.defaultThumbSrc);
            }
            this.thumb.fade(this.thumbOpacity);
        },

        startThumbSlide: function (eventId) {
            var self = this;
            var myRequest = new Request.JSON({
                url: document.URL,
                method: 'get',

                onSuccess: function (responseText) {
                    if (!responseText) return;
                    if (responseText.eventId != self.eventId) return;
                    if (responseText.eventId == null || self.eventId == null) return;
                    if (responseText.thumbPath != "" && responseText.thumbPath != self.thumb.getProperty('src')) {
                        var currentTime = new Date();
                        if (currentTime.getTime() - self.lastSlide < 1200) {
                            self.startThumbSlide(eventId);
                            return;
                        }

                        self.lastSlide = currentTime.getTime();
                        var thumb = self.thumb;
                        thumb.setProperty('src', responseText.thumbPath);

                    }

                    self.startThumbSlide(responseText.eventId);

                }
            });
            if (self.currentPic == self.countPictures - 1) {
                self.currentPic = 0;
            }
            //next pic
            self.currentPic++;
            myRequest.send('isAjax=1&thumbSlider=1&AlbumId=' + self.albumId + '&limit=' + self.currentPic + '&eventId=' + eventId);
        }
    });
})(document.id);

