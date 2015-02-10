// Dollar Safe Mode
(function ($) {
    window.addEvent('domready', function () {
        var objGalleryCreator = new GalleryCreatorBeCheckTables();
        objGalleryCreator.start();
    });


    /**
     * Class GalleryCreatorBeCheckTables
     *
     * Provide methods to check tables
     * @copyright  Marko Cupic 2015
     * @author     Marko Cupic <m.cupic@gmx.ch>
     */
    GalleryCreatorBeCheckTables = new Class({

        /**
         * array with als albumID's
         */
        albumIDS: null,

        /**
         * constructor
         */
        initialize: function () {
            document.id('main').addClass('gc_check_tables');
        },

        /**
         * kick off!
         */
        start: function () {
            this.getAlbumIDS();
        },

        /**
         * get all album ids
         */
        getAlbumIDS: function () {
            var self = this;
            var myRequest = new Request.JSON({

                url: document.URL + '&isAjaxRequest=true&checkTables=true&getAlbumIDS=true',

                method: 'get',

                onSuccess: function (responseText) {
                    if (!responseText) return;

                    var responseString = responseText.albumIDS.toString();
                    if (responseString != '') {
                        self.albumIDS = responseString.split(",");
                        self.checkTables();
                    }
                },

                onError: function () {
                    //
                }
            });
            // fire request (get AlbumIDS)
            myRequest.send();
        },

        /**
         * for each album there will be fired a request
         * display error messages in the head section of the backend
         */
        checkTables: function () {
            if (this.albumIDS === null) {
                return;
            }

            this.albumIDS.each(function (albumId) {
                var myRequest = new Request.JSON({

                    url: document.URL + '&isAjaxRequest=true&checkTables=true&albumId=' + albumId,

                    method: 'get',

                    // Any calls made to start while the request is running will be chained up,
                    // and will take place as soon as the current request has finished,
                    // one after another.
                    chain: true,

                    onSuccess: function (responseText) {
                        if (!responseText) {
                            return;
                        }
                        if (responseText.errors.toString() == '') {
                            return;
                        }
                        var arrError = responseText.errors.toString().split('***');
                        if (!$$('.tl_message')[0]) {
                            var messageBox = new Element('div');
                            messageBox.addClass('tl_message');
                            messageBox.inject(document.id('tl_buttons'), 'after');
                        }
                        arrError.each(function (errorMsg) {
                            var error = new Element('p', {
                                    'class': 'tl_error',
                                    text: errorMsg
                                }
                            );
                            error.inject($$('.tl_message')[0]);
                        });
                    },

                    onComplete: function () {
                        // destroy previous status boxes
                        if ($$('.tl_status_box')) {
                            $$('.tl_status_box').each(function (el) {
                                el.destroy();
                            });
                        }

                        // inject status box into DOM
                        $$('#tl_listing .tl_folder_top')[0].setStyle('position', 'relative');
                        var statusBox = new Element('p#statusBox' + albumId, {
                            'class': 'tl_status_box',
                            text: 'Check album with ID ' + albumId + '.'
                        });
                        statusBox.inject($$('.tl_folder_top')[0]);

                        // delete the last status box after 10s of delay
                        var delStatusBox = (function () {
                            if (document.id('statusBox' + albumId)) {
                                document.id('statusBox' + albumId).destroy();
                            }
                        }.delay(10000));
                    },

                    onError: function () {
                        //
                    }
                });
                // fire request
                myRequest.send();

            });
        }
    });
})(document.id);

