/**
 * Class GalleryCreatorFe
 *
 * Provide methods to handle GalleryCreator-output.
 * @copyright  Marko Cupic 2011
 * @author     Marko Cupic <m.cupic@gmx.ch>
 */
 
window.addEvent('domready', function() {
	//Create the global GalleryCreatorFe-object
	objGalleryCreator = new GalleryCreatorFe();
});
 
 
GalleryCreatorFe = new Class({
	initialize: function ()
	{
		//constructor
 		this.thumbOpacity = 1.0;
	},		

	initThumbSlide: function (el, fmdId, albumId, countPictures, moduleType)
	{
		if (this.eventId) 
		{
			this.initThumbSlide(el, fmdId, albumId, moduleType);
			return;
		}	
		//set some class-vars
		this.currentDiv = document.id(el);
		this.thumb = document.id(el).getElement('img.thumb');
		this.fmdId = fmdId;
		this.albumId = albumId;
		this.countPictures = countPictures;
		this.currentPic=0;
		this.moduleType = moduleType;
		this.defaultThumbSrc=this.thumb.getProperty('src');
		var currentTime = new Date();		
		this.eventId = currentTime.getTime();
		this.lastFade = currentTime.getTime();
		//add the onmouseout-event
		this.currentDiv.addEvent('mouseout', function() {
			objGalleryCreator.stopThumbSlide();
		});
		
		//slide thumbs after a delay of xxx milliseconds
		this.startThumbSlide(this.eventId);
	},
	
	stopThumbSlide:	function ()
	{
		this.eventId=null;		
		if (this.thumb.getProperty('src')!= this.defaultThumbSrc)
		{
			this.thumb.fade(this.thumbOpacity);					
			this.thumb.set('opacity', this.thumbOpacity);				
			this.thumb.setProperty('src',this.defaultThumbSrc);
		}
		this.thumb.fade(this.thumbOpacity);
	},

	startThumbSlide:	function (eventId)
	{
		var myRequest = new Request.JSON({
			url: 'ajax.php',
			method: 'get',
			
			onSuccess: function(responseText)
			{
				if (!responseText) return;
				if (responseText.eventId != objGalleryCreator.eventId) return; 
				if (responseText.eventId==null || objGalleryCreator.eventId==null) return;	

				if (responseText.thumbPath!="" && responseText.thumbPath!=objGalleryCreator.thumb.getProperty('src'))
				{
					var currentTime = new Date();		
					if (currentTime.getTime()-objGalleryCreator.lastFade<2000)
					{
						objGalleryCreator.startThumbSlide(eventId);
						return;
					}
					
					objGalleryCreator.lastFade = currentTime.getTime();					
					var thumb = objGalleryCreator.thumb;
					thumb.fade(0.3);					
					thumb.set('opacity', 0.3);
					thumb.setProperty('src', responseText.thumbPath);
					thumb.fade(this.thumbOpacity);
				}
				
				objGalleryCreator.startThumbSlide(responseText.eventId);
				
			}
		});
		if (objGalleryCreator.currentPic==objGalleryCreator.countPictures-1){
				objGalleryCreator.currentPic=0;
		}
		//next pic
		objGalleryCreator.currentPic++;
		myRequest.send('isAjax=1&action=' + objGalleryCreator.moduleType + '&id=' + objGalleryCreator.fmdId + '&thumbSlider=1&AlbumId=' + objGalleryCreator.albumId + '&limit=' + objGalleryCreator.currentPic + '&eventId=' + eventId);
	}
});
