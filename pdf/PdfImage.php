<?php
/**
* @package pdf
*/
/**
* @package pdf
*/
class PdfImage extends PdfObject
{
	var $image;
	// most of these functions are just placeholders, waiting to be overriden in the classes that inherit from this object
	// this is mostly a template/interface so we know what to implement....
	function PdfImage(&$pdf, $contents = "", $width = -1, $height = -1)
	{
		//echo("pdfofbject constructor" . "<br>");
		$this->PdfObject($pdf, $contents, $width, $height);
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
			$info = getImageSize($this->contents);
			$this->contentWidth = $info[0];
			return $this->contentWidth;
		}
	}
	
	function getContentHeight()
	{
		if($this->contentHeight->value != -1 && $this->contentHeight->width == $this->width)
		{
			return $this->contentHeight;
		}
		else
		{
			$info = getImageSize($this->contents);
			$this->contentHeight->value = $info[1];
			$this->contentHeight->width = $this->width;
			return $this->contentHeight->value;
		}
	}
	
	function draw($x, $y , $align = -1)
	{
		//if($this->width == -1)
		//{
			$info = getImageSize($this->contents);
//			echo("drawing Image at $x, $y");
			$img_type = $info[2];
			if ($img_type=="2") 
			{
				$img = imagecreatefromjpeg($this->contents);
			} 
			elseif ($img_type=="3") 
			{
				$img = imagecreatefrompng($this->contents);
			}
/*			elseif ($img_type=="1") 
			{
				$img = imagecreatefromgif($this->contents);
			}*/
			else 
			{
				return;
			}
//			echo("adding image");
			

			if($align == PdfTableCell_align_center)
			{
				$x = $x + $this->getWidth()/2 - $this->getContentWidth() / 2;
			}
			else if ($align == PdfTableCell_align_right)
			{
				$x = $x + $this->getWidth() - $this->getContentWidth();
			}
			$this->pdf->addImage($img, //filename
				$x ,
				$y - $info[1] - 4,$info[0]);
		//}
	}
}

?>