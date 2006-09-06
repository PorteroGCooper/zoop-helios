<?php
/**
* @package pdf
*/
/**
* @package pdf
*/
class PdfCircle extends PdfObject
{
	// most of these functions are just placeholders, waiting to be overriden in the classes that inherit from this object
	// this is mostly a template/interface so we know what to implement....
	var $color = array(0,0,0);
	var $filled = 1;
	
	function PdfCircle(&$pdf, $radius = 1, $width = -1, $height = -1)
	{
		//echo("pdfCirclet constructor" . "<br>");
		$this->radius = $radius;
		$this->PdfObject($pdf, $radius, $width, $height);		
	}
	
	function setColor($color = array(0,0,0))
	{
		$this->color = $color;
	}
	
	function setFileed($filled)
	{
		$this->filled = $filled;
	}
	
	function getHeight()
	{
		if($this->height == -1)
			return $this->getContentHeight();
		else
			return $this->height;
	}
	
	function getContentWidth()
	{
		if(isset($this->contentWidth) && $this->contentWidth != -1)
		{
			return $this->contentWidth;
		}
		else
		{
			$this->contentWidth = $this->radius * 2;
			return $this->contentWidth;
		}
	}
	
	function getContentHeight()
	{
		if(isset($this->contentHeight->value) && $this->contentHeight->value != -1)
		{
			return $this->contentHeight->value;
		}
		else
		{
			$this->contentHeight->value = $this->getHeight();
			return $this->contentHeight->value;
		}
	}
	
	function draw($x, $y , $align = -1)
	{
		//echo($x . "\r\n");
			if($align == PdfTableCell_align_center)
			{
				$x = $x + $this->getWidth()/2 - $this->getContentWidth() / 2;
			}
			else if ($align == PdfTableCell_align_right)
			{
				$x = $x + $this->getWidth() - $this->getContentWidth();
			}
		//echo($this->getContentWidth() . " ");
		//echo($x + ($this->getContentWidth() / 2) . "\r\n<br>");
			//$this->pdf->setLineStyle($this->radius, "square", "round");
			$this->pdf->setStrokeColor($this->color[0], $this->color[1], $this->color[2]);
			if($this->filled)
				$this->pdf->filledellipse($x + ($this->getContentWidth() / 2), $y - ($this->getHeight()/2), $this->radius);//$x + $this->getContentWidth(), $y + $this->getHeight());		
			else
				$this->pdf->ellipse($x + $this->getContentWidth() / 2, $y - ($this->getHeight()/2), $this->radius);//$x + $this->getContentWidth(), $y + $this->getHeight());		
	}
}

?>