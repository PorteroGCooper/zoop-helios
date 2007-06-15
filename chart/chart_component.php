<?
/**
* @category zoop
* @package chart
*/
// Copyright (c) 2007 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.
/**
 * component_chart 
 * 
 * @uses component
 * @package chart
 * @version $id$
 * @copyright 1997-2007 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}
 */

class component_chart extends component
{
	function getRequiredComponents()
	{
		return array('graphic');
	}
	
	function getIncludes()
	{
		return array(
			'chartparser' => $this->getBasePath() . '/ChartParser.php',
			'chartobjectparser' => $this->getBasePath() . '/ChartObjectParser.php',
			'chartobject' => $this->getBasePath() . '/ChartObject.php',
			'chartplot' => $this->getBasePath() . '/ChartPlot.php',
			'chartlegend' => $this->getBasePath() . '/ChartLegend.php',
			'chartstring' => $this->getBasePath() . '/ChartString.php',
			'chart' => $this->getBasePath() . '/Chart.php',
			'piechart' => $this->getBasePath() . '/PieChart.php',
			'barchart' => $this->getBasePath() . '/BarChart.php',
			'barchartdatagroup' => $this->getBasePath() . '/BarChartDataGroup.php',
			'horizontalbarchart' => $this->getBasePath() . '/HorizontalBarChart.php',
			'verticalbarchart' => $this->getBasePath() . '/VerticalBarChart.php',
			'deepverticalbarchart' => $this->getBasePath() . '/DeepVerticalBarChart.php',
			'sideverticalbarchart' => $this->getBasePath() . '/SideVerticalBarChart.php',
			'linechart' => $this->getBasePath() . '/LineChart.php',
			'linechartdatagroup' => $this->getBasePath() . '/LineChartDataGroup.php',
		);
	}
}
?>
