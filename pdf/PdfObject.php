<?
class PdfObject
{
	// most of these functions are just placeholders, waiting to be overriden in the classes that inherit from this object
	// this is mostly a template/interface so we know what to implement....
	var $pdf = null;
	var $width = 0;
	var $height = -1;
	var $contents;
	var $contentHeight = -1;
	var $bgColor = array(1,1,1);
	var $bgObject = null;
	
	function pdfObject(&$pdf, $contents = "", $width = kPdf_default_page_width, $height = kPdf_default_page_height)
	{
		//echo("pdfofbject constructor" . "<br>");
		$this->pdf = &$pdf;
		$this->width = $width;
		$this->height = $height;
		$this->contents = $contents;
	}
	
	function setBorder($border)
	{
		$this->border = $border;
	}
	
	function getBorder()
	{
		return $this->border;
	}
	
	function setContents($contents)
	{
		$this->contents = $contents;
	}
	
	function getContents()
	{
		return $this->contents;
	}
	
	function setBGColor($color = array(1,1,1))
	{
		$this->bgColor = $color;
	}
	
	function setBackground(&$object)
	{
		$this->bgObject = &$object;
	}
	
	function setWidth($width)
	{
		$this->width = $width;
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function setHeight($height)
	{
		$this->height = $height;
	}
	
	function getHeight()
	{
		if($this->height == -1)
			return $this->getContentHeight();
		else
			return $this->height;
	}
	
	function getContentHeight()
	{
		if($this->contentHeight->value != -1 && $this->contentHeight->width == $this->width)
		{
			return $this->contentHeight;
		}
		else
		{
			$this->contentHeight->value = $this->lineHeight * count($this->lines);//really stupid, but I'm generic, remember?
			$this->contentHeight->width = $this->width;
			return $this->contentHeight;
		}
	}
	
	function draw($x, $y)
	{
		$lines = $this->getLines();
		$i = 0;
		while($y < $this->height && $i < count($lines))
		{
			//draw one line, then increment $y, is a basic algorithm.
			$this->pdf->addText($x, $y, $this->textSize, $lines[$i], $this->textAngle);
			$i++;
		}
	}
	
	function printr($depth=0)
	{
		if($depth == 0)
		{
			echo("<pre>");
		}
		for($i = 0; $i < $depth; $i++) echo("\t");
		echo(get_class($this) . "\r\n");
		for($i = 0; $i < $depth; $i++) echo("\t");
		echo("{\r\n");
		$vars = get_object_vars($this);
		foreach($vars as $name => $val)
		{
			if($name == 'contents' && is_array($val))
			{
				for($i = 0; $i <= $depth; $i++) echo("\t");
				echo("->$name =>\r\n");
				foreach($val as $key => $content)
				{
					for($i = 0; $i <= $depth+1; $i++) echo("\t");
					echo("[$key] =>\r\n");
					if(gettype($content) == "object")
						$content->printr($depth+2);
					else
						echo($content);
				}
			}
			else
			{
				for($i = 0; $i <= $depth; $i++) echo("\t");
				echo("->$name => $val\r\n");
			}
		}
		for($i = 0; $i < $depth; $i++) echo("\t");
		echo("}\r\n");
		if($depth == 0)
		{
			echo("</pre>");
		}			
	}
}

?>