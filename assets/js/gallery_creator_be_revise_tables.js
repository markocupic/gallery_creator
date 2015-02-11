// Dollar Safe Mode
(function ($) {
    window.addEvent('domready', function () {
        var objGalleryCreator = new GalleryCreatorBeReviseTables();
    });


    /**
     * Class GalleryCreatorBe
     *
     * Provide methods to revise tables
     * @copyright  Marko Cupic 2015
     * @author     Marko Cupic <m.cupic@gmx.ch>
     */
    GalleryCreatorBeReviseTables = new Class({

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
         * button
         */
        button: null,

        /**
         * checkbox
         */
        checkbox: null,

        /**
         * messageBox
         */
        labelCheckbox: null,


        /**
         * constructor
         */
        initialize: function () {
            var self = this;
            document.id('main').addClass('gc_revise_tables');

            this.button = document.id('reviseTableBtn');
            this.checkbox = $$('input[name=revise_tables]')[0];
            this.labelCheckbox = $$('label[for=revise_tables]')[0];

            // inject message holder into DOM
            this.messageBox = new Element('div#messageBox', {
                'class': 'gc_message'
            });
            this.messageBox.inject($$('.tl_formbody_submit')[0], 'before');

            // inect statusBox into DOM
            this.statusBox = new Element('p#statusBox', {
                'class': 'tl_status_box'
            });
            this.statusBox.inject(this.messageBox);


            this.button.addEvent('click', function (event) {
                if (self.checkbox.checked) {
                    // hide some elements
                    self.button.fade(0);
                    self.checkbox.fade(0);
                    self.labelCheckbox.fade(0);

                    // kick off!
                    self.start();
                }
            });
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
            this.statusBox.set('text', 'Please wait a moment...');
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
                        self.reviseTables();
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
        reviseTables: function () {
            var self = this;

            if (this.albumIDS === null) {
                return;
            }

            this.albumIDS.each(function (albumId) {
                var myRequest = new Request.JSON({

                    url: document.URL + '&isAjaxRequest=true&checkTables=true&reviseTables=true&albumId=' + albumId,

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

                        var arrResponse = responseText.errors.toString().split('***');

                        arrResponse.each(function (errorMsg) {
                            var errorBox = new Element('p', {
                                    'class': 'tl_error',
                                    text: errorMsg
                                }
                            );
                            errorBox.inject(self.messageBox);
                            self.errors++;
                        });
                    },

                    onComplete: function () {

                        self.intRequestsDone++;

                        // display next message
                        self.statusBox.set('text', 'Check album with ID ' + albumId + '.');

                        // finaly display a message, when all requests are done
                        if (self.intRequestsDone == self.albumIDS.length) {
                            var delStatusBox = (function () {
                                self.statusBox.set('text', 'Database cleaned up. ' + self.errors.toInt().toString() + ' error(s) found.');
                                self.button.fade(1);
                                self.checkbox.checked = false;
                                self.checkbox.fade(1);
                                self.labelCheckbox.fade(1);
                            }.delay(3000));
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

