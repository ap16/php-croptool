<?php
require_once('config.php');

if (isset($_FILES['image'])) {
    //print_r($_FILES);
    $errors = array();
    $file = $_FILES['image']['tmp_name'];
    $file_size = $_FILES['image']['size'];

    if (file_exists($file)) {
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
        $file_ext = @strtolower(end(explode('.', $_FILES['image']['name'])));
        $file_name_filter = strtolower(preg_replace('/[^a-zA-Z0-9_.]/', '_', basename($file_name, '.' . $file_ext)));
        $new_file_name = $file_name_filter . '_' . CURRENT_TIMESTAMUNIX . '.' . strtolower($file_ext);

        $destination = IMAGES_PATH . DS . TEMP_IMAGE_FOLDER . DS . date('Ym');
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $status = move_uploaded_file($file_tmp, $destination . DS . $new_file_name);
        if (!$status)
            die('Image Upload Failed');
        //echo "Success";
        //echo SITE_URL."/resize.php?imageName=".$name.'.'.$file_ext.'&site_id='.$site_id.'&content_type='.$content_type;die;
        header("location:" . SITE_URL . "resize.php?imageName=" . $new_file_name);
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
                    <input type="hidden" name="folder" value="<? //= $_REQUEST['folder'];        ?>" />
                    <input type="hidden" name="reqWidth" id="reqWidth" value="<?= $validationFile['size']['reqWidth'] ?>" />
                    <input type="hidden" name="reqHeight" id="reqHeight" value="<?= $validationFile['size']['reqHeight'] ?>" />
                    <input type="hidden" name="orgWidth" id="orgWidth" value="" />
                    <input type="hidden" name="orgHeight" id="orgHeight" value="" />
                    <input type="submit" value="Go" />
                </form>
            </div>
        </div>

        <script type="text/javascript">
            function Checkfile()
            {

                var fup = document.getElementById('filename');
                var fileName = fup.value;
                if (fileName == "")
                {
                    alert('Please upload image.');
                    return false;
                }

                if (document.getElementById('orgWidth').value == '' || document.getElementById('orgHeight').value == '') {
                    alert('Unknown Image');
                    return false;
                }

            }

            var _URL = window.URL || window.webkitURL;

            $("#filename").change(function (e) {
                var file, img, fileHandle;
                fileHandle = this;

                document.getElementById('orgWidth').value = '';
                document.getElementById('orgHeight').value = '';

                if ((file = this.files[0])) {
                    var fileSize = parseInt(this.files[0].size);
                    if (fileSize > <?= $validationFile['size']['max'] ?>) {
                        alert('<?= $validationFile['size']['errormsg'] ?>');
                        fileHandle.value = '';
                        return false;
                    }
                    img = new Image();
                    img.onload = function () {
                        //alert(this.width + " " + this.height);
                        var width = parseInt($("#reqWidth").val());
                        var height = parseInt($("#reqHeight").val());
                        if (parseInt(this.width) < width) {
                            alert("Image width must be greater than " + width + "px.");
                            fileHandle.value = '';
                            return false;
                        }
                        if (parseInt(this.height) < height) {
                            alert("Image Height must be greater than " + height + "px.");
                            fileHandle.value = '';
                            return false;
                        }
                        document.getElementById('orgWidth').value = this.width;
                        document.getElementById('orgHeight').value = this.height;
                    };
                    img.onerror = function () {
                        alert("not a valid file: " + file.type);
                        fileHandle.value = '';
                        return false;
                    };
                    img.src = _URL.createObjectURL(file);


                }

            });
        </script>
    </body>
</html>