<?



//require_once(dirname(__file__) . '/CustomDiv.php');
//require_once(dirname(__file__) . '/CustomParser.php');
//require_once(dirname(__file__) . '/CustomObject.php');

class component_chart extends component
{
	function component_chart()
	{
		$this->requireComponent('graphic');
	}
	
	function getIncludes()
	{
		return array(
			'chartparser' => dirname(__file__) . '/ChartParser.php',
			'chartobjectparser' => dirname(__file__) . '/ChartObjectParser.php',
			'chartobject' => dirname(__file__) . '/ChartObject.php',
			'chartplot' => dirname(__file__) . '/ChartPlot.php',
			'chartlegend' => dirname(__file__) . '/ChartLegend.php',
			'chartstring' => dirname(__file__) . '/ChartString.php',
			'chart' => dirname(__file__) . '/Chart.php',
			'piechart' => dirname(__file__) . '/PieChart.php',
			'barchart' => dirname(__file__) . '/BarChart.php',
			'barchartdatagroup' => dirname(__file__) . '/BarChartDataGroup.php',
			'horizontalbarchart' => dirname(__file__) . '/HorizontalBarChart.php',
			'verticalbarchart' => dirname(__file__) . '/VerticalBarChart.php',
			'deepverticalbarchart' => dirname(__file__) . '/DeepVerticalBarChart.php',
			'sideverticalbarchart' => dirname(__file__) . '/SideVerticalBarChart.php',
		);
	}
}
?>