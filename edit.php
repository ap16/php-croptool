<?php
require_once("config.php");
require_once("db.config.php");
$id = isset($_REQUEST['id']) ? $_REQUEST['id'] : '';
$data = array();
if (!empty($id)) {
    $data = getData($id);
    list($reqWidth, $reqHeight) = explode('x', $data[0]['size']);
    list($folderName, $imageName) = explode('/', $data[0]['org']);
    $orgImgName = $data[0]['org'];
} else {
    echo 'Invalid Data';
    exit();
}

if ($_POST) {

    if (!empty($_FILES['image']['name'])) {
        getImageMaxWidthHeight_Each_Ratio();

        $errors = array();
        $tmpFile = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $pathInfo = pathinfo($_FILES['image']['name']);
        $file_ext = strtolower($pathInfo['extension']);
        $file_name_filter = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '_', $pathInfo['filename']));
        $new_file_name = $imageName = $file_name_filter . '_' . CURRENT_TIMESTAMUNIX . '.' . $file_ext;
        $orgImgName = $folderName . '/' . $new_file_name;

        $imagesizedata = getimagesize($tmpFile);
        if ($imagesizedata === FALSE) {
            $errors[] = 'Not a valid Image';
        } else {
            if ($file_size > $validationFile['size']['max']) {
                $errors[] = $validationFile['size']['errormsg'];
            }
        }

        if (empty($errors)) {

            $destination = IMAGES_PATH . DS . TEMP_IMAGE_FOLDER . DS . $folderName . DS . $new_file_name;
            $status = move_uploaded_file($tmpFile, $destination);
            if ($status) {
                $connection = ftp_connect(MEDIA2_SERVER) or die("Unable to connect FTP:" . MEDIA2_SERVER);
                $login = ftp_login($connection, MEDIA2_USER_NAME, MEDIA2_USER_PASS) or die('Media2 Login attempt failed!');
                ftp_chdir($connection, MEDIA2_CROPIT_PATH . $folderName);
                ftp_put($connection, $new_file_name, $destination, FTP_BINARY);
                ftp_close($connection);
            } else {
                die('Image Upload Failed');
            }
        }
    } else {
        if (!empty($_POST['data_image'])) {

            $pathinfo = pathinfo($_POST['org_img_name']);
            $newImageName = $pathinfo['filename'] . rand(1, 100) . '_' . $data[0]['size'] . '.' . $pathinfo['extension'];
            $srcFolder = IMAGES_PATH . DS . TEMP_IMAGE_FOLDER . DS . $folderName . DS;

            list($type, $newCode) = explode(';', $_POST['data_image']);
            list(, $newCode) = explode(',', $newCode);
            $newCode = base64_decode($newCode);
            file_put_contents($srcFolder . $newImageName, $newCode);

            $connection = ftp_connect(MEDIA2_SERVER) or die("Unable to connect FTP:" . MEDIA2_SERVER);
            $login = ftp_login($connection, MEDIA2_USER_NAME, MEDIA2_USER_PASS) or die('Media2 Login attempt failed!');
            ftp_chdir($connection, MEDIA2_CROPIT_PATH . $folderName);
            ftp_put($connection, $newImageName, $srcFolder . $newImageName, FTP_BINARY);
            ftp_close($connection);

            $sql = "update crop_images set org = '" . ($folderName . '/' . $_POST['org_img_name']) . "', cropped = '" . ($folderName . '/' . $newImageName) . "', updated_on = '" . date('Y-m-d H:i:s') . "' where id =" . $id;
            excuteQuery($sql);

            header("location:" . SITE_URL . "view.php?ids=" . $_REQUEST['ids'] . "&imageName=" . $newImageName . "&page=" . $_REQUEST['page'] . '&button=' . $_REQUEST['button']);
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>cropit</title>
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/jquery.cropit.js"></script>
        <script type="text/javascript" language="javascript">
            var validateMaxSize = <?= $validationFile['size']['max'] ?>;
            var validateMaxSizeDisplayError = '<?= $validationFile['size']['errormsg'] ?>';
        </script>
        <script src="assets/js/custom.js"></script>
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
            <div class="form_div">
                <form name="imageUpload"  method="POST" enctype="multipart/form-data" onSubmit="return Checkfile()">
                    <input type="file" id="filename" name="image" />
                    <input type="hidden" name="folder" value="<? //= $_REQUEST['folder'];                                             ?>" />
                    <input type="hidden" name="reqWidth" id="reqWidth" value="<?= $reqWidth ?>" />
                    <input type="hidden" name="reqHeight" id="reqHeight" value="<?= $reqHeight ?>" />
                    <input type="hidden" name="orgWidth" id="orgWidth" value="" />
                    <input type="hidden" name="orgHeight" id="orgHeight" value="" />
                    <input type="submit" value="Go" />
                </form>
            </div>

            <form name="zoom_crop_image" action="" method="post">
                <style>
                    #cropit-preview_1 {width:<?= $reqWidth ?>px;height:<?= $reqHeight ?>px;}
                </style>
                <div id="image-editor_1" class="image-editor">
                    <h1>Edit Image
                        <?= $ratioArr[$i]['ratio'] ?>
                    </h1>
                    <div id="cropit-preview_1" style="width:500;height:<?= $reqHeight ?>;" class="cropit-preview"></div>
                    <div class="range">Slide to zoom area
                        <input type="range" class="cropit-image-zoom-input">
                    </div>
                    <img style="width:25px;" class="rotate-cw_1" src="assets/image/clockwise.png"> <img style="width:25px;" class="rotate-ccw_1" src="assets/image/aclockwise.png">
                    <input type="hidden" name="data_image" id="image_image-editor_1" value="">
                    <input type="hidden" name="org_img_name" id="org_img_name" value="<?= $imageName ?>">
                </div>
                <script  src="script.php?ver=1&imageName=<?= MEDIA2_CROPIT_URL . $orgImgName; ?>" ></script>
                <button class="save" style="float:left">Save</button>
                <input type="button" value="Cancel" onClick="javascript:window.history.go(-1);" class="cancel" style="float: left; margin-top: 10px; margin-left: 13px;">
            </form>
        </div>
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
    </body>
</html>
