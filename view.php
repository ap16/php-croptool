<?php
require_once("config.php");
require_once("db.config.php");
$ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : '';
$data = array();
if (!empty($ids)) {
    $data = getData($ids);
} else {
    echo 'Invalid Data';
    exit();
}

$ratioArr = getImageMaxWidthHeight_Each_Ratio();

function getDbData($size) {
    global $data;
    $dbData['imgUrl'] = 'assets/image/no-image-available.jpg';
    foreach ($data as $row) {
        if ($row['size'] == $size) {
            $dbData['imgUrl'] = MEDIA2_CROPIT_URL . $row['cropped'];
            $dbData['pathinfo'] = pathinfo($row['org']);
            $dbData['size'] = $row['size'];
            $dbData['id'] = $row['id'];
            break;
        }
    }
    return $dbData;
}

//print_r($ratioArr);
//print_r($data);
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
            if (parent.document.getElementById('tmp_content_id').value == '') {
                parent.document.getElementById('tmp_content_id').value = '<?= $ids ?>';
            }

        </script>
        <style>
            .item { border:0px solid gray; width: 120px; float: left; margin: 3px; padding: 3px; font-size:12px; }
        </style>
    </head>
    <body>
        <div class="main-container">
            <!--<a href="javascript:void(0);" onClick="dld()">Download All</a>-->
            <?php foreach ($ratioArr as $ratio) { ?>
                <h3>Ratio : <?= $ratio['ratio'] ?></h3><hr>
                <?php foreach ($ratio['sizes'] as $k => $ratioSizes) { ?>
                    <?php $dbData = getDbData($ratioSizes);
                    $imgUrl = $dbData['imgUrl'];
                    ?>
                    <div class="item">
                        <img style="height:70px; width:70px;" src="<?= $imgUrl ?>"><br><b><?= $ratio['name'][$k] ?> (<?= $ratioSizes ?>)</b><br>
                        <a href="<?= $imgUrl ?>" target="_blank" > View </a> | 
                        <a href="javascript:void(0);" onClick="downloadImg('<?= $imgUrl ?>', '<?= $dbData['pathinfo']['filename'] . '_' . $dbData['size'] . '.' . $dbData['pathinfo']['extension'] ?>')" > Download </a> | 
                        <a href="edit.php?id=<?= $dbData['id'] ?>&ids=<?= $ids ?>&page=<?= $_REQUEST['page'] ?>&button=<?= $_REQUEST['button'] ?>"> Edit </a>
                    </div>
                <?php } ?>
                <div style="clear:both"></div>
<?php } ?>
        </div>


    </body>
</html>