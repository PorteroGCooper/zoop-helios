<?
class GraphicText extends GraphicObject
{
	var $height;
	var $lineWidth;
	var $hexColor;
	var $type;
	var $text;
	var $angle;
	
	function GraphicText(&$context)
	{
		$this->GraphicObject($context);
		$this->setHeight(1);
		$this->type = 'horiz';
		$this->text = '';
		$this->angle = 0;
	}
	
	function setType($type)
	{
		$this->type = $type;
	}
	
	function setText($text)
	{
		$this->text = $text;
	}
	
	function setAngle($angle)
	{
		$this->angle = $angle;
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
			$this->context->addText($x, $y, $this->text, array('angle' => $this->angle));
		}
		
		return 10;
	}
}

?>