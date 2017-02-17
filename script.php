	  $(function() {
	  var imageSrc ='<?=$_REQUEST['imageName']?>';
	 $('#image-editor_<?=$_REQUEST['ver']?>').cropit({ exportZoom: 1,
          imageBackground: true,
          imageBackgroundBorderWidth: 50,imageState: { src: imageSrc  } });
        $('.rotate-cw_<?=$_REQUEST['ver']?>').click(function() {
          $('#image-editor_<?=$_REQUEST['ver']?>').cropit('rotateCW');
		  return false;
        });
        $('.rotate-ccw_<?=$_REQUEST['ver']?>').click(function() {
          $('#image-editor_<?=$_REQUEST['ver']?>').cropit('rotateCCW');
		  return false;
        });
      });
