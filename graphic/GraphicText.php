<?php
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
	
	function getSubstitutions()
	{
		$document = $this->getDocument();
		$text = str_replace('%page_number%', $document->getPageNumber(), $this->text);
		return $text;
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
		if($reallyDraw)
		{
			$text = $this->getSubstitutions();
			$this->context->addText($x, $y, $text, array('angle' => $this->angle));
		}
		
		return 10;
	}
}
