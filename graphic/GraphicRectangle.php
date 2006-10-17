<?
class GraphicRectangle extends GraphicObject
{
	var $height;
	var $width;
	var $color;
	
	function GraphicRectangle(&$context)
	{
		$this->GraphicObject($context);
		$this->height = 10;
		$this->width = 10;
		$this->color = '#000000';
	}
	
	function setHeight($height)
	{
		$this->height = $height;
	}
		
	function setWidth($width)
	{
		$this->width = $width;
	}
	
	function getHeight()
	{
		return $this->height;
	}
	
	function getWidth()
	{
		return $this->width;
	}
	
	function getColor()
	{
		return $this->color;
	}
	
	function setColor($color)
	{
		$this->color = $color;
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
		$rgb = HexToRgb($this->color);
		$this->context->addColor($this->color, $rgb[0], $rgb[1], $rgb[2]);
		$this->context->setCurFillColor($this->color);
		$this->context->addRect($x, $y, $this->getWidth(), $this->getHeight(), 'F');
	}
}
?>