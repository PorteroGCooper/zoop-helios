<?
class FPdfContext extends GraphicContext
{
	var $fpdf;
    var $width;
    var $height;
    
	function FPdfContext($dimensions = 'tall')
	{
		switch(gettype($dimensions))
		{
			case 'string':
				switch($dimensions)
				{
					case 'tall':
						$this->height = 792;
						$this->width = 612;
						$orientation = 'P';
						break;
					case 'wide':
						$this->height = 612;
						$this->width = 792;
						$orientation = 'L';
						break;
				}
				break;
		}
		
		//	BMFPdf should really just be renamed into GraphicFPdf
		$this->fpdf = new BMFPdf($orientation, 'pt', 'letter');
		$this->fpdf->AddPage();
		
		$this->fpdf->AddFont('Verdana','','verdana.php');
		$this->fpdf->AddFont('Verdana','B','verdanab.php');
		$this->fpdf->AddFont('Verdana','I','verdanai.php');
		$this->fpdf->AddFont('Verdana','BI','verdanaz.php');
		
		$this->fpdf->SetMargins(0, 0);
		
		//	call up the constructor chain - this needs to come last
		$this->GraphicContext($this->width, $this->height);
		$this->setCurFont();
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
		$this->fpdf->setDrawColor($color[0], $color[1], $color[2]);
	}
	
	function setCurFillColor($colorName)
	{
		parent::setCurFillColor($colorName);
		$color = $this->_getCurFillColor();
		$this->fpdf->setFillColor($color[0], $color[1], $color[2]);
	}
	
	function setCurTextColor($colorName)
	{
		parent::setCurTextColor($colorName);
		$color = $this->_getCurTextColor();
		$this->fpdf->setTextColor($color[0], $color[1], $color[2]);
	}
	
	function breakPage()
	{
		$this->fpdf->AddPage();
	}
	
	function getStringWidth($string)
	{
		return $this->fpdf->GetStringWidth($string);
	}
	
	function addText($x, $y, $txt, $params = array())
	{
		$this->fpdf->Text($x, $y, $txt, $params);
	}
	
	function addLine($x1, $y1, $x2, $y2, $lineWidth = 0.57)
	{
		$this->fpdf->SetLineWidth($lineWidth);
		$this->fpdf->Line($x1, $y1, $x2, $y2);
		//$this->fpdf->SetLineWidth(0.57);
	}
	
	function addHorizLine($left, $right, $top, $lineWidth = 0.57)
	{
		$halfLineWidth = $lineWidth / 2;
		
		$x1 = $left + $halfLineWidth;
		$x2 = $right - $halfLineWidth;
		$y1 = $y2 = $top + $halfLineWidth;
		$this->fpdf->SetLineWidth($lineWidth);
		$this->fpdf->Line($x1, $y1, $x2, $y2);
		//$this->fpdf->SetLineWidth(0.57);
	}
	
	function addVertLine($top, $bottom, $left, $lineWidth = 0.57)
	{
		/*
		$this->fpdf->SetLineWidth(0.57);
		$this->fpdf->Line(36, 0, 36, 1000);
		$this->fpdf->Line(576, 0, 576, 1000);
		*/
		
		$halfLineWidth = $lineWidth / 2;
		
		$x1 = $x2 = $left + $halfLineWidth;
		$y1 = $top + $halfLineWidth;
		$y2 = $bottom - $halfLineWidth;
		$this->fpdf->SetLineWidth($lineWidth);
		$this->fpdf->Line($x1, $y1, $x2, $y2);
		//$this->fpdf->SetLineWidth(0.57);
	}
	
	function addRect($x, $y, $w, $h, $style = 'D')
	{
		$this->fpdf->Rect($x, $y, $w, $h, $style);
	}
	
	function addImage($file, $x, $y, $w=0, $h=0)
	{
		$this->fpdf->Image($file, $x, $y, $w, $h);
	}
	
	function addPolygon($points, $style = 'D')
	{
		$this->fpdf->Polygon($points, $style);
	}
	
	function addCircle($x, $y, $r, $style='D')
	{
		$this->fpdf->Circle($x, $y, $r, $style );
	}
	
	function addEllipse($x, $y, $rx, $ry, $style='D')
	{
		$this->fpdf->Ellipse($x, $y, $rx, $ry, $style);
	}
	
	//	This uses the tweaked logic of imagearc and imagefilledarc below for evaluating angles
	//		not logic that would actually draw the angles passed in.  This may need to be tweaked
	//		for compatibility with other context modules.
	
	function addArc($x, $y, $w, $h, $startAngle, $endAngle, $style='D')
	{
		$this->fpdf->Arc($x, $y, $w / 2, $h / 2, $startAngle, $endAngle, $style);
	}
	
	//	This uses the tweaked logic of imagearc and imagefilledarc for evaluating angles
	//		not logic that would actually draw the angles passed in.  This may need to be tweaked
	//		for compatibility with other context modules.

	function addCylinderSlice($cx, $cy, $w, $h, $sTheta, $eTheta, $depth, $style='D')
	{
		$this->fpdf->CylinderSlice($cx, $cy, $w / 2, $h / 2, $sTheta, $eTheta, $depth, $style);
	}
	
	function setTextFont($newFontStyle)
	{
		assert( is_a($newFontStyle, 'GraphicTextStyle') );
		
		$style = '';
		if($newFontStyle->getUnderline())
			$style .= 'U';
		if($newFontStyle->getBold())
			$style .= 'B';
		if($newFontStyle->getItalics())
			$style .= 'I';
		
		$this->fpdf->SetFont($newFontStyle->getFont(), $style, $newFontStyle->getTextSize());
	}
	
	function setTextSize($size)
	{
		parent::setTextSize($size);
		$this->fpdf->SetFontSize($size);
	}
	
	function setTextStyle($style)
	{
		parent::setTextStyle($style);
		$this->setCurFont();
	}
	
	function setTextFontName($fontName)
	{
		parent::setTextFontName($fontname);
		$this->setCurFont();
	}
	
	function setCurFont()
	{
		$this->fpdf->SetFont($this->getTextFontName(), $this->getTextStyle(), $this->getTextSize());
	}
	
	function setTextColor($r, $g, $b)
	{
		$this->fpdf->SetTextColor($r, $g, $b);
	}
	
	function setLineWidth($lineWidth)
	{
		$this->fpdf->SetLineWidth($lineWidth);
	}
	
	function addRaw($rawData)
	{
		$this->fpdf->Raw($rawData);
	}
	
	function save($filename)
	{
		//	we should all them to set the name here
		$this->fpdf->Output($filename, 'F');
	}
	
	function display()
	{
		//	we should all them to set the name here
		$this->fpdf->Output('', 'I');
	}
}
?>