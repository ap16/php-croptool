<?php
require_once('config.php');

if (isset($_FILES['image'])) {
    //print_r($_FILES);
    $errors = array();
    $file = $_FILES['image']['tmp_name'];
    $file_size = $_FILES['image']['size'];

    if (file_exists($file)) {
        
        compress($file, $file, $compress_image_quality);
        
        $imagesizedata = getimagesize($file);
        if ($imagesizedata === FALSE) {
            $errors[] = 'Not a valid Image';
        } else {
            if ($file_size > $validationFile['size']['max']) {
                $errors[] = $validationFile['size']['errormsg'];
            }
        }
    } else {
        $errors[] = 'Not any file uploaded';
    }


    if (empty($errors)) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];

        $pathInfo = pathinfo($file_name);
        $file_ext = strtolower($pathInfo['extension']);
        $file_name_filter = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '_', $pathInfo['filename']));
        $new_file_name = $file_name_filter . '_' . CURRENT_TIMESTAMUNIX . '.' . $file_ext;

        $destination = IMAGES_PATH . DS . TEMP_IMAGE_FOLDER . DS . date('Ym');
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $status = move_uploaded_file($file_tmp, $destination . DS . $new_file_name);
        if (!$status)
            die('Image Upload Failed');
        //echo "Success";
        //echo SITE_URL."/resize.php?imageName=".$name.'.'.$file_ext.'&site_id='.$site_id.'&content_type='.$content_type;die;
        header("location:" . SITE_URL . "resize.php?imageName=" . $new_file_name . "&page=" . $_REQUEST['page'] . '&button=' . $_REQUEST['button']);
    } else {
        foreach ($errors as $er) {
            $errorDisplay = $er . '<br/>';
        }
        $errorDisplay = "<div class='error' style='color:red;margin-bottom:10px;'>{$errorDisplay}</div>";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>cropit</title>
        <script src="assets/js/jquery.min.js"></script>
    </head>
    <body>


        <style>
            .main-container{
                position: absolute;
                top:50px;
                bottom: 0;
                left: 150px;
                right: 0;
                margin: auto;
            }
            .form_div{
                margin-top:20px;
            }
        </style>
        <div class="main-container">
            <?php
            if (!empty($errors)) {
                echo $errorDisplay;
            }
            ?>
            <div class="form_div">
                <form name="imageUpload"  method="POST" enctype="multipart/form-data" onSubmit="return Checkfile()">
                    <input type="file" id="filename" name="image" />
                    <input type="hidden" name="folder" value="<? //= $_REQUEST['folder'];            ?>" />
                    <input type="hidden" name="reqWidth" id="reqWidth" value="<?= $validationFile['size']['reqWidth'] ?>" />
                    <input type="hidden" name="reqHeight" id="reqHeight" value="<?= $validationFile['size']['reqHeight'] ?>" />
                    <input type="hidden" name="orgWidth" id="orgWidth" value="" />
                    <input type="hidden" name="orgHeight" id="orgHeight" value="" />
                    <input type="submit" value="Go" />
                </form>
            </div>
        </div>
    </body>
<script type="text/javascript" language="javascript">
var validateMaxSize = <?= $validationFile['size']['max'] ?>;
var validateMaxSizeDisplayError = '<?= $validationFile['size']['errormsg'] ?>';
</script>
<script src="assets/js/custom.js"></script>
</html>