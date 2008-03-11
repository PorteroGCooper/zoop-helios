<?
class ImageContext extends GraphicContext
{
	var $image;
	var $fontFile;
    
	function ImageContext($width, $height)
	{
		//	create the gd instance
		$this->image = imagecreatetruecolor($width, $height);
		
		//	call up the constructor chain
		$this->GraphicContext($width, $height);
		
		//	this should be in a config file
		if(app_status == 'dev')
			//$this->fontFile = "/home/rick/data/fonts/verdana.ttf";
			$this->fontFile = "C:\Windows\fonts\verdana.ttf";
		else
			$this->fontFile = "/home/apps/data/fonts/verdana.ttf";
		
		
		//	it defaults to being filled with black, fill it to white
		imagefilledrectangle($this->image, 0, 0, $this->width, $this->height, $this->colors['white']);
	}
	
	function getPageHeight()
	{
		return 10000000;
	}
	
	function addColor($name, $r, $g, $b)
	{
		$this->colors[$name] = imagecolorallocate($this->image, $r, $g, $b);
//		echo_r($this->colors);
//		echo_backtrace();
	}
		
	function breakPage()
	{
	}
	
	function getStringWidth($string)
	{
		/*
		$str = '20'; //'2005 Overall Competency Rating Distibutions';
		$sum = 0;
		$size = 100;
		for($i = 0; $i < strlen($str); $i++)
		{
			$box = imagettfbbox($size, 0, $this->fontFile, $str[$i] . ' ');
			//echo_r($str[$i] . ' ' . ($box[2] - $box[0]));
			$sum += $box[2] - $box[0];
		}
		echo_r($sum);
		
		$box = imagettfbbox($size, 0, $this->fontFile, $str);
		echo_r(($box[2] - $box[0]));
		
		echo_r(($box[2] - $box[0]) - $sum);
		*/
		
		$box = imagettfbbox($this->textSize, 0, $this->fontFile, $string);
		return $box[2] - $box[0];
	}
	
	function addText($x, $y, $text, $params = array())
	{
		if(0)
		{
			if(isset($params['angle']))
			{
				$angle = 360 - NormalizeAngle($params['angle']);
				imagettftext($this->image, $this->textSize, $angle, $x, $y, $this->_getCurTextColor(), $this->fontFile, $text);
			}
			else
			{
				$angle = 0;
				imagettftext($this->image, $this->textSize, $angle, $x, $y + 13, $this->_getCurTextColor(), $this->fontFile, $text);
				$curx = $x;
				for($i = 0; $i < strlen($text); $i++)
				{
					imagettftext($this->image, $this->textSize, $angle, $curx, $y, $this->_getCurTextColor(), $this->fontFile, $text[$i]);
					$curx += $this->getStringWidth($text[$i]);
				}
			}
		}
		else
		{
			if(isset($params['angle']))
				$angle = 360 - NormalizeAngle($params['angle']);
			else
				$angle = 0;
			imagettftext($this->image, $this->textSize, $angle, $x, $y, $this->_getCurTextColor(), $this->fontFile, $text);
		}
	}
	
	function addLine($x1, $y1, $x2, $y2, $lineWidth = 1)
	{
		imagesetthickness ($this->image, $lineWidth);
		imageline($this->image, $x1, $y1, $x2, $y2, $this->_getCurLineColor());
	}
	
	function addHorizLine($left, $right, $top, $lineWidth = 1)
	{
		imagesetthickness ($this->image, $lineWidth);
		$top += $lineWidth / 2;
		imageline($this->image, $left, $top, $right, $top, $this->_getCurLineColor());
	}
	
	function addVertLine($top, $bottom, $left, $lineWidth = 1)
	{
		imagesetthickness ($this->image, $lineWidth);
		$left += $lineWidth / 2;
		imageline($this->image, $left, $top, $left, $bottom, $this->_getCurLineColor());
	}
	
	function addRect($x, $y, $w, $h, $style = 'D')
	{
		//	if we need to fill
		if(strpos($style, 'F') !== false)
			imagefilledrectangle ($this->image, $x, $y, $x + $w, $y + $h, $this->_getCurFillColor());
		
		//	if we need to draw the outline
		if(strpos($style, 'D') !== false)
			imagerectangle($this->image, $x, $y, $x + $w, $y + $h, $this->_getCurLineColor());
	}
	
	function addPolygon($points, $style = 'D')
	{
		//	if we need to fill
		if(strpos($style, 'F') !== false)
			imagefilledpolygon ($this->image, $points, count($points) / 2, $this->_getCurFillColor());
		
		//	if we need to draw the outline
		if(strpos($style, 'D') !== false)
			imagepolygon ($this->image, $points, count($points) / 2, $this->_getCurLineColor());
	}
	
	function addCircle($x, $y, $r, $style='D')
	{
		imageellipse($this->image, $x, $y, $r * 2, $r * 2, $this->_getCurLineColor());
	}
	
	function addEllipse($x, $y, $rx, $ry, $style='D')
	{
		$w = $rx * 2;
		$h = $ry * 2;
		
		if(strpos($style, 'D') !== false )
			imagefilledellipse($this->image, $x, $y, $w, $h, $this->_getCurFillColor());
		
		if(strpos($style, 'F') !== false)
			imageellipse($this->image, $x, $y, $w, $h, $this->_getCurLineColor());
	}
	
	//	This uses the tweaked logic of imagearc and imagefilledarc below for evaluating angles
	//		not logic that would actually draw the angles passed in.  This may need to be tweaked
	//		for compatibility with other context modules.
	
	function addArc($x, $y, $w, $h, $startAngle, $endAngle, $style='D')
	{
		$same = $startAngle == $endAngle ? 1 : 0;
		
		$startAngle = NormalizeAngle($startAngle);
		$endAngle = NormalizeAngle($endAngle);
		
		if(!$same && ($startAngle == $endAngle))
			$endAngle++;
		
		//	draw just the arc
		if(strpos($style, 'A') !== false)
			imagearc($this->image, $x, $y, $w, $h, $startAngle, $endAngle, $this->_getCurLineColor());
		
		//	if we need to fill
		if(strpos($style, 'F') !== false)
			imagefilledarc($this->image, $x, $y, $w, $h, $startAngle, $endAngle, $this->_getCurFillColor(), IMG_ARC_PIE);
		
		//	if we need to draw the outline
		if(strpos($style, 'D') !== false)
			imagefilledarc($this->image, $x, $y, $w, $h, $startAngle, $endAngle, $this->_getCurLineColor(), IMG_ARC_NOFILL + IMG_ARC_EDGED);
	}
	
	//	This uses the tweaked logic of imagearc and imagefilledarc below for evaluating angles
	//		not logic that would actually draw the angles passed in.  This may need to be tweaked
	//		for compatibility with other context modules.

	function addCylinderSlice($cx, $cy, $w, $h, $sTheta, $eTheta, $depth, $style='D')
	{
		//	if we need to draw the outline
		if(strpos($style, 'D') !== false)
		{
			//	draw the bottom arc
			$this->addArc($cx, $cy + $depth, $w, $h, $sTheta, $eTheta, 'A');
		}
		
		//	if we need to fill
		if(strpos($style, 'F') !== false)
		{
			assert($depth >= 0);
			for($z = 1; $z < $depth; $z++)
			{
				$this->pushLineColor($this->curFillColor);
				$this->addArc($cx, $cy + $z, $w, $h, $sTheta, $eTheta, 'A');
				$this->popLineColor();
			}
		}
		
		//	if we need to draw the outline
		if(strpos($style, 'D') !== false)
		{
			//	draw the top arc
			$this->addArc($cx, $cy, $w, $h, $sTheta, $eTheta, 'A');
			
			//	draw the edge lines to connect the arcs
			$x = NULL;
			$y = NULL;
			EllipseCirclePos($cx, $cy, $w, $h, $sTheta, $x, $y);
			$this->addLine($x, $y, $x, $y + $depth);
			
			$x = NULL;
			$y = NULL;
			EllipseCirclePos($cx, $cy, $w, $h, $eTheta, $x, $y);
			$this->addLine($x, $y, $x, $y + $depth);
		}
	}
	
	function setTextFont($newFontStyle)
	{
		assert( is_a($newFontStyle, 'GraphicTextStyle') );
		
		/*
		$style = '';
		if($newFontStyle->getUnderline())
			$style .= 'U';
		if($newFontStyle->getBold())
			$style .= 'B';
		if($newFontStyle->getItalics())
			$style .= 'I';
		*/
		
		$this->textSize = $newFontStyle->getTextSize();
		//$this->fpdf->SetFont($newFontStyle->getFont(), $style, );
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
		header("Content-type: image/png");
		imagepng($this->image);
		imagedestroy($this->image);
	}
	
	function useKerningHack()
	{
		return 1;
	}
	
	function getLineHeightMultiplier()
	{
		return 1.2;
	}
}
?>