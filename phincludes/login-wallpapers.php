<?php defined('PHRAPI') or die("Direct access not allowed!");?>
<?$images = $factory->Access->loadWallpapers()?>
<?$imagesPath = $GLOBALS['config']['wallpapers_path']?>
<div style="position: absolute; border: 0px solid white; top: 13%; display: none;" id="panelWP">
	<?if($images > 0):?>
		<?foreach($images as $image):?>
			<img class="wp" src="<?=$imagesPath . '/' . $image?>" width="100px" /><br /><br />
		<?endforeach;?>
	<?endif;?>
</div>