<?
/**
* @package pdf
*/
/**
* @package pdf
*/
class PdfContainer extends PdfObject
{
	// most of these functions are just placeholders, waiting to be overriden in the classes that inherit from this object
	// this is mostly a template/interface so we know what to implement....
	var $leftBorder = 0;
	var $rightBorder = 0;
	var $topBorder = 0;
	var $bottomBorder = 0;
	
	function pdfContainer(&$pdf, $contents = array(), $width = kPdf_default_page_width, $height = -1)
	{
		//echo("pdfcontainer constructor" . "<br>");
		$this->PdfObject($pdf, $contents, $width, $height);
	}
	
	function addElement($pdfObject)
	{
		$this->contents[] = $pdfObject;
	}
	
	function removeElement($id)
	{
		array_splice($this->contents,$id,1);//removes $contents[$id] and shifts array
	}
	
	function getElement($id)
	{
		return $this->contents[$id];
	}
	
	function setElement($id, $pdfObject)
	{
		$this->contents[$id] = $pdfObject;
	}
	
	function setElementWidth($id, $width)
	{
		$this->contents[$id]->setWidth($width);
	}
	
	function getElementWidth($id)
	{
		return $this->contents[$id]->getWidth();
	}
	
	function setBorder($border)
	{
		$this->leftBorder = $border;
		$this->rightBorder = $border;
		$this->topBorder = $border;
		$this->bottomBorder = $border;
	}
	
	function setLeftBorder($border)
	{
		$this->leftBorder = $border;
	}
	
	function setRightBorder($border)
	{
		$this->rightBorder = $border;
	}
	
	function setTopBorder($border)
	{
		$this->topBorder = $border;
	}
	
	function setBottomBorder($border)
	{
		$this->bottomBorder = $border;
	}
		
	function setBorderColor($borderColor)
	{
		$this->borderColor = $borderColor;
	}
	
	function drawBorder($x, $y)
	{
		if(isset($this->borderColor))
		{
			$this->pdf->setStrokeColor($this->borderColor[0], $this->borderColor[1], $this->borderColor[2]);
		}
		if($this->leftBorder != 0)
		{			
			$this->pdf->setLineStyle($this->leftBorder);
			$this->pdf->line($x + $this->leftBorder * .5, $y - $this->getHeight(), $x + $this->leftBorder * .5, $y);
			//$this->pdf->rectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
		}
		if($this->rightBorder != 0)
		{			
			$this->pdf->setLineStyle($this->rightBorder);
			$this->pdf->line($x + $this->getWidth() - $this->leftBorder * .5, $y - $this->getHeight() + .5 , $x  + $this->getWidth() - $this->leftBorder * .5, $y);
			//$this->pdf->rectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
		}
		if($this->topBorder != 0)
		{			
			$this->pdf->setLineStyle($this->topBorder);
			$this->pdf->line($x, $y, $x + $this->getWidth(), $y);
			//$this->pdf->rectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
		}
		if($this->bottomBorder != 0)
		{			
			$this->pdf->setLineStyle($this->bottomBorder);
			$this->pdf->line($x, $y - $this->getHeight() + $this->bottomBorder * .5 , $x + $this->getWidth(), $y - $this->getHeight() + $this->bottomBorder * .5);
			//$this->pdf->rectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
		}
		$this->pdf->setStrokeColor(0, 0, 0);
	}
}

?>