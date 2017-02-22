<?php
require_once("config.php");
require_once("db.config.php");
$ratioArr = getImageMaxWidthHeight_Each_Ratio();
//print_r($ratioArr);

if ($_POST) {
    $orgImage = $_REQUEST['imageName'];
    $pathinfo = pathinfo($orgImage);
    $folderName = date('Ym');

    $srcFolder = IMAGES_PATH . DS . TEMP_IMAGE_FOLDER . DS . $folderName . DS;
    //$desFolder = IMAGES_PATH . DS . DESTINATION_IMAGE_FOLDER . DS . date('Ym') . DS;
    // copy original file to destination folder
    //copy($srcFolder . $orgImage, $desFolder . $orgImage); 

    $connection = ftp_connect(MEDIA2_SERVER) or die("Unable to connect FTP:" . MEDIA2_SERVER);
    $login = ftp_login($connection, MEDIA2_USER_NAME, MEDIA2_USER_PASS) or die('Media2 Login attempt failed!');

    //uploading crop images to media2 cropit folder
    $cropitPath = MEDIA2_CROPIT_PATH . $folderName;
    $dirExist = is_dir('ftp://' . MEDIA2_USER_NAME . ':' . MEDIA2_USER_PASS . '@' . MEDIA2_SERVER . $cropitPath);
    if (!$dirExist)
        @ftp_mkdir($connection, $cropitPath);
    ftp_chdir($connection, $cropitPath);
    $uploadStatus = ftp_put($connection, $orgImage, $srcFolder . $orgImage, FTP_BINARY);

    $skipArr = array();
    foreach ($_POST as $key => $value) {
        list($type, $code) = explode(';', $value);
        list(, $code) = explode(',', $code);
        $code = base64_decode($code);
        $size = $key;
        $skipArr[] = $size;
        $newFile = $pathinfo['filename'] . '_' . $size . '.' . $pathinfo['extension'];

        file_put_contents($srcFolder . $newFile, $code);
        //$uploadStatus = ftp_put($connection, $newFile, $srcFolder . $newFile, FTP_BINARY);
    }

    $insertIds = '';
    foreach ($ratioArr as $val) {
        foreach ($val['sizes'] as $k => $innerval) {

            //$source_image = $desFolder . $file_name_primary . '_' . $val['maxWidth'] . 'x' . $val['maxHeight'] . '.' . $file_name_ext;
            $source_image = $srcFolder . $pathinfo['filename'] . '_' . $val['maxWidth'] . 'x' . $val['maxHeight'] . '.' . $pathinfo['extension'];
            $destination_format = $pathinfo['extension'];
            $destination_path = $srcFolder;
            $destination_filename = $pathinfo['filename'] . '_' . $innerval;
            $destination_format = $pathinfo['extension'];
            $imgWidHtArr = explode("x", $innerval);
            $NewImageWidth = $imgWidHtArr[0];
            $NewImageHeight = $imgWidHtArr[1];
            $image_type_db_column = $val['name'][$k];

            $resizeImage = resizeImage($source_image, array($destination_path, $destination_filename, $destination_format), array($NewImageWidth, $NewImageHeight), $ecode, $emsgs);
            $uploadStatus = ftp_put($connection, basename($resizeImage), $resizeImage, FTP_BINARY);
            if ($uploadStatus) {
                $ctype = $_REQUEST['page'] . '#' . $_REQUEST['button'];
                $cropped = $folderName . '/' . basename($resizeImage);
                $date = date('Y-m-d H:m:s');
                $orgImagePath = $folderName . '/' . $orgImage;
                $insertQuery = "INSERT INTO crop_images (`content_type`, `image_type`, `org`, `size`, `cropped`, `added_on`, `updated_on`) values('$ctype', '$image_type_db_column', '$orgImagePath', '$innerval', '$cropped','$date', '$date')";
                $insertIds .= insertToDB($insertQuery) . ',';
            }
        }
    }
    header("location:" . SITE_URL . "view.php?ids=" . substr($insertIds, 0, -1) . "&imageName=" . $orgImage . "&page=" . $_REQUEST['page'] . '&button=' . $_REQUEST['button']);
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
                <?php
                for ($i = 0; $i < count($ratioArr); $i++) {
                    ;
                    ?>
                    <style>#cropit-preview_<?= $i + 1 ?> {width:<?= $ratioArr[$i]['maxWidth'] ?>px;height:<?= $ratioArr[$i]['maxHeight'] ?>px;}</style>
                    <div id="image-editor_<?= $i + 1 ?>" class="image-editor">
                        <h1>Edit Images <?= $ratioArr[$i]['ratio'] ?> </h1>
                        <div id="cropit-preview_<?= $i + 1 ?>" style="width:500;height:<?= $ratioArr[$i]['maxHeight'] ?>;" class="cropit-preview"></div>
                        <div class="range">Slide to zoom area <input type="range" class="cropit-image-zoom-input"></div>
                        <img style="width:25px;" class="rotate-cw_<?= $i + 1 ?>" src="assets/image/clockwise.png">
                        <img style="width:25px;" class="rotate-ccw_<?= $i + 1 ?>" src="assets/image/aclockwise.png">
                        <input type="hidden" name="<?= $ratioArr[$i]['maxWidth'] ?>x<?= $ratioArr[$i]['maxHeight'] ?>" id="image_image-editor_<?= $i + 1 ?>" value="">	
                    </div>
                    <script  src="script.php?ver=<?= $i + 1 ?>&imageName=<?= SITE_URL . MAIN_IMAGE_FOLDER . '/' . TEMP_IMAGE_FOLDER . '/' . date('Ym') . '/' . $_REQUEST['imageName']; ?>" ></script>
                <?php } ?>
                <button class="save" >Save</button>
                <input type="button" value="Cancel" onClick="javascript:window.location = '<?= SITE_URL ?>index.php?page=<?= $_REQUEST['page'] ?>&button=<?= $_REQUEST['button'] ?>'" class="cancel">
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