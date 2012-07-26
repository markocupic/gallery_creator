<!-- indexer::continue -->
<?php $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/gallery_creator/html/gallery_creator_fe.js'; ?>

<?php if (!$this->Input->get('vars')): ?>
<!--start album-overview-->
<div class="<?php echo $this->class; ?> gallery_creator block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>
	
<?php echo $this->pagination; ?>
<?php if (count($this->arrAlbums)>0): ?>
<ul class="list_albums">
<?php foreach ($this->arrAlbums as $Album): ?> 
	<li class="level_1 block row"  style="<?php echo $this->imagemargin; ?>">
		<div class="tstamp block">[<?php echo $Album["event_date"]; ?>]</div>
			<div class="col_1">
				<div class="image_container" onmouseover="<?php echo  $Album["thumbMouseover"]; ?>">

						<img src="<?php echo $Album["thumb_src"]; ?>" alt="<?php echo $Album["id"]; ?>" title="<?php echo $Album["title"]; ?>" class="<?php echo $Album["class"]; ?>">

				</div>
			</div>
			<div class="col_2">
				<h2><?php echo $Album["name"]; ?></h2>
<?php if ($Album["count"]): ?>				
				<p class="count_pics"><?php echo $Album["count"]; ?> <?php echo $GLOBALS['TL_LANG']['gallery_creator']['pictures']; ?></p>
<?php endif; ?>
<?php if ($Album["count_subalbums"]): ?>				
				<p class="count_pics"><?php echo $Album["count_subalbums"]; ?> <?php echo $GLOBALS['TL_LANG']['gallery_creator']['subalbums']; ?></p>
<?php endif; ?>		
<?php if ($Album["comment"]): ?>
				<p class="album_comment"><?php echo $Album["comment"]; ?></p>
<?php endif; ?>
			</div>
		<div class="clr"><!--clearing-box--></div>
	</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>	
</div>
<!--end album-overview-->
<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function() {
    //Weiterleitung bei Klick auf das, das Bild enthaltende Listenelement
	$$(".image_container img").addEvent('click', function() {
		var el = $(this);
		startRequest(el.getProperty('alt'));
	});
	
	function startRequest(albumId)
	{
		var myRequest = new Request.JSON({
			url: 'ajax.php',
			method: 'get',
			
			onSuccess: function(responseText)
			{
				if(responseText.arrMediabox!="")
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
		myRequest.send('isAjax=1&action=<?php echo $this->elementType; ?>&id=<?php echo $this->elementId; ?>&LightboxSlideshow=true&albumId=' + albumId + '');
	}
});
//--><!]]>
</script>
<?php endif; ?>




<script type="text/javascript">
<!--//--><![CDATA[//><!--
window.addEvent('domready', function() {
	/**
	 * Cursor über h2
	 */
	$$('.image_container img').setStyle('cursor', 'pointer');
	
	//bei domready erhält das erste Listenelement einen overlay
	$$(".gallery_creator ul.list_albums").getFirst("li").addClass('active');
	//Klassenzuweisung 
	$$(".gallery_creator ul.list_albums li.level_1").addEvent('mouseover', function() {
		$$(".gallery_creator ul.list_albums").getFirst("li").removeClass('active');
		this.addClass('active');
	});
	//Klassenentfernung 
	$$(".gallery_creator ul.list_albums li.level_1").addEvent('mouseout', function() {
		this.removeClass('active');
	});

});
//--><!]]>
</script>

<!-- indexer::stop -->

