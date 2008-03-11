<?
class RosPdfContext extends GraphicContext
{
	var $pdf;
    var $width;
    var $height;
    
    //	defaults to portrait
	function RosPdfContext(&$pdf, $bounds = array(0, 0, 612, 792))
	{
		$this->width = $bounds[2];
		$this->height = $bounds[3];
		
		$bounds = array(0, 0, $this->width, $this->height);
		
		if(!$pdf)
		{
			$this->pdf = new GraphicRosPdfEngine($bounds);
		}
		else
		{
			$this->pdf = &$pdf;
		}
		
		$this->pdf->selectFont(pehppy_dir . '/pdf/fonts/Helvetica.afm', 'utf-8');
		
		//	call up the constructor chain - this needs to come last
		$this->GraphicContext($this->width, $this->height);
		//$this->setCurFont();
	}
		
	function fixy($y)
	{
		return $this->height - $y;
	}
	
	function getPageWidth()
	{
		return $this->width;
	}
	
	function getPageHeight()
	{
		return $this->height;
	}
	
	function addColor($name, $r, $g, $b)
	{
		$this->colors[$name] = array($r, $g, $b);
	}
	
	
	function setCurLineColor($colorName)
	{
		parent::setCurLineColor($colorName);
		$color = $this->_getCurLineColor();
		$this->pdf->setStrokeColor($color[0] / 255, $color[1] / 255, $color[2] / 255);
	}
	
	function setCurFillColor($colorName)
	{
		parent::setCurFillColor($colorName);
		$color = $this->_getCurFillColor();
		$this->pdf->setColor($color[0] / 255, $color[1] / 255, $color[2] / 255);
	}
	
	function setCurTextColor($colorName)
	{
		parent::setCurTextColor($colorName);
		$color = $this->_getCurTextColor();
		//$this->pdf->setColor($color[0] / 255, $color[1] / 255, $color[2] / 255);
//		$this->pdf->setColor(0.5, 0.5, 0.5);
	}
	
	function breakPage()
	{
		$this->pdf->newPage();
	}
	
	function getStringWidth($string)
	{
		return $this->pdf->getTextWidth($this->getTextSize(), htmlspecialchars($string));
	}
	
	function addText($x, $y, $txt, $params = array())
	{
		$y = $this->fixy($y);
		$this->pdf->addText($x, $y, $this->getTextSize(), htmlspecialchars($txt));
	}
	
	function addLine($x1, $y1, $x2, $y2)
	{
		$y1 = $this->fixy($y1);
		$y2 = $this->fixy($y2);
		$this->pdf->line($x1, $y1, $x2, $y2);
	}
	
	function addHorizLine($left, $right, $top, $lineWidth = 0.57)
	{
		$top = $this->fixy($top);
		
		$halfLineWidth = $lineWidth / 2;
		
		$x1 = $left;
		$x2 = $right;
		$y1 = $y2 = $top - $halfLineWidth;
		$this->setLineWidth($lineWidth);
		$this->pdf->line($x1, $y1, $x2, $y2);
	}
	
	function addVertLine($top, $bottom, $left, $lineWidth = 0.57)
	{
		$top = $this->fixy($top);
		$bottom = $this->fixy($bottom);
		
		$halfLineWidth = $lineWidth / 2;
		
		$x1 = $x2 = $left - $halfLineWidth;
		$y1 = $top;
		$y2 = $bottom;
		$this->setLineWidth($lineWidth);
		$this->pdf->line($x1, $y1, $x2, $y2);
	}
	
	function addRect($x, $y, $w, $h, $style = 'D')
	{
		$y = $this->fixy($y);
		$h = $h * -1;
		
		if($style == 'D')
			$this->pdf->rectangle($x, $y, $w, $h);
		else
		{
			$this->pdf->filledRectangle($x, $y, $w, $h);
		}
	}
	
	//	right now this is limited to jpegs
	function addImage($file, $x, $y, $w=0, $h=0)
	{
		//$type = exif_imagetype($file);
		if(true || $type == IMAGETYPE_JPEG)
		{
			if($h)
				$height = $h;
			else
			{
				$fileInfo = getimagesize($file);
				$fileWidth = $fileInfo[0];
				$fileHeight = $fileInfo[1];					
				
				if($w)
				{
					$heightToWidth = $fileHeight / $fileWidth;
					$width = $w;
					$height = $heightToWidth * $w;
				}
				else
				{
					$width = $fileWidth;
					$height = $fileHeight;
				}
			}
		}
		else
		{
			trigger_error('image type not handled');
		}
		
		$y = $this->fixy($y) - $height;
		$this->pdf->addJpegFromFile($file, $x, $y, $w, $h);
	}
	
	function addPolygon($points, $style = 'D')
	{
		$this->pdf->polygon($points);
	}
	
	function addCircle($x, $y, $r, $style='D')
	{
		$this->pdf->ellipse($x, $y, $r);
	}
	
	function addEllipse($x, $y, $rx, $ry, $style='D')
	{
		$this->pdf->Ellipse($x, $y, $rx, $ry);
	}
	
	//	This uses the tweaked logic of imagearc and imagefilledarc below for evaluating angles
	//		not logic that would actually draw the angles passed in.  This may need to be tweaked
	//		for compatibility with other context modules.
	
	function addArc($x, $y, $w, $h, $startAngle, $endAngle, $style='D')
	{
		trigger_error("not yet implemented");
		//$this->fpdf->Arc($x, $y, $w / 2, $h / 2, $startAngle, $endAngle, $style);
	}
	
	//	This uses the tweaked logic of imagearc and imagefilledarc for evaluating angles
	//		not logic that would actually draw the angles passed in.  This may need to be tweaked
	//		for compatibility with other context modules.

	function addCylinderSlice($cx, $cy, $w, $h, $sTheta, $eTheta, $depth, $style='D')
	{
		trigger_error("not yet implemented");
		//$this->fpdf->CylinderSlice($cx, $cy, $w / 2, $h / 2, $sTheta, $eTheta, $depth, $style);
	}
	
	function setTextFont($newFontStyle)
	{
		$font = $newFontStyle->getFont();
		$font = $font == 'Arial' ? 'Helvetica' : $font;
		
		$this->setTextSize($newFontStyle->getTextSize());
		
		$fontFile = $this->calculateFontFile($font, $newFontStyle->getBold(), $newFontStyle->getItalics());
		$this->pdf->selectFont($fontFile);
	}
	
	/*
	function setTextSize($size)
	{
		
		trigger_error("not yet implemented");
		
		//parent::setTextSize($size);
		//$this->fpdf->SetFontSize($sizes);
	}
	*/
	
	function setTextStyle($style)
	{
		trigger_error("not yet implemented");
		
		//parent::setTextStyle($style);
		//$this->setCurFont();
	}
	
	function setTextFontName($fontName)
	{
		trigger_error("not yet implemented");
		
		//parent::setTextFontName($fontname);
		//$this->setCurFont();
	}
	
	function setCurFont()
	{
		trigger_error('not yet implemented');
		//$this->fpdf->SetFont($this->getTextFontName(), $this->getTextStyle(), $this->getTextSize());
	}
	
	function calculateFontFile($textFont, $bold, $italics)
	{
		$baseDir = pehppy_dir . '/pdf/fonts/';
		if((!isset($bold) && !isset($italics)) || (!$bold && !$italics))
		{
			return $baseDir . $textFont . ".afm";
		}
		else if(isset($bold) && $bold)
		{
			if(isset($italics) && $italics)
			{
				if(file_exists(dirname(__file__) . "/fonts/" . $textFont . "-BoldOblique.afm"))
					return $baseDir . $textFont . "-BoldOblique.afm";
				else
					return $baseDir . $textFont . "-BoldItalic.afm";
			}
			else
			{
				return $baseDir . $textFont . "-Bold.afm";
			}
		}
		else
		{
			if(file_exists(dirname(__file__) . "/fonts/" . $textFont . "-Oblique.afm"))
				return $baseDir . $textFont . "-Oblique.afm";
			else
				return $baseDir . $textFont . "-Italic.afm";
		}
	}		 
	
	function setTextColor($r, $g, $b)
	{
		$this->pdf->setColor($r, $g, $b);
	}
	
	function setLineWidth($lineWidth)
	{
		$this->pdf->setLineStyle($lineWidth);
	}
	
	function addRaw($rawData)
	{
		$this->pdf->raw($rawData);
	}
	
	function display()
	{
		//	we should all them to set the name here
		$this->pdf->stream();
	}
}
?>