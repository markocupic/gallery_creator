/**
 * Javascript for the Gallery Creator Lightbox Template
 *
 * Provides functions to handle GalleryCreator-output.
 * @copyright  Marko Cupic 2011
 * @author     Marko Cupic <m.cupic@gmx.ch>
 */
 

window.addEvent('domready', function() {
    //Weiterleitung bei Klick auf das, das Bild enthaltende Listenelement
	$$(".image_container img").addEvent('click', function() {
		var el = $(this);
		arrInfo = el.getProperty('alt').split(',');
		startRequest(arrInfo[0],arrInfo[1],arrInfo[2]);
	});
	
	function startRequest(albumId, elementType, elementId)
	{
		var myRequest = new Request.JSON({
			url: 'ajax.php',
			method: 'get',
			
			onSuccess: function(responseText)
			{
				if(responseText.arrImage!="")
				{
					var responseArray = responseText.arrImage.split('***');
					var i=0;					
					var imageArray = Array();
					responseArray.each(function(str, index){
						var pic = str.split('###');
						if (pic[0]!="")
						{
							imageArray[i] = ['' + pic[0] + '', '' + pic[1] + '',''];
						}
						i++;
					});
					
					if (typeof Mediabox  != "undefined") {Mediabox.open(imageArray,0);}
					if (typeof Slimbox  != "undefined") {Slimbox.open(imageArray,0);}
				}
			}
		});
		myRequest.send('isAjax=1&action=' + elementType + '&id=' + elementId + '&LightboxSlideshow=true&albumId=' + albumId);
	}
});
