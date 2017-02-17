<?php 
require_once("config.php"); 
$ratioArr = getImageMaxWidthHeight_Each_Ratio();

$orgImage = $_REQUEST['imageName'];
$file_name_ext = @strtolower(end(explode('.', $orgImage)));
$file_name_primary = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '_', basename($orgImage, '.' . $file_name_ext))); 
if ($file_name_ext == 'jpg'){$file_name_ext = 'jpeg';}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>cropit</title>
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/jszip.js"></script>
        <script>
		function downloadImg(imgPath, imgName) {
			var a = $("<a>")
		    .attr("href", imgPath)
		    .attr("download", imgName)
		    .appendTo("body");
			a[0].click();
			a.remove();
		}
		</script>
        <script>
				

		</script>
        <style>
            .item { border:0px solid gray; width: 120px; float: left; margin: 3px; padding: 3px; font-size:12px; }
        </style>
    </head>
    <body>
        <div class="main-container">
        <!--<a href="javascript:void(0);" onClick="dld()">Download All</a>-->
<?php foreach($ratioArr as $val) { ?>
		<div style="clear:both;">
			<b><?=$val['ratio']?></b><hr>
<?php 	foreach($val['sizes'] as $innerval) { ?>
<?php 		$imgUrl = SITE_URL.MAIN_IMAGE_FOLDER.'/'.DESTINATION_IMAGE_FOLDER.'/'.date('Ym').'/'.$file_name_primary.'_'.$innerval.'.'.$file_name_ext; ?>
            <div class="item">
            <img style="height:60px;" src="<?=$imgUrl?>"><br><b><?=$innerval?></b> <br><a href="<?=$imgUrl?>" target="_blank" > View </a> <a href="javascript:void(0);" onClick="downloadImg('<?=$imgUrl?>','<?=$file_name_primary.'_'.$innerval.'.'.$file_name_ext?>')" > | Download </a>
            </div>
<?php 	} ?>
		</div>
<?php } ?>
        </div>
        
                
    </body>
</html>