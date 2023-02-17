<?php
#function_resize.php

function resize($id, $img, $idGroup)
{

class SimpleImage {

   var $image;
   var $image_type;

   function load($filename) {
      $image_info = getimagesize($filename);
      $this->image_type = $image_info[2];
      if( $this->image_type == IMAGETYPE_JPEG ) {
         $this->image = imagecreatefromjpeg($filename);
      } elseif( $this->image_type == IMAGETYPE_GIF ) {
         $this->image = imagecreatefromgif($filename);
      } elseif( $this->image_type == IMAGETYPE_PNG ) {
         $this->image = imagecreatefrompng($filename);
      }
   }
   function save($filename, $image_type=IMAGETYPE_JPEG, $compression=75, $permissions=null) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image,$filename,$compression);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image,$filename);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image,$filename);
      }
      if( $permissions != null) {
         chmod($filename,$permissions);
      }
   }
   function output($image_type=IMAGETYPE_JPEG) {
      if( $image_type == IMAGETYPE_JPEG ) {
         imagejpeg($this->image);
      } elseif( $image_type == IMAGETYPE_GIF ) {
         imagegif($this->image);
      } elseif( $image_type == IMAGETYPE_PNG ) {
         imagepng($this->image);
      } 
   }
   function getWidth() {
      return imagesx($this->image);
   }
   function getHeight() {
      return imagesy($this->image);
   }
   function resizeToHeight($height) {
      $ratio = $height / $this->getHeight();
      $width = $this->getWidth() * $ratio;
      $this->resize($width,$height);
   }
   function resizeToWidth($width) {
      $ratio = $width / $this->getWidth();
      $height = $this->getheight() * $ratio;
      $this->resize($width,$height);
   }
   function scale($scale) {
      $width = $this->getWidth() * $scale/100;
      $height = $this->getheight() * $scale/100;
      $this->resize($width,$height);
   }
   function resize($width,$height) {
      $new_image = imagecreatetruecolor($width, $height);
      imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
      $this->image = $new_image;
   }
}



    function ExifRotate ($file_path) {
        $image = imagecreatefromjpeg($file_path);
        // Прочитать данные EXIF
        $exif = exif_read_data($file_path);
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                // Поворот на 180 градусов
                case 3: {
                    $image = imagerotate($image,180,0);
                    break;
                }
                // Поворот вправо на 90 градусов
                case 6: {
                    $image = imagerotate($image,-90,0);
                    break;
                }
                // Поворот влево на 90 градусов
                case 8: {
                    $image = imagerotate($image,90,0);
                    break;
                }
            }
        }
        
        imagejpeg($image, $file_path, 100);
        
    }


    $ext = pathinfo($img,PATHINFO_EXTENSION); # узнаем формат файла
    if($ext) # если формат определился
    {
        
        
    if ($ext == "jpeg" || $ext == "jpg") {
        # jpeg файлы имеют EXIF (данные о повороте)
        ExifRotate($img);
    }
        

    $link_128 = "files/loaded/size_128_$id.$ext";
    $link_650 = "files/loaded/size_650_$id.$ext";
    // $link_1024 = "files/size_1024/size_1024_".$id.".".$ext;
    
    global $dir;

       $image = new SimpleImage();
       $image->load($img);
       $image->resizeToWidth(128);
       $image->save($_SERVER['DOCUMENT_ROOT']."/".$link_128); 
       

    
    
       $image = new SimpleImage();
       $image->load($img);
       $image->resizeToWidth(650); 
       $image->save($_SERVER['DOCUMENT_ROOT']."/".$link_650); 


    	
    	$GLOBALS['mysqli'] -> query("INSERT INTO files (id_group, small_size, medium_size, large_size, type_file) VALUES ($idGroup,'$link_128','$link_650','files/loaded/$id.$ext','img');");
    	$insert_id = $GLOBALS['mysqli']->insert_id;
        return array('small_size'=>$link_128, 'medium_size'=>$link_650, 'original_link' => "files/loaded/$id.$ext", 'fileid'=>$insert_id);
    }
    
    
}
?>
































