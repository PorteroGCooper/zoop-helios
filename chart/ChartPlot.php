<?
class ChartPlot extends ChartObject
{
	function draw($x, $y, $width, $reallyDraw = 1)
	{
		return $this->chart->drawPlotArea($x, $y, $width, $reallyDraw);
	}
}
?>