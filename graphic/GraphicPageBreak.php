<?
class GraphicPageBreak extends GraphicObject
{
	function GraphicPageBreak(&$context)
	{
		$this->GraphicObject($context);
	}
	
	function forcePageBreak()
	{
		return 1;
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
		return 0;
	}
}

?>