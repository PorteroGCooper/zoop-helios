<?
class ChartObject extends GraphicObject
{
	var $chart;
	
	function setParent(&$parent)
	{
		$this->parent = &$parent;
		
		$curParent = &$this->parent;
		while($curParent)
		{
			if(is_a($curParent, 'chart'))
			{
				$this->chart = &$curParent;
				return;
			}
			
			$curParent = &$curParent->parent;
		}
		
		trigger_error('a ChartPlot object must be a descendant of a Chart object');
	}
	
	function draw($x, $y, $width, $reallyDraw = 1)
	{
		trigger_error('virtual function');
	}
}
?>