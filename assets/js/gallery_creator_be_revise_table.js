/**
 * Class GalleryCreatorBe
 *
 * Provide methods to handle GalleryCreator-output.
 * @copyright  Marko Cupic 2015
 * @author     Marko Cupic <m.cupic@gmx.ch>
 */

// Dollar Safe Mode
(function ($) {
    window.addEvent('domready', function () {
        //Create the global GalleryCreatorFe-object
        objGalleryCreator = new GalleryCreatorBe();
        objGalleryCreator.getAlbumIDS();

    });


    GalleryCreatorBe = new Class({

        /**
         * array with als albumID's
         */
        albumIDS: null,

        /**
         * constructor
         */
        initialize: function () {
            //
        },

        /**
         * get all album ids
         */
        getAlbumIDS: function () {
            var self = this;
            var myRequest = new Request.JSON({
                url: document.URL + '&isAjaxRequest=true&reviseTable=true&getAlbumIDS=true',
                method: 'get',

                onSuccess: function (responseText) {
                    if (!responseText) return;
                    var string = responseText.albumIDS.toString();
                    self.albumIDS = string.split(",");
                    self.reviseTable();
                },

                onError: function () {
                    // if (!responseText) return;
                    // alert('error');
                }
            });

            myRequest.send();
        },

        /**
         * fire for each album there will be fired a request
         * display error messages in the head section of the backend
         */
        reviseTable: function () {
            this.albumIDS.each(function (albumId) {
                var myRequest = new Request.JSON({
                    url: document.URL + '&isAjaxRequest=true&reviseTable=true&albumId=' + albumId,
                    method: 'get',
                    chain: true,

                    onSuccess: function (responseText) {
                        if (!responseText) return;
                        if (responseText.errors.toString() == '') return;
                        var arrError = responseText.errors.toString().split('***');
                        if (!$$('.tl_message')[0]) {
                            var messageBox = new Element('div');
                            messageBox.addClass('tl_message');
                            messageBox.inject(document.id('tl_buttons'), 'after');
                        }
                        arrError.each(function (errorMsg) {
                            var error = new Element('p');
                            error.addClass('tl_error');
                            error.set('text', errorMsg);
                            error.inject($$('.tl_message')[0]);
                        });

                    },

                    onError: function () {
                        //alert('error');
                    }
                });

                myRequest.send();

            });
        }
    });
})(document.id);

