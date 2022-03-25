<?php



namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use Zend\Db\Adapter\Adapter;

//class Application_Plugin_Image

use RecursiveDirectoryIterator;

use RecursiveIteratorIterator;

use RegexIterator;

class Image extends AbstractPlugin

{	

	/*

	 *	Univeral Image Uploader

	 */

	public function universal_upload($options = array(),$UploadType="Image"){

		

		$ImageCrop = new \Application\Controller\Plugin\ImageCrop(); 

	

  		$return_single_image = true ;

		

		if(isset($options['multiple']) and $options['multiple']){

			$return_single_image = false ; 

 		}

		

		if(!isset($options['directory']) or !is_dir($options['directory']))

			return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"No Such Directory Exists ",'files_upload'=>0,'media_path'=>array());

			

		$uploaded_image_names = array();

	

		$adapter = new \Zend\File\Transfer\Adapter\Http(); 

		

		$files = $adapter->getFileInfo();

		$uploaded_image_names = array();		

		$new_name = false; 


		$files_array = array_keys($options['files_array']);


		foreach ($files as $file => $info) {

			if(in_array($file, $files_array)){

			  /* Begin Foreach for handle multiple images */

			if(isset($files[$file]['tmp_name']) and $files[$file]['tmp_name']!='' and file_exists($files[$file]['tmp_name']))
			{

			$finfo = finfo_open(FILEINFO_MIME_TYPE); 

			$uploaded_image_extension = getFileExtension($files[$file]['name']);

			

			$typeval=finfo_file($finfo, $files[$file]['tmp_name']);

			

			finfo_close($finfo);	
			if(in_array($uploaded_image_extension,array("png","PNG","jpg","JPG","jpeg","JPEG","svg","SVG","ico","ICO"))&& (!($typeval=='image/jpeg'  || $typeval=='image/png' || $typeval=='image/svg'|| $typeval=='image/svg+xml'|| $typeval=='image/ico' || $typeval=='image/x-icon'))){
				return (object) array('success'=>false,"error"=>true,'exception'=>true,'message'=>"Invalid Image",'exception_code'=>"Invalid Image") ;

			}		
			if($UploadType=="Both"){
				//($typeval=='image/jpeg'  || $typeval=='image/png')
				if((!($typeval=='application/vnd.openxmlformats-officedocument.wordprocessingml.document'  || $typeval=='application/pdf' || $typeval=='application/msword' || $typeval=='image/png' || $typeval=='image/jpeg' ||$typeval=='image/svg'|| $typeval=='image/svg+xml' || $typeval=="application/octet-stream" || $typeval == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $typeval == 'application/zip'))){

					return (object) array('success'=>false,"error"=>true,'exception'=>true,'message'=>"Invalid File",'exception_code'=>"Invalid File") ;

				}

			}elseif($UploadType=="Doc"){	
			if((!($typeval=='application/vnd.openxmlformats-officedocument.wordprocessingml.document'  || $typeval=='application/pdf' || $typeval=='application/msword' || $typeval=="application/octet-stream" || $typeval == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $typeval == 'application/zip'))){

				return (object) array('success'=>false,"error"=>true,'exception'=>true,'message'=>"Invalid File",'exception_code'=>"Invalid File") ;

			}
			}
			}
			 /* code to check invalid image */

			 $target_file_ck = basename($files[$file]['name']);
			if($target_file_ck!=""){

			 $imageFileType = strtolower(pathinfo($target_file_ck,PATHINFO_EXTENSION));
			 if($UploadType=="Image"){

				if(!in_array($imageFileType,array("png","PNG","jpg","JPG","jpeg","JPEG","svg","SVG","ico","ICO"))){

				return (object) array('success'=>false,"error"=>true,'exception'=>true,'message'=>"Invalid Image",'exception_code'=>"Invalid Image") ;

				}	

			}elseif($UploadType=="Doc"){
				if(!in_array($imageFileType,array("doc","docx","pdf","PDF","txt","TXT","DOC","DOCX","xlsx","XLSX","zip","ZIP"))){

				return (object) array('success'=>false,"error"=>true,'exception'=>true,'message'=>"Invalid Image",'exception_code'=>"Invalid File") ;

				}	 
			}

			}

  			$name_old = $adapter->getFileName($file);

			if(empty($name_old)){

				continue ;			

			}

			$file_title  = $adapter->getFileInfo($file);

			if($file_title[$file]['size']==0)

				continue; 

			

			$file_title = $file_title[$file]['name']; 

  			$uploaded_image_extension = getFileExtension($name_old);
			

 			$file_title  = str_replace(".".$uploaded_image_extension,"",$file_title);

			

			$file_title = formatImageName($file_title);

  
			$new_name = time()."-".rand(1,100000).".".$uploaded_image_extension;
  			$adapter->addFilter('Rename',array('target' => $options['directory']."/".$new_name));

			

			try{

				$adapter->receive($file);

				

				//if(!empty($options['url'])){


					if($uploaded_image_extension=='jpg' || $uploaded_image_extension=='JPG' || $uploaded_image_extension=='jpeg' || $uploaded_image_extension=='JPEG'){

						$new_name = image_fix_orientation($options['directory']."/".$new_name,$options['directory'],$new_name);
						
						

					}

				//}

			}

			catch(Zend_Exception $e){

				return (object) array('success'=>false,"error"=>true,'exception'=>true,'message'=>$e->getMessage(),'exception_code'=>$e->getCode()) ;

			}

			$thumb_config = array("source_path"=>$options['directory'],"name"=> $new_name);
			if(!in_array(strtolower($uploaded_image_extension),unserialize(THUMB_FALSE))){ 
			if(isset($options['thumbs']) and is_array($options['thumbs'])){



				foreach($options['thumbs'] as $key=>$value){

					

					$width = isset($value['size'])?$value['size']:(isset($value['width'])?$value['width']:"220");

					$height = isset($value['height'])?$value['height']:(isset($value['ratio'])?$value['ratio']*$width:"330");

					$ImageCrop->uploadThumb(array_merge($thumb_config,

						array(

							"width"=>$width,

							"height"=>$height,

							"crop"=>true,

							"ratio"=>false,

							"destination_path"=>$options['directory']."/$key")

						));

				}

				

 			}else{

				

				$ext = getFileExtension($thumb_config['name']);

				if($ext == 'jpg' or $ext == 'png' or $ext == 'jpeg' or $ext == 'JPG' or $ext == 'JPEG' or $ext == 'PNG'){

					$ImageCrop->uploadThumb(array_merge($thumb_config));

					$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>$options['directory']."/60","crop"=>true ,"size"=>60,"ratio"=>true)));

					if($options['directory'] == PRODUCT_PIC_PATH) {
						$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>$options['directory']."/160","crop"=>true ,"size"=>160,"ratio"=>true)));

						$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>$options['directory']."/240","crop"=>true ,"width"=>240,"height"=>250,"ratio"=>true)));
					} else {
						$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>$options['directory']."/160","crop"=>true ,"size"=>160,"ratio"=>true)));

						$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>$options['directory']."/240","crop"=>true ,"width"=>240,"height"=>250,"ratio"=>true)));
					}
					$ImageCrop->uploadThumb(array_merge($thumb_config,array("destination_path"=>$options['directory']."/thumb","crop"=>true ,"size"=>400,"ratio"=>true)));

					

				}

				

			}

			}	
 			if(!$return_single_image){
				$uploaded_image_names[$file]=array('media_path'=>$new_name,'element'=>$file,'name'=>$new_name); //=> For Multiple Images

			}

  		
		}
			

		}/* End Foreach Loop for all images */
		if(!$return_single_image){
			return (object)array("success"=>true,'error'=>false,"message"=>"Image(s) Successfully Uploaded","media_path"=>$uploaded_image_names) ;

		}	

		return (object)array("success"=>true,'error'=>false,"message"=>"Image(s) Successfully Uploaded","media_path"=>$new_name) ;

 	}

	

	

	/* 

	 *	Universal Unlink Image

	 */

	public function universal_unlink($image_name , $options = array()){

		

		if(empty($image_name))

			return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"Image Name is Empty",'files_unlink'=>0);

		

		if($options!="" and !is_array($options)){

			$options = array('directory'=>$options) ;

 		} 

 		

		if(!isset($options['directory']) or !is_dir($options['directory']))

			return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"No Such Directory Exists ",'files_unlink'=>0);

			

		

   		$directory = new RecursiveDirectoryIterator($options['directory']); 

		$flattened = new RecursiveIteratorIterator($directory);

		

		 

		// Make sure the path does not contain "/.Trash*" folders and ends eith a .php or .html file

		//$files = new RegexIterator($flattened, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:php|html|jpg)$#Di');

		

		$image_name = str_replace(array("(",")"),array("\(","\)"),$image_name);

		

  		$files = new RegexIterator($flattened, "/$image_name/");

		

 		$unlink_count = 0;

		

		foreach($files as $file) {

 			unlink($file);

 			$unlink_count++;

		}

 		

		return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"Images are successfully removed",'files_unlink'=> $unlink_count );

		

	}

	

	

	/* 

	 *	Universal Unlink Image

	 */

	public function universal_rename($old_name , $new_name, $options = array()){

		

		if(empty($old_name) or empty($new_name))

			return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"Old Name and New Name is Empty",'files_unlink'=>0);

		

		if($options!="" and !is_array($options)){

			$options = array('directory'=>$options) ;

 		}

 		

		if(!isset($options['directory']) or !is_dir($options['directory']))

			return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"No Such Directory Exists ",'files_unlink'=>0);

			

		 

   		$directory = new RecursiveDirectoryIterator($options['directory']);

		$flattened = new RecursiveIteratorIterator($directory);

		

		 

		// Make sure the path does not contain "/.Trash*" folders and ends eith a .php or .html file

		//$files = new RegexIterator($flattened, '#^(?:[A-Z]:)?(?:/(?!\.Trash)[^/]+)+/[^/]+\.(?:php|html|jpg)$#Di');

 		$image_name = str_replace(array("(",")"),array("\(","\)"),$old_name);

		

  		$files = new RegexIterator($flattened, "/$image_name/");

		

 		$unlink_count = 0;

		

		foreach($files as $file) {

			

 			$unlink_count++;

		}

		

 		

		return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"Images are successfully removed",'files_unlink'=> $unlink_count );

		

	}

	

	

	

	

	public function simple_rename($old_name , $new_name, $options = array()){

		

		if(empty($old_name) or empty($new_name))

			return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"Old Name and New Name is Empty",'files_unlink'=>0);

		

		if($options!="" and !is_array($options)){

			$options = array('directory'=>$options) ;

 		}

 		

		if(!isset($options['directory']) or !is_dir($options['directory']))

			return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"No Such Directory Exists ",'files_unlink'=>0);

			

		 

  		

		if(file_exists($options['directory']."/".$old_name)){

 			rename($options['directory']."/".$old_name,$options['directory']."/".$new_name);

 		}

  		

		return (object)array('success'=>true,'error'=>false,'exception'=>false ,'message'=>"Images are successfully removed",'files_rename'=>1);

		

	}

	

 

	/*

	 *	Universal Upload Image Thumbs

	 */

	public function univeral_upload_thumb(){

		

		

	}

	

	

	public function universal_crop_image($options = array()){

		

		

		/* Parameters Validation */

		if(!isset($options['_w']) or !isset($options['_h']) or !isset($options['_x']) or !isset($options['_y']) or !isset($options['source_directory']) or !isset($options['name'])){

			return (object)array('success'=>false , 'error'=>true ,'message'=>"Missing Required Arguments , Please Check Again!");

		}

		

 		/* Setup For Default Values  */

		!isset($options['quality'])?$options['quality']=75:"";

		!isset($options['target_name'])?$options['target_name']=$options['name']:"";

		!isset($options['destination'])?$options['relative_path']=true:"";

		!isset($options['destination'])?$options['destination']="":"";

	 

 		 

 		if(is_array($options['destination'])){

			

			foreach($options['destination'] as $key=>$values){

				

				$width = $height = 160 ;

				$target_name = $options['target_name'] ;

							

				if(isset($options['relative_path']) and $options['relative_path']==false){

					$destination_path = $values['destination'];

				}else{

					$destination_path = $options['source_directory'].'/'.$key ;

				}

				

				if(isset($values['size']) and !empty($values['size'])){

					$width = $height = $values['size'] ;

 				}

				

 				if(isset($values['width']) and !empty($values['width'])){

					$width = $values['width'] ;

 				}

				

				if(isset($values['height']) and !empty($values['height'])){

					$height = $values['height'] ;

 				}

				

				if(isset($values['target_name']) and !empty($values['target_name'])){

					$target_name = $values['target_name'] ;

 				}

				

				 

  				$this->_crop(array(

								'_w'=>$options['_w'],

								'_h'=>$options['_h'],

								'_x'=>$options['_x'],

								'_y'=>$options['_y'],

								'quality'=>$options['quality'],

								'source_path'=>$options['source_directory']."/".$options['name'],

								'destination_path'=>$destination_path,

								'target_name'=>$target_name,

								'width'=>$width,

								'height'=>$height

 							)

						);

  			}

   		}else{

			

				$width = $height = 160 ;

				$target_name = $options['target_name'] ;

							

				if(isset($options['relative_path']) and $options['relative_path']==false){

					$destination_path = $options['destination'];

				}else{

					$destination_path = $options['source_directory'].'/' ;

				}

				

				if(isset($options['size']) and !empty($options['size'])){

					$width = $height = $options['size'] ;

 				}

				

 				if(isset($options['width']) and !empty($options['width'])){

					$width = $options['width'] ;

 				}

				

				if(isset($options['height']) and !empty($options['height'])){

					$height = $options['height'] ;

 				}

				

				if(isset($options['target_name']) and !empty($options['target_name'])){

					$target_name = $options['target_name'] ;

 				}

				

			

			if(isset($options['relative_path']) and $options['relative_path']==false){

				$destination_path = $options['destination'];

			}else{

				$destination_path = $options['source_directory'].'/'.$options['destination'] ;

			}

			

			$this->_crop(array(

					'_w'=>$options['_w'],

					'_h'=>$options['_h'],

					'_x'=>$options['_x'],

					'_y'=>$options['_y'],

					'quality'=>$options['quality'],

					'source_path'=>$options['source_directory']."/".$options['name'],

					'destination_path'=>$destination_path,

					'target_name'=>$target_name,

					'width'=>$width,

					'height'=>$height

				)

			);

			

 		}

		

 		return (object)array('success'=>true , 'error'=>false  ,'message'=>"Image(s) Croped Successfully !");



	}

	

	

	

	/* Perform Crop Operation */

	private function _crop($options){

	 		

  		

   		$image_source_path = $options['source_path'];

		$image_destination_path = $options['destination_path']."/".$options['target_name'] ;

		

		$dst_r = ImageCreateTrueColor(  $options['width'], $options['height'] );

		

		list($imagewidth, $imageheight, $imageType) = getimagesize($image_source_path);

		

		$imageType = image_type_to_mime_type($imageType);

		

		switch($imageType) {

			case "image/gif":$source = imagecreatefromgif($image_source_path);break;

			case "image/pjpeg":

			case "image/jpeg":

			case "image/jpg":$source = imagecreatefromjpeg($image_source_path);break;

			case "image/png":

			case "image/x-png":$source = imagecreatefrompng($image_source_path); break;

		}

		

		

		imagecopyresampled($dst_r,$source,0,0,$options['_x'],$options['_y'],$options['width'],$options['height'],$options['_w'],$options['_h']);



		switch($imageType) {

			case "image/gif":imagegif($dst_r,$image_destination_path);break;

			case "image/pjpeg":

			case "image/jpeg":

			case "image/jpg":imagejpeg($dst_r, $image_destination_path , $options['quality']);break;

			case "image/png":

			case "image/x-png": imagepng($dst_r, $image_destination_path); break;

		}

			

		

		return true ;

		

		

	}

	

	

	

	public function universal_image_show(){

		

		

		

	}



	/* Generate a thumbnail image of large image or can crop in desired size */
	public function universal_image_resize($src, $dst, $width, $height, $crop=0){
		
		if(!file_exists($src)){
			return false;
		}

		if(!list($w, $h) = getimagesize($src)) return "Unsupported picture type!";
		$type = strtolower(substr(strrchr($src,"."),1));
		/*$info = getimagesize($src);
		if($info[2] == IMAGETYPE_JPEG){
			$type = 'jpg';
		} else if($info[2] == IMAGETYPE_PNG){
			$type = 'png';
		} else if($info[2] == IMAGETYPE_GIF){
			$type = 'gif';
		} else if($info[2] == IMAGETYPE_BMP){
			$type = 'gif';
		} */
		/*echo $src.'<br>';
		echo $type;exit;*/

		if($type == 'jpeg') $type = 'jpg';
		switch($type){
			case 'bmp': $img = imagecreatefromwbmp($src); break;
			case 'gif': $img = imagecreatefromgif($src); break;
			case 'jpg': $img = imagecreatefromjpeg($src); break;
			case 'png': $img = imagecreatefrompng($src); break;
			default : return "Unsupported picture type!";
		}

		// resize
		if($crop){
			if($w < $width or $h < $height){ 
				if($src!=$dst and !copy($src, $dst)){
					return "There is a problem to save image, please contact to administrator.";
				}
				// return true;
				return "Picture is too small!";
			}
			$old_h = $h;
			$ratio = max($width/$w, $height/$h);
			$h = $height / $ratio;
			$x = ($w - $width / $ratio) / 2;
			$y = ($old_h - $h) / 2;
			$w = $width / $ratio;
		} else{
			
			if($w < $width and $h < $height){ 
				if($src!=$dst and !copy($src, $dst)){
					return "There is a problem to save image, please contact to administrator.";
				}
				// return true;
				return "Picture is too small!";
			}
			$ratio = min($width/$w, $height/$h);
			$width = $w * $ratio;
			$height = $h * $ratio;
			$x = 0;
			$y = 0;
		}

		$new = imagecreatetruecolor($width, $height);

		// preserve transparency
		if($type == "gif" or $type == "png"){
			imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
			imagealphablending($new, false);
			imagesavealpha($new, true);
		}

		imagecopyresampled($new, $img, 0, 0, $x, $y, $width, $height, $w, $h);

		switch($type){
			case 'bmp': imagewbmp($new, $dst, 100); break;
			case 'gif': imagegif($new, $dst, 100); break;
			case 'jpg': imagejpeg($new, $dst, 100); break;
			case 'png': imagepng($new, $dst); break;
		}

		return true;
	}

}

?>