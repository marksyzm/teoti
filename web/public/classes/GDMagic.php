<?php
class GDMagic {

	var $handle;
	var $height;
	var $width;

		function __construct(&$handle){
			$this->handle 	= $handle;
			$this->height 	= imagesy($handle);
			$this->width	= imagesx($handle);
		}
		

		private function color($x, $y, $color){

			$rgb = imagecolorat($this->handle, $x, $y);
		
			Switch($color){
				
				case 0:	return ($rgb >> 16) & 0xFF;
					break;

				case 1: return ($rgb >>  8) & 0xFF;
					break;

				case 2: return  $rgb & 0xFF;
					break;

			}

		}		


		function gradient($start, $end, $dir='horizontal'){
		
			$opt = (($dir=='vertical')?$this->height:$this->width);
		

				$r = $start[0];
				$g = $start[1];
				$b = $start[2];		

		
			for($i=0;$i<$opt;$i++){
			
				$x1  = (($dir=='vertical')?0:$i);
				$y1  = (($dir=='vertical')?$i:0);
				$x2  = (($dir=='vertical')?$this->width:$i);
				$y2  = (($dir=='vertical')?$i:$this->height);	

			
				$color = imagecolorallocate($this->handle, abs($r), abs($g), abs($b));
				imageline($this->handle, $x1, $y1, $x2, $y2, $color);
			
					$r -= ($start[0]-$end[0])/$opt;
					$g -= ($start[1]-$end[1])/$opt;
					$b -= ($start[2]-$end[2])/$opt;
			}

			return true;
		}
		



		function replacecolor($search, $replace, $precision=30, $alpha=0){
			
			for($i=0; $i<$this->width; $i++){
				for($j=0; $j<$this->height; $j++){
					
					$r	= $this->color($i, $j, 0);
					$g	= $this->color($i, $j, 1);
					$b	= $this->color($i, $j, 2);
					
					if(	abs($search[0]-$r)<$precision &&
						abs($search[1]-$g)<$precision &&
						abs($search[2]-$b)<$precision)
					{$r=$replace[0]; $g=$replace[1]; $b=$replace[2];}
					
					$color = imagecolorallocatealpha($this->handle, $r, $g, $b, $alpha);
					
					imagesetpixel($this->handle, $i, $j, $color);	
			
				}
			}	
		
			return true;
		}



		
		function greyscale(){
			
				for($i=0; $i<$this->width; $i++){
					for($j=0; $j<$this->height; $j++){

						$r	= $this->color($i, $j, 0);
						$g	= $this->color($i, $j, 1);
						$b	= $this->color($i, $j, 2);
	
							$gray = ($r+$g+$b)/3;
						
							$color = imagecolorallocate($this->handle, $gray, $gray, $gray);

							imagesetpixel($this->handle, $i, $j, $color);

					}
				}

		}


		function negative(){
		
				for($i=0; $i<$this->width; $i++){
					for($j=0; $j<$this->height; $j++){

						$r	= $this->color($i, $j, 0);
						$g	= $this->color($i, $j, 1);
						$b	= $this->color($i, $j, 2);


							$color = imagecolorallocate($this->handle, 255-$r, 255-$g, 255-$b);
					
							imagesetpixel($this->handle, $i, $j, $color);
					}
				}

		}



		function blur(){
			$matrix = array(array(1,1,1),
					array(1,1,1),
					array(1,1,1));
			
			$this->convolution_matrix($matrix);
		}

		function edge(){
			$matrix = array(array(0,1,0),
					array(1,-4,1),
					array(0,1,0));

			$this->convolution_matrix($matrix);
		}		

		
		function emboss(){
			$matrix = array(array(-2,-1,0),
					array(-1,1,1),
					array(0,1,2));

			$this->convolution_matrix($matrix);
		}	

		//Beta...
		function convolution_matrix($matrix){
			
			$tmp = imagecreatetruecolor($this->width, $this->height);

			for($x=0; $x<$this->width; $x++){
      				for($y=0; $y<$this->height; $y++){
            
            				$r[] =  $this->color($x-1,$y-1,0)*$matrix[0][0]+
            					$this->color($x,$y-1,0)*$matrix[1][0]+
               					$this->color($x+1,$y-1,0)*$matrix[2][0]+
               					$this->color($x-1,$y,0)*$matrix[0][1]+
              					$this->color($x,$y,0)*$matrix[1][1]+
               					$this->color($x+1,$y,0)*$matrix[2][1]+
               					$this->color($x-1,$y+1,0)*$matrix[0][2]+
               					$this->color($x,$y+1,0)*$matrix[1][2]+
               					$this->color($x+1,$y+1,0)*$matrix[2][2];

            				$g[] =  $this->color($x-1,$y-1,1)*$matrix[0][0]+
              					$this->color($x,$y-1,1)*$matrix[1][0]+
               					$this->color($x+1,$y-1,1)*$matrix[2][0]+
               					$this->color($x-1,$y,1)*$matrix[0][1]+
              					$this->color($x,$y,1)*$matrix[1][1]+
              	 				$this->color($x+1,$y,1)*$matrix[2][1]+
               					$this->color($x-1,$y+1,1)*$matrix[0][2]+
               					$this->color($x,$y+1,1)*$matrix[1][2]+
               					$this->color($x+1,$y+1,1)*$matrix[2][2];

            				$b[] =  $this->color($x-1,$y-1,2)*$matrix[0][0]+
              	 				$this->color($x,$y-1,2)*$matrix[1][0]+
               					$this->color($x+1,$y-1,2)*$matrix[2][0]+
              					$this->color($x-1,$y,2)*$matrix[0][1]+
               					$this->color($x,$y,2)*$matrix[1][1]+
               					$this->color($x+1,$y,2)*$matrix[2][1]+
              					$this->color($x-1,$y+1,2)*$matrix[0][2]+
               					$this->color($x,$y+1,2)*$matrix[1][2]+
               					$this->color($x+1,$y+1,2)*$matrix[2][2];

			

      				}
   			}

   			$maxr = max($r);
   			$maxg = max($g);
   			$maxb = max($b);

   					for($i=0,$x=0; $x<$this->width; $x++){
      						for($y=0; $y<$this->height; $i++,$y++){
         
         						$rt = 255*$r[$i]/$maxr;         
         						$gt = 255*$g[$i]/$maxg;
         						$bt = 255*$b[$i]/$maxb;

        						$rc = $rt>0?$rt:0;
         						$gc = $gt>0?$gt:0;
        		 				$bc = $bt>0?$bt:0;
   	
         						$c = imagecolorallocate($tmp, $rc, $gc, $bc);
         						imagesetpixel($tmp, $x, $y, $c);

      						}
   					}
			
					
					for($x=0; $x<$this->width; $x++){
						for($y=0; $y<$this->height; $y++){
							
							$rgb 	= imagecolorat($tmp, $x, $y);
							$r  	= ($rgb >> 16) & 0xFF;
							$g	= ($rgb >>  8) & 0xFF;
							$b 	=  $rgb & 0xFF;						

							$c = imagecolorallocate($this->handle, $r, $g, $b);
							imagesetpixel($this->handle, $x, $y, $c);	
					
						}
					}
		}

		

}

?>
