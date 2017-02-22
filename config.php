<?php 
$rootPath = __DIR__;
if (!defined('DS')) {define('DS', DIRECTORY_SEPARATOR);}

if (!defined('SITE_URL')) {define('SITE_URL', 'http://localhost/croptool-php-master/');}

if (!defined('MAIN_IMAGE_FOLDER')) {define('MAIN_IMAGE_FOLDER','cropped');}

if (!defined('IMAGES_PATH')) {define('IMAGES_PATH',$rootPath . DS . MAIN_IMAGE_FOLDER);}

if (!defined('TEMP_IMAGE_FOLDER')) {define('TEMP_IMAGE_FOLDER','temp');}

if (!defined('DESTINATION_IMAGE_FOLDER')) {define('DESTINATION_IMAGE_FOLDER','images');}

if (!defined('CURRENT_TIMESTAMUNIX')) {define('CURRENT_TIMESTAMUNIX',time());}

//// if not any cropped images folder created then will create.. note: this folder need full read/write permission.////
if (!file_exists(IMAGES_PATH) && !is_dir(IMAGES_PATH)) {
	mkdir(IMAGES_PATH);
}

//// if not any temp folder created then TEMP_IMAGE_FOLDER will create//////
if (!file_exists(IMAGES_PATH.DS.TEMP_IMAGE_FOLDER) && !is_dir(IMAGES_PATH.DS.TEMP_IMAGE_FOLDER)) {
	mkdir(IMAGES_PATH.DS.TEMP_IMAGE_FOLDER);
}

//// if not any current year-month folder created then will create//////
if (!file_exists(IMAGES_PATH.DS.TEMP_IMAGE_FOLDER.DS.date('Ym')) && !is_dir(IMAGES_PATH.DS.TEMP_IMAGE_FOLDER.DS.date('Ym'))) {
	mkdir(IMAGES_PATH.DS.TEMP_IMAGE_FOLDER.DS.date('Ym'));
}


/////if not any destination folder created then will create////////
if (!file_exists(IMAGES_PATH.DS.DESTINATION_IMAGE_FOLDER) && !is_dir(IMAGES_PATH.DS.DESTINATION_IMAGE_FOLDER)) {
	mkdir(IMAGES_PATH.DS.DESTINATION_IMAGE_FOLDER);
}

//// if not any current year-month folder created then will create//////
if (!file_exists(IMAGES_PATH.DS.DESTINATION_IMAGE_FOLDER.DS.date('Ym')) && !is_dir(IMAGES_PATH.DS.DESTINATION_IMAGE_FOLDER.DS.date('Ym'))) {
	mkdir(IMAGES_PATH.DS.DESTINATION_IMAGE_FOLDER.DS.date('Ym'));
}


$validationFile['size'] = array('max'=>2097152, 'errormsg'=>'File size must be lower than 2 MB.','reqWidth'=>'1048','reqHeight'=>'1048');
$validationFile['extension'] = array("jpeg", "jpg", "png","JPEG", "JPG", "PNG");




$fileRatioAndSizes = array(
            '16:9' => array(
                'extra_large_image' => '749x421', //New
                'large_image' => '618x347', //New
				'home_page_small_card_image'=>'555x313', //New
				'home_page_small_card_image4'=>'455x256', //New
                'kicker_image' => '299x168', // New
                'kicker_image2' => '260x147', //New
				'home_page_small_card_image3'=>'127x72',
                'thumb_image' => '98x55'//both
            ),
            '1:1' => array(
                'old_small_kicker_img' => '155x155', //Old
				'home_page_big_card_image5'=>'554x554', //New
				'home_page_big_card_image6'=>'262x262', //New
				'home_page_big_card_image7'=>'362x362' //New
            ),
            '4:3' => array(
				'kicker_image3'=>'360x270', //New
                'old_kicker_image' => '325x244', //Old
                'old_large_kicker_image' => '406x228', //Old
                'old_large_kicker_image2' => '650x488' //Old
            )
);




function validateMaxImgWidthHeight() {
	global $validationFile, $fileRatioAndSizes;
	$cropSizes = $fileRatioAndSizes;
	$widthArr = array();
    $heightArr = array();
	foreach ($cropSizes as $key => $size) {
		foreach ($size as $k => $v) {
			$sizeExp = explode('x', $v);
			$sizeArr[$v] = $sizeExp[0];
            $sizeNameArr[$v] = $k;
            $widthArr[] = $sizeExp[0];
            $heightArr[] = $sizeExp[1];
		}
	}
    $validationFile['size']['reqWidth'] = max($widthArr);
    $validationFile['size']['reqHeight'] = max($heightArr);
}

validateMaxImgWidthHeight();



function getImageMaxWidthHeight_Each_Ratio() {
	global $validationFile, $fileRatioAndSizes;
	$cropSizes = $fileRatioAndSizes;
	//echo '<pre>'; print_r($cropSizes);
	
	$returnData = array();
	$i=0;
	foreach ($cropSizes as $key => $size) {
		$returnData[$i] = array();
		$x=0;
		$maxWidth = 0;
		$maxHeight = 0;
		foreach ($size as $k => $v) {
			$width_height_arr = explode('x', $v);
			$maxWidth = $width_height_arr[0] > $maxWidth ? $width_height_arr[0] : $maxWidth;
			$maxHeight = $width_height_arr[1] > $maxHeight ? $width_height_arr[1] : $maxHeight;
			$returnData[$i]['sizes'][]=$v; 
			$x++;
		}
		$returnData[$i]['maxWidth'] = $maxWidth;
		$returnData[$i]['maxHeight'] = $maxHeight;
		$returnData[$i]['ratio'] = $key;
		$i++;
	}
	
	//echo '<pre>'; print_r($returnData); die;

	/*
	$ratioArr = array();
	$widthArr = array();
    $heightArr = array();
	foreach ($cropSizes as $key => $size) {
		$ratioArr[] = $key;
		foreach ($size as $k => $v) {
            $width = explode('x', $v);
            $sizeArr[$key][$v] = $width[0];
			$sizeNameArr[$v] = $k;
    	}
	}
    
	$maxSizeArr = array();
	foreach ($ratioArr as $key => $val) {
		$maxVal = max($sizeArr[$val]);
		$size = array_search($maxVal, $sizeArr[$val]);
		foreach ($sizeArr[$val] as $k => $v) {
			if ($size != $k) {
				$maxSizeArr[$size][$v] = $k;
			}
		}
	}
	*/
	return $returnData;
}




function getValidFilenameFrom($filename) {
    return preg_replace('/[^a-zA-Z0-9_.]/', '_', $filename); 
}

function resizeImage($src_file, array $dst, array $dst_size = array(), &$ecode = null, &$emsgs = null) {
    /* parameter:
      $src_file:      (string) source path + filename -- path/path/filename.extension
      $dst:           (array) destination array(
      0 => (string){path},
      1 => (string){name},
      2 => (string){format 'png', 'jpeg', 'gif' or 'wbmp'}
      )
      $dst_size:      (array) resize image to array(0 => (int){width}, 1 => (int){height})
      &$ecode:         (int) error code
      &$emsgs:         (string) error message
      returns: (bool)
     */
    // check source file
    $src_file = (string) $src_file;
    if (!file_exists($src_file)) {
        $ecode = 10;
        $emsgs = 'source filename "' . $src_file . '" does not exist';
        return false;
    }
    if (!is_file($src_file)) {
        $ecode = 11;
        $emsgs = 'source filename "' . $src_file . '" is not a file';
        return false;
    }
    if (!is_readable($src_file)) {
        $ecode = 12;
        $emsgs = 'source filename "' . $src_file . '" is not readable';
        return false;
    }
    // check destination path
    $dst_path = (string) $dst[0];
    if (strlen($dst_path) > 0) {
        $dst_path = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), (string) $dst_path);
        if (substr($dst_path, -1) !== DIRECTORY_SEPARATOR) {
            $dst_path .= DIRECTORY_SEPARATOR;
        }
    }
    if (!file_exists($dst_path)) {
        $ecode = 20;
        $emsgs = 'destination path "' . $dst_path . '" does not exist';
        return false;
    }
    if (!is_dir($dst_path)) {
        $ecode = 21;
        $emsgs = 'destination path "' . $dst_path . '" is not a directory';
        return false;
    }
    if (!is_readable($dst_path)) {
        $ecode = 22;
        $emsgs = 'destination path "' . $dst_path . '" is not readable';
        return false;
    }
    // check destination filename
     $dst_name = (string) $dst[1];
	
	
    if ($dst_name !== getValidFilenameFrom($dst_name)) {
        $ecode = 30;
        $emsgs = 'invalid destination filename "' . $dst_name . '"';
        return false;
    }
    if (strlen($dst_name) < 1) {
        $ecode = 31;
        $emsgs = 'destination filename cannot be empty';
        return false;
    }
    // check destination format (plus set callable $call)
    $dst_format = strtolower((string) $dst[2]);
    if ($dst_format === 'png') {
        $call = 'imagepng';
    } elseif ($dst_format === 'jpeg' || $dst_format === 'jpg') {
        $call = 'imagejpeg';
    } elseif ($dst_format === 'gif') {
        $call = 'imagegif';
    } elseif ($dst_format === 'wbmp') {
        $call = 'imagewbmp';
    } else {
        // wrong format given
        $ecode = 40;
        $emsgs = 'invalid destination format "' . $dst_format . '"';
        return false;
    }
    // prepare destination size
    $dst_size = array(
        isset($dst_size[0]) ? $dst_size[0] : null,
        isset($dst_size[1]) ? $dst_size[1] : null,
    );

    // get source image raw data
    if (($src_raw = file_get_contents($src_file)) === false) {
        // unable to read source
        $ecode = 100;
        $emsgs = 'unable to read source filename "' . $src_file . '"';
        return false;
    }

    // create new image from source
    if (($src_image = imagecreatefromstring($src_raw)) === false) {
        // must be something else than a picture
        $ecode = 101;
        $emsgs = 'destination filename "' . $src_file . '" is not an image';
        return false;
    }

    // get current size
    $src_size = getimagesize($src_file);

    // get new size
    //$dst_size = ratioResize($src_size, $dst_size, false);
//print_r($dst_size);
    // create new image
    if (($dst_image = imagecreatetruecolor($dst_size[0], $dst_size[1])) === false) {
        $ecode = 102;
        $emsgs = 'unable to create new true color image';
        return false;
    }

    // transparency
    if (imagesavealpha($dst_image, true) === false) {
        $ecode = 103;
        $emsgs = 'unable to set flag to save full alpha channel information';
        return false;
    }
    if (($transparency = imagecolorallocatealpha($dst_image, 0, 0, 0, 127)) === false) {
        $ecode = 104;
        $emsgs = 'unable to allocate a color for an image';
        return false;
    }
    imagefill($dst_image, 0, 0, $transparency);

    // copy and resize
    if (
            imagecopyresampled(
                    $dst_image, $src_image, 0, 0, 0, 0, $dst_size[0], $dst_size[1], $src_size[0], $src_size[1]
            ) === false
    ) {
        $ecode = 105;
        $emsgs = 'unable to copy and resize image';
        return false;
    }

    // write
    if (call_user_func_array($call, array($dst_image, $dst_path . $dst_name . '.' . $dst_format)) === false) {
        // unable to write file
        $ecode = 106;
        $emsgs = 'unable to write image to destination';
        return false;
    }
    imagedestroy($src_image);
    imagedestroy($dst_image);
    return true;
}

$compress_image_quality = 70;
function compress($source, $destination, $quality = 70) {
    $info = getimagesize($source);
    if ($info['mime'] == 'image/jpeg')
        $image = imagecreatefromjpeg($source);
    elseif ($info['mime'] == 'image/gif')
        $image = imagecreatefromgif($source);
    elseif ($info['mime'] == 'image/png')
        $image = imagecreatefrompng($source);
    imagejpeg($image, $destination, $quality);
    return $destination;
}