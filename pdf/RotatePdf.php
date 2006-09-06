<?php
/**
* @package pdf
*/
class RotatePdf extends Cpdf 
{
	
	function RotatePdf ($pageSize=array(0,0,612,792))
	{
		$this->pagesize = $pageSize;
		$this->setRotation(0);
		parent::Cpdf($pageSize);
	}
	function setRotation($rotation)
	{
		$this->rotation = deg2rad($rotation);
		$this->sint = sin($this->rotation);
		$this->cost = cos($this->rotation);
		$this->xcenter2 = (float)($this->pagesize[2] - $this->pagesize[0]) / 2;//destination center
		$this->ycenter2 = (float)($this->pagesize[3] - $this->pagesize[1]) / 2;//destination center
		$this->xcenter = (float)($this->pagesize[3] - $this->pagesize[1]) / 2;//original center
		$this->ycenter = (float)($this->pagesize[2] - $this->pagesize[0]) / 2;//original center(assuming the bounds rotate 90 degrees from original
	}
	
	function rotateX($x, $y)
	{
		if($this->rotation != 0)
		{
			$x = (float) $x - $this->xcenter;
			$y = (float) $y - $this->ycenter;
			//echo_r("rotating $x, $y");
			$answer = ($x * $this->cost)
					- ($y * $this->sint);
			//echo_r("x = $answer");
			return (float)$answer + (float)$this->xcenter2;
		}
		else
			return $x;
	}
	
	function rotateY($x, $y)
	{
		if($this->rotation != 0)
		{
			$x = (float) $x - $this->xcenter;
			$y = (float) $y - $this->ycenter;
			//echo_r("rotating $x, $y");
			$answer = $x * $this->sint
					+ $y * $this->cost;
			//echo_r("y = $answer");
			return (float)$answer + (float) $this->ycenter2;
		}
		else
			return $y;
	}
	
	function rotateXY($x, $y)
	{
		//$point = $this->rotateRadial($x, $y);		
		$point['x'] = $this->rotateX($x, $y);
		$point['y'] = $this->rotateY($x, $y);
		return $point;
	}
	
	function getR($x, $y)
	{
		return sqrt($x*$x + $y*$y);
	}
	
	function getAngle($x, $y)
	{
		if($x == 0)
		{
			$x = 0.00001;
		}
		return atan($y/$x);
	}
	
	function getX($r, $angle)
	{
		return $r * cos($angle);
	}
	
	function getY($r, $angle)
	{
		return $r * sin($angle);
	}
	
	function rotateRadial($x, $y)
	{
		if($this->rotation != 0)
		{
			$x = (float)$x - $this->xcenter;
			$y = (float)$y - $this->ycenter;
			$r = $this->getR($x , $y);
			$angle = $this->getAngle($x, $y);
			$deg = rad2deg($angle);
			//*
			if(
				($deg >= -180 && $deg < -90 && $x >= 0 && $y >= 0)
				|| ($deg >= -90 && $deg < 0 && $x < 0 && $y >= 0)
				|| ($deg >= 0 && $deg < 90 && $x < 0 && $y < 0)
				|| ($deg >= 90 && $deg < 180 && $x >= 0 && $y < 0))
			{
				$r = -1.0 * $r;
			}
			//*/
			$angle = $angle + $this->rotation;
			$point['x'] = $this->getX($r, $angle) + $this->xcenter2;
			$point['y'] = $this->getY($r, $angle) + $this->ycenter2;
			return $point;
		}
		else
		{
			$point['x'] = $x;
			$point['y'] = $y;
			return $point;
		}
	}
	
	function line($x1,$y1,$x2,$y2)
	{
		$point = $this->rotateXY($x1, $y1);
		//$point = $this->rotateRadial((float)$x1orig, (float)$y1orig);
		$x1 = $point['x'];
		$y1 = $point['y'];
		
		$point = $this->rotateXY($x2, $y2);
		//$point = $this->rotateRadial((float) $x2orig, (float)$y2orig);
		$x2 = $point['x'];
		$y2 = $point['y'];
		
		return parent::line($x1,$y1,$x2,$y2);
	}
	
	function addText($x,$y,$size,$text,$angle=0,$wordSpaceAdjust=0)
	{
		$angle = $angle - rad2deg($this->rotation);
		//*
		$point = $this->rotateXY($x, $y);		
		
		
		//*/
		/*
		$point = $this->rotateRadial($x, $y);
		*/
		$x = $point['x'];
		$y = $point['y'];
		return parent::addText($x, $y, $size, $text, $angle, $wordSpaceAdjust);
	}
	
	function rectangle($x1,$y1,$width,$height)
	{
		/*
		$x1 = $this->rotateX($x1, $y1);
		$y1 = $this->rotateY($x1, $y1);
		*/
		$point = $this->rotateXY($x1, $y1);
		//$point = $this->rotateRadial($x1, $y1);
		$x1 = $point['x'];
		$y1 = $point['y'];
		if(rad2deg($this->rotation) == 90 || rad2deg($this->rotation) == -90)
		{
			$temp = $width;
			$width = $height;
			$height = $temp;
			$y1 -= $height;
		}
		return parent::rectangle($x1,$y1,$width,$height);
	}
	
	function filledRectangle($x1,$y1,$width,$height)
	{
		/*
		$x1 = $this->rotateX($x1, $y1);
		$y1 = $this->rotateY($x1, $y1);
		*/
		$point = $this->rotateXY($x1, $y1);
		//$point = $this->rotateRadial($x1, $y1);
		$x1 = $point['x'];
		$y1 = $point['y'];
		if(rad2deg($this->rotation) == 90 || rad2deg($this->rotation) == -90)
		{
			$temp = $width;
			$width = $height;
			$height = $temp;
			//$x1 -= $width;
			$y1 -= $height;
		}
		return parent::filledRectangle($x1,$y1,$width,$height);
	}
	
	function filledEllipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360)
	{
		$angle = $angle + rad2deg($this->rotation);
		/*
		$x0 = $this->rotateX($x0, $y0);
		$y0 = $this->rotateX($x0, $y0);
		*/
		$point = $this->rotateXY($x0, $y0);
		//$point = $this->rotateRadial($x0, $y0);
		$x0 = $point['x'];
		$y0 = $point['y'];
		return parent::filledEllipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360);
	}
	
	function ellipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360,$close=1,$fill=0)
	{
		$angle = $angle + rad2deg($this->rotation);
		/*
		$x0 = $this->rotateX($x0, $y0);
		$y0 = $this->rotateX($x0, $y0);
		*/
		$point = $this->rotateXY($x0, $y0);
		//$point = $this->rotateRadial($x0, $y0);
		$x0 = $point['x'];
		$y0 = $point['y'];
		return parent::ellipse($x0,$y0,$r1,$r2=0,$angle=0,$nSeg=8,$astart=0,$afinish=360);
	}		
}
?>