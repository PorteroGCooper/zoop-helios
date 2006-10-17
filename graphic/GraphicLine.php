<?
class GraphicLine extends GraphicObject
{
	var $height;
	var $lineWidth;
	var $hexColor;
	var $type;
	
	function GraphicLine(&$context)
	{
		$this->GraphicObject($context);
		$this->setHeight(1);
		$this->type = 'horiz';
	}
	
	function setType($type)
	{
		$this->type = $type;
	}
	
	function setLineWidth($lineWidth)
	{
		$this->lineWidth = $lineWidth;
	}
	
	function setHeight($height)
	{
		$this->height = $height;
	}
	
	function getHeight()
	{
		return $this->height;
	}
	
	function getWidth()
	{
		if( isset($this->width) )
			return $this->width;
		else
			return $this->parent->getContentWidth();
	}
	
	function setHexColor($hexColor)
	{
		assert( strlen($hexColor) == 6 );
		$this->hexColor = $hexColor;
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
		if($reallyDraw)
		{
			//	set the line width
			if($this->lineWidth)
				$this->context->setLineWidth($this->lineWidth);
			
			//	set the line color
			$r = (integer)hexdec(substr($this->hexColor, 0, 2));
			$g = (integer)hexdec(substr($this->hexColor, 2, 2));
			$b = (integer)hexdec(substr($this->hexColor, 4, 2));
			$this->context->addColor($this->hexColor, $r, $g, $b);
			$this->context->pushLineColor($this->hexColor);
			
			$length = isset($this->width) ? $this->width : $width;
			
			$liney = $y + ($this->getHeight() / 2);
			
			if($this->type == 'horiz')
				$this->context->addLine($x, $liney, $x + $length, $liney);
			else if($this->type == 'vert')
			{
				$this->context->addLine($x, $liney, $x, $liney + $length);
			}
			else
				trigger_error("unknown type: " . $this->type);
			
			//	restore the line color (we should probably have push and pop for line widths too)
			$this->context->popLineColor();
		}
		
		return $this->getHeight();
	}
}

?>