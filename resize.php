<?php 
require_once("config.php"); 
$ratioArr = getImageMaxWidthHeight_Each_Ratio();
//print_r($ratioArr);
if($_POST) {
	$orgImage = $_REQUEST['imageName'];
	$file_name_ext = @strtolower(end(explode('.', $orgImage)));
	$file_name_primary = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '_', basename($orgImage, '.' . $file_name_ext))); 
	if ($file_name_ext == 'jpg'){$file_name_ext = 'jpeg';}
	
	$srcFolder = IMAGES_PATH . DS . TEMP_IMAGE_FOLDER . DS . date('Ym') . DS;
	$desFolder = IMAGES_PATH . DS . DESTINATION_IMAGE_FOLDER . DS . date('Ym') . DS;
	copy($srcFolder.$orgImage, $desFolder.$orgImage); // copy original file to destination folder
	foreach ($_POST as $key => $value) {
		list($type, $code) = explode(';', $value);
		list(, $code) = explode(',', $code);
		$code = base64_decode($code);
		$size = $key;
		
		$newFile = $desFolder.$file_name_primary.'_'.$size.'.'.$file_name_ext;
		file_put_contents($newFile, $code);
	}
	//print_r($ratioArr); die;
	foreach($ratioArr as $val) {
		foreach($val['sizes'] as $innerval) {
			$source_image = $desFolder.$file_name_primary.'_'.$val['maxWidth'].'x'.$val['maxHeight'].'.'.$file_name_ext;
			$destination_path = $desFolder;
			$destination_filename = $file_name_primary.'_'.$innerval ;
			$destination_format = $file_name_ext;
			$imgWidHtArr = explode("x",$innerval);
			$NewImageWidth = $imgWidHtArr[0];
			$NewImageHeight = $imgWidHtArr[1];
			
			if($source_image != $destination_path.$destination_filename.'.'.$file_name_ext)
				$result = resizeImage($source_image, array($destination_path, $destination_filename, $destination_format), array($NewImageWidth, $NewImageHeight), $ecode, $emsgs);
			
		}
	}
	
	header("location:" . SITE_URL."view.php?imageName=" . $orgImage );
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>cropit</title>
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/jquery.cropit.js"></script>
        <style>
            .main-container{ position: absolute; top:0; bottom: 0; left: 150px; right: 0; margin: auto; }
            .cropit-preview { background-color: #f8f8f8; background-size: cover; border: 5px solid #ccc; border-radius: 3px; margin-top: 7px;
                              width: 250px; height: 250px; }
            .cropit-preview-image-container { cursor: move; }
            .cropit-preview-background { opacity: .2; cursor: auto; }
            .image-size-label { margin-top: 10px; }

            input, .export {
                /* Use relative position to prevent from being covered by image background */
                position: relative; z-index: 10; display: block; }

            button { margin-top: 10px; }

            .cropit-image-loading { background:rgba(0, 0, 0, 0) url("assets/image/bigLoader.gif") no-repeat scroll center center ; }
        </style>
        
       
    </head>
    <body>
        <div class="main-container">
        	<form name="zoom_crop_image" action="" method="post">
<?php for($i=0; $i<count($ratioArr); $i++) {; ?>
				<style>#cropit-preview_<?=$i+1?> {width:<?=$ratioArr[$i]['maxWidth']?>px;height:<?=$ratioArr[$i]['maxHeight']?>px;}</style>
				<div id="image-editor_<?=$i+1?>" class="image-editor">
                	<h1>Edit Images <?=$ratioArr[$i]['ratio']?> </h1>
			      	<div id="cropit-preview_<?=$i+1?>" style="width:500;height:<?=$ratioArr[$i]['maxHeight']?>;" class="cropit-preview"></div>
				  	<div class="range">Slide to zoom area <input type="range" class="cropit-image-zoom-input"></div>
			  		<img style="width:25px;" class="rotate-cw_<?=$i+1?>" src="assets/image/clockwise.png">
			  		<img style="width:25px;" class="rotate-ccw_<?=$i+1?>" src="assets/image/aclockwise.png">
				  	<input type="hidden" name="<?=$ratioArr[$i]['maxWidth']?>x<?=$ratioArr[$i]['maxHeight']?>" id="image_image-editor_<?=$i+1?>" value="">	
			    </div>
                <script  src="script.php?ver=<?=$i+1?>&imageName=<?=SITE_URL.MAIN_IMAGE_FOLDER.'/'.TEMP_IMAGE_FOLDER.'/'.date('Ym').'/'.$_REQUEST['imageName'];?>" ></script>
<?php } ?>
				<button class="save" >Save</button>
                <input type="button" value="Cancel" onclick="javascript:window.location='<?=SITE_URL?>index.php'" class="cancle">
            </form>
        

            <script>
                $(function () {
                    $('.save').click(function () {
                        $(".image-editor").each(function () {
                            var id = $(this).attr('id');
                            var imageData = $('#' + id).cropit('export');
                            $('#image_' + id).val(imageData);
                        });
                    });
                });
            </script>
        </div>
    </body>
</html>