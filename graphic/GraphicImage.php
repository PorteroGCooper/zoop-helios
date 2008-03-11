<?
class GraphicImage extends GraphicObject
{
	var $file;
	var $height;
	var $width;
	
	function GraphicRectangle(&$context)
	{
		$this->GraphicObject($context);
		$this->height = 10;
		$this->width = 10;
	}
	
	function getFile()
	{
		return $this->file;
	}
	
	function setFile($file)
	{
		$this->file = $file;
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
	
	function draw($x, $y, $width, $reallyDraw)
	{
		//assert($this->file);
		if($reallyDraw)
			$this->context->addImage($this->getFile(), $x, $y, $this->getWidth(), $this->getHeight());
		
		return $this->getHeight();
	}
}
?>