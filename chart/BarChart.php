<?
class BarChart extends Chart
{
	var $groupType;
	var $groups;
	var $plotMargins;
	
	var $depthx;
	var $depthy;
	
	//	stuff for the scale axis
	var $min;
	var $max;
	var $numDivisions;
	var $increment;
	
	var $orientation;
	var $legendData;
	var $type;
	
	var $dataEntryTopMargin;
	var $dataEntryBottomMargin;
	var $dataEntryMiddleMargin;
	var $dataEntryThickness;
	var $dataEntryBarSpaceRatio;
	
	var $calced;
	
	function BarChart(&$context)
	{
		//	pass onto the base class constructor
		$this->Chart($context);
		$this->legendData = array();
		
		$this->setGroupType('simple');
		$this->addLegendEntry('default', 'default', array(128, 128, 128));
		$this->groups = array();
		
		//	set the default plot margins
		$this->setPlotMargin('left', 150);
		$this->setPlotMargin('right', 60);
		$this->setPlotMargin('top', 30);
		$this->setPlotMargin('bottom', 40);
		
		$this->setDataEntryBarSpaceRatio(0.4);
		
		/*
		$this->dataEntryTopMargin = 10;
		$this->dataEntryBottomMargin = 10;
		$this->dataEntryMiddleMargin = 10;
		$this->dataEntryThickness = 10;
		*/
		
		//$this->legendMargin = 45;
	}
	
	function getPlotMargin($name)
	{
		return $this->plotMargins[$name];
	}
	
	function setPlotMargin($name, $margin)
	{
		$this->plotMargins[$name] = $margin;
	}
	
	function &addGroup($name = NULL)
	{
		if( isset($this->groups[$name]) )
			trigger_error("group $name already exists");
		
		$this->groups[$name] = new BarChartDataGroup();
		
		return $this->groups[$name];
	}
	
	function &pushGroup()
	{
		$this->groups[] = new BarChartDataGroup();
		$tmp = array_pop(array_keys($this->groups));
		return $tmp;
	}
	
	function addDataEntry($info)
	{
		if(!isset($info['category']))
			$info['category'] = 'default';
		if(isset($this->legendData[$info['category']]['value']))
			$info['catValue'] = $this->legendData[$info['category']]['value'];
		$simpleGroup = 0;
		if(!isset($info['group']))
		{
			$info['group'] = $this->pushGroup();
			$simpleGroup = 1;
		}
		
		//	get the group and make sure that it exists
		if( isset($this->groups[$info['group']]) )
			$group = &$this->groups[$info['group']];
		else
			$group = &$this->addGroup($info['group']);
		
		if($simpleGroup)
			$group->setText($info['text']);
		
//		echo_r($info);
		
		$group->addEntry($info);
	}
		
	//	this data entry overrides the following three.  
	//		If you set it the others will all be auto calculated
	//		The value must be a number between 0 and 1
	function setDataEntryBarSpaceRatio($dataEntryBarSpaceRatio)
	{
		assert($dataEntryBarSpaceRatio > 0 && $dataEntryBarSpaceRatio < 1);
		$this->dataEntryBarSpaceRatio = $dataEntryBarSpaceRatio;
	}

	function setDataEntryTopMargin($dataEntryTopMargin)
	{
		$this->dataEntryTopMargin = $dataEntryTopMargin;
	}
	
	function setDataEntryMiddleMargin($dataEntryMiddleMargin)
	{
		$this->dataEntryMiddleMargin = $dataEntryMiddleMargin;
	}
	
	function setDataEntryThickness($dataEntryThickness)
	{
		$this->dataEntryThickness = $dataEntryThickness;
	}
	
	function setDepth($angle, $length)
	{
		$this->depthx = $length * cos(deg2rad($angle));
		$this->depthy = $length * sin(deg2rad($angle));
	}

	function setGroupType($groupType)
	{
		$this->groupType = $groupType;
	}

	function getGrouping()
	{
		return $this->grouping;
	}
	
	function setMin($min)
	{
		$this->min = $min;
	}

	function setMax($max)
	{
		$this->max = $max;
	}
		
	function setBarColor($barColor)
	{
		$color = HexToRgb($barColor);
		$this->context->setColor('default', $color[0], $color[1], $color[2]);
	}
	
	function setIncrement($increment)
	{
		$this->numDivisions = NULL;
		$this->increment = $increment;
	}
	
	function setNumDivisions($numDivisions)
	{
		$this->increment = NULL;
		$this->numDivisions = $numDivisions;
	}
	
	function addLegendEntry($name, $text, $color, $value = false)
	{
		//echo "$name, $text, $color<Br>";
		$this->legendData[$name]['text'] = $text;
		$this->legendData[$name]['color'] = $color;
		$this->legendData[$name]['value'] = $value;
		$this->context->addColor($name, $color[0], $color[1], $color[2]);
	}
	
	function drawLegend($x, $y, $width, $reallyDraw)
	{
		$legendHeight = 30;
		
		if(!$reallyDraw)
			return $legendHeight;
		
		if(count($this->legendData) < 2)
			return 0;
		
		//echo_r($this->groups);
		
		//	figure out how big the box needs to be
		$curWidth = 10;
		foreach($this->legendData as $name => $thisLegendEntry)
		{
			//echo_r($thisLegendEntry);
			if(!$this->getCatCount($name))
				continue;
			$curWidth += $this->context->getStringWidth($thisLegendEntry['text']) + 30;
		}
		
		//	draw the box
		$left = $x;
		$top = $y;
		$this->context->addRect($left, $top, $curWidth, $legendHeight);
		
		//	draw the text and color boxes
		$curx = $left + 10;
		foreach($this->legendData as $name => $thisLegendEntry)
		{
			if(!$this->getCatCount($name))
				continue;
			
			$this->context->setCurFillColor($name);
			$this->context->addRect($curx, $top + 10, 10, 10, 'DF');
			$curx += 20;
			$this->context->addText($curx, $top + 20, $thisLegendEntry['text']);
			$curx += $this->context->getStringWidth($thisLegendEntry['text']) + 10;
			
		}
		
		return $legendHeight;
	}
	
	function getLegendWidth()
	{
		if(count($this->legendData) < 2)
			return 0;
		
		//	figure out how big the box needs to be
		$curWidth = 10;
		foreach($this->legendData as $name => $thisLegendEntry)
		{
			//echo_r($thisLegendEntry);
			if(!$this->getCatCount($name))
				continue;
			$curWidth += $this->context->getStringWidth($thisLegendEntry['text']) + 30;
		}
		
		return $curWidth;
	}
	
	function drawPlotArea($x, $y, $width, $reallyDraw)
	{
		if(!$reallyDraw)
			return $this->getPlotHeight();
		
		//	get the min and max if they havne't been set yet
		if($this->min == NULL)
			$this->min = 0;
		
		//	if they didn't specify any increment info then
		//		we calculate it ourselves
		//
		//	actually if max is set we should do something else here
		//		but this will do for now
		//
		if(!$this->increment && !$this->numDivisions)
		{
			//	this is basically doing what's right for simple and stacked bar charts
			//		non-stacked but grouped bars will need to change this
			$biggest = $this->getDataMax();
			
			if($biggest == 0)
			{
				$this->increment = 1;
				$this->max = 10;
			}
			else
			{
				$this->increment = pow(10, floor(log($biggest, 10)));
				$ratio = $biggest / $this->increment;
				
				if($ratio < 3)
					$extraSegments = 1;
				else
					$extraSegments = 2;
				$this->max = (floor($biggest / $this->increment) + $extraSegments) * $this->increment;
				
				if( ($ratio < 3) && ($this->increment > 1) )
				{
					$this->increment = $this->increment / 2;
				}
//				else if($ratio > 8)
//				{
//					$this->increment = $this->increment * 2;
//				}
			}
		}
		//	now do the drawing
		
		$this->drawAxis($x, $y, $width, $reallyDraw);
		$this->drawTics($x, $y, $width, $reallyDraw);
		$this->drawData($x, $y, $width, $reallyDraw);
		
		return $this->getPlotHeight();
	}
	
	function getDataMax()
	{
		return $this->getBiggestGroupTotal();
	}
	
	/*
	*/
	
	/*
	function processData()
	{
		$biggestGroupTotal = 0;
		$biggestItemValue = 0;
		$grandTotal = 0;
		
		foreach($this->groups as $name => $thisGroup)
		{
			$groupValue = 0;
			$biggestGroupItemValue[$name] = 0;
			
			foreach($thisGroup->getEntries() as $thisEntry)
			{
				$itemValue = $thisEntry['value'];
				$groupValue += $itemValue;
				
				if($itemValue > $biggestItemValue)
					$biggestItemValue = $itemValue;
				
				if($itemValue > $biggestGroupItemValue[$name])
					$biggestGroupItemValue[$name] = $itemValue;
			}
			
			
			$groupTotal[$name] = $groupValue;
			$grandTotal += $groupValue;
			
			if($groupValue > $biggestGroupTotal)
				$biggestGroupTotal = $groupValue;
		}
		
		$this->calced->biggestGroupTotal = $biggestGroupTotal;
		$this->calced->biggestItemValue = $biggestItemValue;
		$this->calced->biggestGroupItemValue = $biggestGroupItemValue;
		$this->calced->groupTotal = $groupTotal;
		$this->calced->grandTotal = $grandTotal;
	}
	*/
	
	function getBiggestGroupTotal()
	{
		$totals = array();
		foreach($this->groups as $thisGroup)
			$totals[] = $thisGroup->getTotal();
		return empty($totals) ? 0 : max($totals);
	}
	
	function getBiggestItemValue()
	{
		$maxes = array();
		foreach($this->groups as $thisGroup)
			$maxes[] = $thisGroup->getMax();
		return max($maxes);
	}
	
	function getValueTotal()
	{
		return $this->getGrandTotal();
	}
	
	function getGrandTotal()
	{
		$totals = array();
		foreach($this->groups as $thisGroup)
			$totals[] = $thisGroup->getTotal();
		return array_sum($totals);
	}
	
	function getCatCount($catName)
	{
		$count = 0;
		foreach($this->groups as $thisGroup)
			$count += $thisGroup->catCount($catName);
		return $count;
	}
	
	function getRealIncrement()
	{
		if($this->increment)
			return $this->increment;
		
		if($this->numDivisions)
			return ($this->max - $this->min) / $this->numDivisions;
		
		return false;
	}	
}
?>