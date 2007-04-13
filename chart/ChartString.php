<?
class ChartString extends ChartObject
{
	var $text;
	var $size;
	
	function ChartString($context)
	{
		$this->text = '';
		$this->size = 10;
		$this->ChartObject($context);
	}
	
	function setText($text)
	{
		$this->text = $text;
	}
	
	function setSize($size)
	{
		$this->size = $size;
	}
	
	function substitute()
	{
		//$this->text = sprintf($this->text, $this->chart->getValueTotal());
		$this->text = str_replace("%n", $this->chart->getValueTotal(), $this->text);
	}
	
	function draw($x, $y, $width, $reallyDraw = 1)
	{
		if($reallyDraw)
		{
			$this->substitute();
			$this->context->setTextSize($this->size);
			$this->context->addText($x, $y + $this->size, $this->text);
		}
		
		return $this->size;
	}
	
	function getWidth()
	{
		$this->substitute();
		$this->context->setTextSize($this->size);
		return $this->context->getStringWidth($this->text);
	}
}
?>