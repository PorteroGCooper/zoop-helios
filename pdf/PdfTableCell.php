<?
/**
* @package pdf
*/

define("PdfTableCell_align_center", 0);
define("PdfTableCell_align_left", 1);
define("PdfTableCell_align_right", 2);

define("pdf_align_center", 0);
define("pdf_align_left", 1);
define("pdf_align_right", 2);

define("PdfTableCell_valign_bottom", 3);
define("PdfTableCell_valign_top", 4);
define("PdfTableCell_valign_middle", 5);
/**
* @package pdf
*/
class PdfTableCell extends PdfContainer
{
	// most of these functions are just placeholders, waiting to be overriden in the classes that inherit from this object
	// this is mostly a template/interface so we know what to implement....
	var $pdf = null;
	var $width = 0;
	var $height = -1;
	var $contents;
	var $contentHeight;
	var $colSpan = 1;
	var $align = -1;
	var $valign = -1;
	
	
	function pdfTableCell(&$pdf, $contents = array(), $colSpan = 1, $width = kPdf_default_page_width, $height = -1)
	{
//		echo("pdftablecell constructor" . "<br>");
		$this->PdfContainer($pdf, $contents, $width, $height);
		$this->setColSpan($colSpan);
		$this->setWidth($width);
	}
	
	function &getNewTextBox($textSize = kPdf_default_text_size, $width = kPdf_default_page_width, $height = -1)
	{
		$box = &new PdfTextBox($this->pdf, '',$textSize, $width, $height);
		$this->addElement($box);
		return $this->contents[count($this->contents) - 1];
	}
	
	function addElement($pdfObject)
	{
		$this->contents[] = $pdfObject;
		$this->contents[count($this->contents)-1]->setWidth($this->width);
	}
	
	function setColSpan($colSpan)
	{
		$this->colSpan = $colSpan;
	}
	
	function getColSpan()
	{
		return $this->colSpan;
	}
	
	function setAlign($align)
	{
		$this->align = $align;
	}
	
	
		
	function setValign($valign)
	{
		$this->valign = $valign;
	}
	
	function setWidth($width)
	{
		$this->width = $width;
		reset($this->contents);
		while(list($key,$val) = each($this->contents))
		{
			$this->contents[$key]->setWidth($width);
		}
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
			$widest = 0;
			reset($this->contents);
			while(list($key, $val) = each($this->contents))
			{
				if($this->contents[$key]->getContentWidth() > $widest)
				{
					$widest = $this->contents[$key]->getContentWidth();
				}
			}
			$this->contentWidth = $widest;
			return $this->contentWidth;
		}
	}
	
	function getContentHeight()//are we horizontal?  vertical?
	{
		
		$curx = 0;
		$height = 0;
		$tallest = 0;
		reset($this->contents);
		while(list($key, $val) = each($this->contents))
		{
			//if($curx + $this->contents[$key]->getContentWidth() < $this->width)
			//{
			//	$curx += $this->contents[$key]->getContentWidth();
				
			//	if($this->contents[$key]->getContentHeight() > $tallest)
			//	{
					$tallest = $this->contents[$key]->getContentHeight();
					//print_r("itemHeight: " . (int)$tallest);
					//echo("<br>");

			//	}
			//}
			//else
			//{
				$height += $tallest;
				$tallest = 0;
			//}
		}
		if($height == 0)
		{
			$this->contentHeight->value = $tallest;
		}
		else
		{
			$this->contentHeight->value = $height;
		}
		$this->contentHeight->width = $this->width;
		//print_r("cellHeight: ". count($this->contents) . " " . $this->contentHeight->value);
		//echo("<br>");
		return $this->contentHeight->value;
	}
	
	function drawBg($x, $y)
	{
		if(!($this->bgColor[0] == 1 && $this->bgColor[1] == 1 && $this->bgColor[2] == 1))
		{
			$this->pdf->setColor($this->bgColor[0], $this->bgColor[1], $this->bgColor[2]);
//			echo($this->getHeight()."<br>");
			$this->pdf->filledRectangle($x, $y - $this->getHeight() , $this->getWidth(), $this->getHeight());
		}
		$this->pdf->setColor(0,0,0);
		
		if($this->bgObject != null)
		{
			$this->bgObject->setWidth($this->getWidth());
			$this->bgObject->setHeight($this->getHeight());
			$this->bgObject->draw($x,$y);		
		}
	}
	
	function draw($x, $y, $align = -1, $valign = -1)//returns undrawn objects.
	{
		if($align == -1)
		{
			if($this->align == -1)
			{
				$align = PdfTableCell_align_left;
			}
			else
			{
				$align = $this->align;
			}
		}
		if($valign == -1)
		{
			if($this->valign == -1)
			{
				$valign = PdfTableCell_valign_top;
			}
			else
			{
				$valign = $this->valign;
			}
		}
		$this->drawBg($x, $y);
		$this->drawBorder($x, $y);
		
		if($valign == PdfTableCell_valign_middle)
		{
			//echo_r($this->getHeight());
			//echo_r($this->getContentHeight());
			$cury = $y - (($this->getHeight() - $this->getContentHeight()) / 2);
		}
		else if($valign == PdfTableCell_valign_bottom)
		{
			$cury = $y - $this->getHeight() + $this->getContentHeight();
		}
		else if($valign == PdfTableCell_valign_top)
		{
			$cury = $y;
		}
		$i = 0;

		while($i < count($this->contents) && ($this->height == -1 || $cury - $y + $this->contents[$i]->getContentHeight() <= $this->height))
		{
			$curx = $x;
			
			$this->contents[$i]->draw($curx, $cury, $align);
			
			/*
			if(get_class($this->contents[$i]) == 'pdftextbox')
			{
				echo $this->contents[$i]->width . '<br>';
				echo $this->contents[$i]->contents . "<br>\n";
				
			}
			*/
			
			//if($curx - $x + $this->contents[$i]->getContentWidth() > $this->width)
			//{
				$cury += $this->contents[$i]->getHeight();
			//}
			//else
			//	$curx += $this->contents[$i]->getContentWidth();
			
			$i++;
		}
		
		return $i;
	}
}
?>
