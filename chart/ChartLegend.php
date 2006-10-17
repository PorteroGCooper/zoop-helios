<?
class ChartLegend extends ChartObject
{
	function draw($x, $y, $width, $reallyDraw = 1)
	{
		return $this->chart->drawLegend($x, $y, $width, $reallyDraw);
	}
	
	function getWidth()
	{
		return $this->chart->getLegendWidth();
	}
}
?>