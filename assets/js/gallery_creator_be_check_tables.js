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
         * count errors
         */
        errors: 0,

        /**
         * count completed requests
         */
        intRequestsDone: 0,

        /**
         * messageBox
         */
        messageBox: null,

        /**
         * statusBox
         */
        statusBox: null,

        /**
         * constructor
         */
        initialize: function () {
            document.id('main').addClass('gc_check_tables');

            // Inject the message holder into the DOM
            if (!$$('.tl_message')[0]) {
                this.messageBox = new Element('div', {
                    'class': 'tl_message'
                });
                this.messageBox.inject(document.id('tl_buttons'), 'after');

                // Inject the status box into the DOM
                this.statusBox = new Element('p#statusBox', {
                    'class': 'tl_status_box'
                });
                this.statusBox.inject($$('.tl_folder_top')[0]);
            }
        },

        /**
         * kick off!
         */
        start: function () {
            this.intRequestsDone = 0;
            this.errors = 0;
            this.albumIDS = null;
            $$('.tl_error').each(function(el){
                el.destroy();
            });
            this.statusBox.set('text', 'Checking tables...');

            // Kick off!
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
            var self = this;
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

                        arrError.each(function (errorMsg) {
                            self.errors++;
                            var error = new Element('p', {
                                    'class': 'tl_error',
                                    text: errorMsg
                                }
                            );
                            error.inject($$('.tl_message')[0]);
                        });
                    },

                    onComplete: function () {
                        self.intRequestsDone++;
                        self.statusBox.set('text', 'Check album with ID ' + albumId + '.');

                        // finaly display a message, when all requests are done
                        if (self.intRequestsDone == self.albumIDS.length) {
                            // Show message after all requests are done
                            var endCheck = (function () {
                                self.statusBox.set('text', 'The check ended up. ' + self.errors.toInt().toString() + ' error(s) were found.');
                            }.delay(3000));

                            // Clear status Box
                            var cleanStatusBox = (function () {
                                self.statusBox.set('text', '');
                            }.delay(15000));
                        }

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

