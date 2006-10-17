<?
class CustomDiv extends GraphicDiv
{
	var $done;
	
	function CustomDiv(&$context)
	{
		$this->done = 0;
		$this->GraphicDiv($context);
	}
	
	function doneDrawing()
	{
		return $this->done;
	}
	
	function draw($x, $y, $width, $reallyDraw = 1)
	{
		$this->done = 1;
		return 30;
	}
}
?>