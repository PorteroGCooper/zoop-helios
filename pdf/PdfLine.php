<?
/**
* @package pdf
*/
/**
* @package pdf
*/
class PdfLine extends PdfObject
{
	// most of these functions are just placeholders, waiting to be overriden in the classes that inherit from this object
	// this is mostly a template/interface so we know what to implement....
	var $color = array(0,0,0);
	
	function PdfLine(&$pdf, $thickness = 1, $width = -1, $height = -1)
	{
		//echo("pdfofbject constructor" . "<br>");
		$this->thickness = $thickness;
		$this->PdfObject($pdf, $thickness, $width, $height);
	}
	
	function setColor($color = array(0,0,0))
	{
		$this->color = $color;
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
			$this->contentWidth = $this->width;//contents is length...
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
			$this->contentHeight->value = $this->thickness;
			return $this->contentHeight->value;
		}
	}
	
	function draw($x, $y , $align = -1)
	{
			if($align == PdfTableCell_align_center)
			{
				$x = $x + $this->getWidth()/2 - $this->getContentWidth() / 2;
			}
			else if ($align == PdfTableCell_align_right)
			{
				$x = $x + $this->getWidth() - $this->getContentWidth();
			}
			$this->pdf->setLineStyle($this->thickness, "square", "round");
			$this->pdf->setStrokeColor($this->color[0], $this->color[1], $this->color[2]);
			$this->pdf->line($x, $y, $x + $this->getContentWidth(), $y + $this->getHeight());		
	}
}

?>