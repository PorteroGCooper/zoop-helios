<?
class ImageMapContext extends GraphicContext
{
	var $image;
	var $fontFile;
	var $mapItems;
    
	function ImageMapContext($width, $height)
	{
		//	call up the constructor chain
		$this->GraphicContext($width, $height);
		
		//	this should be in a config file
		if(app_status == 'dev')
			$this->fontFile = "/home/rick/data/fonts/verdana.ttf";
		else
			$this->fontFile = "/home/apps/data/fonts/verdana.ttf";
		
		$this->mapItems = array();
		
		$this->asdf = 5;
	}
	
	function getPageHeight()
	{
		return 10000000;
	}
	
	function addColor($name, $r, $g, $b)
	{
	}
	
	function breakPage()
	{
	}
	
	function getStringWidth($string)
	{
		$box = imagettfbbox($this->textSize, 0, $this->fontFile, $string);
		return $box[2] - $box[0];
	}
	
	function addText($x, $y, $text)
	{
	}
	
	function addLine($x1, $y1, $x2, $y2)
	{
	}
	
	function addHorizLine($left, $right, $top, $lineWidth = 0.57)
	{
	}
	
	function addVertLine($top, $bottom, $left, $lineWidth = 0.57)
	{
	}
	
	function addRect($x, $y, $w, $h, $style = 'D', $url = NULL)
	{
		if($url)
		{
			$right = $x + $w;
			$bottom = $y + $h;
			//echo "$x, $y, $w, $h, $right, $bottom<br>";
			$this->mapItems[] = "<area shape=\"rect\" coords=\"$x, $y, $right, $bottom\" href=\"$url\">";
		}
	}
	
	function addPolygon($points, $style = 'D', $url = NULL)
	{
		if($url)
		{
			$coordList = implode(', ', $points);
			$this->mapItems[] = "<area shape=\"poly\" coords=\"$coordList\" href=\"$url\">";
		}
	}
	
	function addCircle($x, $y, $r, $style='D')
	{
		imageellipse($this->image, $x, $y, $r * 2, $r * 2, $this->_getCurLineColor());
	}
	
	function addEllipse($x, $y, $rx, $ry, $style='D', $url = NULL)
	{
		$w = $rx * 2;
		$h = $ry * 2;
		$this->addArc($x, $y, $w, $h, 0, 360, $style, $url);
	}
	
	function addArc($x, $y, $w, $h, $startAngle, $endAngle, $style='D', $url = NULL)
	{
		if($url)
		{
			$angleList = array($x, $y);
			for($t = $startAngle; $t < $endAngle; $t += 10)
			{
				EllipseCirclePos($x, $y, $w, $h, $t, $xCoord, $yCoord);
				$angleList[] = $xCoord;
				$angleList[] = $yCoord;
			}
			EllipseCirclePos($x, $y, $w, $h, $endAngle, $xCoord, $yCoord);
			$angleList[] = $xCoord;
			$angleList[] = $yCoord;
			$coordList = implode(', ', $angleList);
			$this->mapItems[] = "<area shape=\"poly\" coords=\"$coordList\" href=\"$url\">";
		}
		//echo_r($this);
		$this->asdf = 2;
	}
	
	function addCylinderSlice($cx, $cy, $w, $h, $sTheta, $eTheta, $depth, $style='D')
	{
	}
	
	function setTextFont($newFontStyle)
	{
	}
	
	function getFontHeight()
	{
		return $this->fontHeight;
	}
	
	function setTextColor($r, $g, $b)
	{
	}
	
	function display()
	{
	}
	
	function getMap()
	{
//		echo_r($this->mapItems);
		krsort($this->mapItems);
		return implode("\n", $this->mapItems);
	}
	
	function useKerningHack()
	{
		return 1;
	}
	
	function getLineHeightMultiplier()
	{
		return 1.2;
	}

/*
	var $width;
	var $height;
	var $textHeight;
	var $mapItems;
    
	function ImageMapContext($width, $height)
	{
		$this->width = $width;
		$this->height = $height;
		
		//	this should be in a config file
		if(app_status == 'dev')
			$this->fontFile = "/usr/share/fonts/bitstream-vera/Vera.ttf";
		else
			$this->fontFile = "/home/apps/data/fonts/verdana.ttf";
				
		$this->textHeight = 10;
		
		$this->mapItems = array();
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function getHeight()
	{
		return $this->height;
	}
	
	function addText($x, $y, $text)
	{
	}
	
	function addLine($x1, $y1, $x2, $y2)
	{
	}
	
	function addRect($x, $y, $w, $h, $style = 'D', $url = NULL)
	{
		if($url)
		{
			$right = $x + $w;
			$bottom = $y + $h;
			//echo "$x, $y, $w, $h, $right, $bottom<br>";
			$this->mapItems[] = "<area shape=\"rect\" coords=\"$x, $y, $right, $bottom\" href=\"$url\">";
		}
	}
	
	function addPolygon($points, $style = 'D', $url = NULL)
	{
		if($url)
		{
			$coordList = implode(', ', $points);
			$this->mapItems[] = "<area shape=\"poly\" coords=\"$coordList\" href=\"$url\">";
		}
	}
	
	function addCircle($x, $y, $r, $style='D')
	{
	}
	
	function addEllipse($x, $y, $rx, $ry, $style='D', $url = NULL)
	{
		$w = $rx * 2;
		$h = $ry * 2;
		$this->addArc($x, $y, $w, $h, 0, 360, $style, $url);
	}
	
	function addArc($x, $y, $w, $h, $startAngle, $endAngle, $style='D', $url = NULL)
	{
		if($url)
		{
			$angleList = array($x, $y);
			for($t = $startAngle; $t < $endAngle; $t += 10)
			{
				EllipseCirclePos($x, $y, $w, $h, $t, $xCoord, $yCoord);
				$angleList[] = $xCoord;
				$angleList[] = $yCoord;
			}
			EllipseCirclePos($x, $y, $w, $h, $endAngle, $xCoord, $yCoord);
			$angleList[] = $xCoord;
			$angleList[] = $yCoord;
			$coordList = implode(', ', $angleList);
			$this->mapItems[] = "<area shape=\"poly\" coords=\"$coordList\" href=\"$url\">";
		}
	}
	
	function addCylinderSlice($cx, $cy, $w, $h, $sTheta, $eTheta, $depth, $style='D')
	{
	}
	
	function setTextFont($newFontStyle)
	{
	}
	
	function getFontHeight()
	{
		return $this->fontHeight;
	}
	
	function setTextColor($r, $g, $b)
	{
	}
	
	function addColor($name, $r, $g, $b)
	{
	}
	
	function _getCurTextColor()
	{
	}
	
	function _getCurLineColor()
	{
	}
	
	function _getCurFillColor()
	{
	}
	
	function setCurLineColor($color)
	{
	}
	
	function pushLineColor($color)
	{
	}
	
	function popLineColor()
	{
	}
	
	function setCurFillColor($color)
	{
	}
	
	function setCurTextColor($color)
	{
	}
	
	function getPageWidth()
	{
	}
	
	function getPageHeight()
	{
	}
	
	function breakPage()
	{
	}
	
	function getStringWidth($string)
	{
		$box = imagettfbbox($this->textHeight, 0, $this->fontFile, $string);
		return $box[2] - $box[0];
	}
	
	function getMap()
	{
		krsort($this->mapItems);
		return implode("\n", $this->mapItems);
	}
	
	function display()
	{
	}
*/
}
?>
