<?
class LineChart extends Chart
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
	
	function LineChart(&$context)
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
		
		$this->groups[$name] = new LineChartDataGroup();
		
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
	
	/*
	function setDepth($angle, $length)
	{
		$this->depthx = $length * cos(deg2rad($angle));
		$this->depthy = $length * sin(deg2rad($angle));
	}
	*/

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
	
	function addLegendEntry($name, $text, $color)
	{
		//echo "$name, $text, $color<Br>";
		$this->legendData[$name]['text'] = $text;
		$this->legendData[$name]['color'] = $color;
		$this->context->addColor($name, $color[0], $color[1], $color[2]);
	}
	
	function drawLegend($x, $y, $width, $reallyDraw)
	{
		$legendHeight = 30;
		
		//echo_r($this->groups);
		
		//	figure out how big the box needs to be
		$curWidth = 10;
		foreach($this->legendData as $name => $thisLegendEntry)
		{
			//echo_r($thisLegendEntry);
			if(!$this->getCatCount($name))
				continue;
			$newWidth = $this->context->getStringWidth($thisLegendEntry['text']) + 30;
			if($curWidth + $newWidth >= $width)
			{
				$curWidth = 10;
				$legendHeight += 15;
			}
			$curWidth += $newWidth;
		}
		
		if(!$reallyDraw)
			return $legendHeight;
		
		if(count($this->legendData) < 2)
			return 0;
		
		//	draw the box
		$left = $x;
		$top = $y;
		$this->context->setCurLineColor('black');
		if($legendHeight == 30)
			$this->context->addRect($left, $top, $curWidth, $legendHeight);
		else
			$this->context->addRect($left, $top, $width, $legendHeight);
			
		//	draw the text and color boxes
		$curx = $left + 10;
		foreach($this->legendData as $name => $thisLegendEntry)
		{
			if(!$this->getCatCount($name))
				continue;
			$newx = $this->context->getStringWidth($thisLegendEntry['text']) + 10;
			if(abs($left - ($curx + $newx + 20)) >= $width)
			{
				$curx = $left + 10;
				$top += 15;
			}
			$this->context->setCurLineColor($name);
			$this->context->addLine($curx, $top + 17.5, $curx + 10, $top + 17.5, 1);
			$curx += 20;
			$this->context->addText($curx, $top + 20, $thisLegendEntry['text']);
			$curx += $newx;
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
			$newWidth = $this->context->getStringWidth($thisLegendEntry['text']) + 30;
			if($curWidth + $newWidth > $this->context->width)
				break;
			$curWidth += $newWidth;
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
	
	function drawAxis($x, $y, $width, $reallyDraw)
	{
		//	this kind of forces the axis to be drawn first
		$this->plotLeft = $x + $this->getPlotMargin('left');
		$this->plotTop = $y + $this->getPlotMargin('top');
		$this->plotRight = $x + $width - $this->getPlotMargin('right');
		/*
		dump_r($width);
		dump_r($x);
		dump_r($this->getPlotMargin('right'));
		dump_r($this->getPlotMargin('left'));
		dump_r($this->plotLeft);
		dump_r($this->plotRight);
		*/
		$this->plotBottom = $y + $this->plotHeight - $this->getPlotMargin('bottom');
		
		//	calculate out how to space the bars if they have chosen this option
		if( $this->dataEntryBarSpaceRatio )
			$this->calcBarSpacing();
		
		//
		//	draw the x axis
		//
		if($this->depthx)
		{
			$this->context->setCurFillColor('gray');
			$this->context->addPolygon( array($this->plotLeft, $this->plotBottom,
												$this->plotLeft + $this->depthx, $this->plotBottom - $this->depthy,
												$this->plotRight, $this->plotBottom - $this->depthy,
												$this->plotRight - $this->depthx, $this->plotBottom), 'DF' );
		}
		else
		{
			$this->context->addLine($this->plotLeft, $this->plotBottom, $this->plotRight, $this->plotBottom);
		}
		
		//
		//	draw the y axis
		//
		if($this->depthx)
		{
			$this->context->addLine($this->plotLeft, $this->plotBottom, $this->plotLeft, $this->plotTop + $this->depthy);
			$axisLength = $this->plotBottom - $this->plotTop + $this->depthy;
		}
		else
		{
			$this->context->addLine($this->plotLeft, 
									$this->plotBottom,
									$this->plotLeft, 
									$this->plotTop);
			$axisLength = $this->plotBottom - $this->plotTop;
		}
		
		/*
		//	draw the unit label for the y axis
		$ticLabelWidth = $this->context->getStringWidth($this->max);
		$stringWidth = $this->context->getStringWidth($this->scaleLabel);
		$this->context->addText($plotLeft - $ticLabelWidth - 30, $plotTop + (($axisLength + $stringWidth) / 2), $this->scaleLabel, array('angle' => 270));
		*/
	}
	
	function drawTics($x, $y, $width, $reallyDraw)
	{
		$pixelIncrement = $this->getPixelIncrement();
		$realIncrement = $this->getRealIncrement();
		
		if(!$pixelIncrement)
			return;
		
		$this->context->setTextSize(10);
		
		$curRealValue = $this->min;
		$endy = $this->depthy ? $this->plotTop + $this->depthy : $this->plotTop;
		$endy -= 1;	//	fudge it a bit
		for($cury = $this->plotBottom - $pixelIncrement; $cury >= $endy; $cury -= $pixelIncrement)
		{
			$curRealValue += $realIncrement;
			
			//echo "{$this->max} $pixelIncrement $cury >= $plotTop $curRealValue<br>";
			
			//	draw the tic line
			if($this->depthx)
			{
				$this->context->addLine($this->plotLeft, $cury, $this->plotLeft + $this->depthx, $cury - $this->depthy);
				$this->context->addLine($this->plotLeft + $this->depthx, $cury - $this->depthy, $this->plotRight,  $cury - $this->depthy);
			}
			else
			{
				$this->context->addLine($this->plotLeft + $this->depthx, $cury - $this->depthy, $this->plotRight,  $cury - $this->depthy);
			}
			
			//	draw the tic label
			$stringWidth = $this->context->getStringWidth($curRealValue);
			$this->context->addText($this->plotLeft - $stringWidth - 10, $cury + 7, $curRealValue);
		}
	}
	
	function calcBarSpacing()
	{
		assert($this->dataEntryBarSpaceRatio);
		
		$totalSpace = $this->plotRight - $this->plotLeft - $this->depthx;
		$totalBarLength = $this->dataEntryBarSpaceRatio * $totalSpace;
		$totalSpaceLength = $totalSpace - $totalBarLength;
		
		//	this will also need to change for real grouped charts
		$nEntries = count($this->groups);
		$this->dataEntryThickness = $totalBarLength / $nEntries;
		$this->dataEntryMiddleMargin = $totalSpaceLength / ($nEntries + 1);
		$this->dataEntryTopMargin = $this->dataEntryMiddleMargin;
	}
	
	function getPixelsPerReal()
	{
		$distance = $this->depthy ? ($this->plotBottom - $this->plotTop) - $this->depthy : ($this->plotBottom - $this->plotTop);
		return $distance / ($this->max - $this->min);
	}
	
	function getPixelIncrement()
	{
		$distance = $this->depthy ? ($this->plotBottom - $this->plotTop) - $this->depthy : ($this->plotBottom - $this->plotTop);
		
		if($this->increment)
			return ($this->increment / ($this->max - $this->min)) * $distance;
		
		if($this->numDivisions)
			return $distance / $this->numDivisions;
		
		return false;
	}
	
	function drawData($x, $y, $width, $reallyDraw)
	{
		$curx = $this->plotLeft + $this->dataEntryTopMargin;
		$i = 0;
		foreach($this->groups as $name => $thisGroup)
		{
			//	draw the text label
			$groupWidth = $this->dataEntryThickness + $this->dataEntryMiddleMargin;
			$textMargin = 1;
			if($groupWidth - $textMargin > 10)
			{
				$this->drawTextBox($curx - (($this->dataEntryMiddleMargin - ($textMargin / 2))/2), 
								$this->plotBottom + 4, $groupWidth - $textMargin, $thisGroup->getText(), 
								array('alignment' => 'center', 'textSize' => 8));
			}
			else if($i % 2 == 0)
			{
				$this->drawTextBox($curx - (($this->dataEntryMiddleMargin - ($textMargin / 2))), 
								$this->plotBottom + 4, ($groupWidth - $textMargin) * 2, $thisGroup->getText(), 
								array('alignment' => 'center', 'textSize' => 8));
				
			}
			$i++;
			//	draw the boxes
			foreach($thisGroup->getEntries() as $thisEntry)
			{
				
				$cury[$thisEntry['category']] = $this->plotBottom - $thisEntry['value'] * $this->getPixelsPerReal();
				$this->context->setCurLineColor($thisEntry['category']);
				
				if(isset($prevx) && isset($prevy[$thisEntry['category']]) )
				{
					$this->context->addLine($prevx, $prevy[$thisEntry['category']], $curx, $cury[$thisEntry['category']], 1);
				}
				
				$prevy[$thisEntry['category']] = $cury[$thisEntry['category']];
				//
				
				if(false && $this->depthx)
				{
					$this->context->addPolygon( array($curx, $cury - $height,
														$curx + $this->depthx, $cury - $height - $this->depthy,
														$curx + $this->depthx + $this->dataEntryThickness, $cury - $height - $this->depthy,
														$curx + $this->dataEntryThickness, $cury - $height), 'DF', $url);
				
					$this->context->addPolygon( array($curx + $this->dataEntryThickness, $cury - $height,
										$curx + $this->depthx + $this->dataEntryThickness, $cury - $height - $this->depthy,
										$curx + $this->depthx + $this->dataEntryThickness, $cury - $this->depthy,
										$curx + $this->dataEntryThickness, $cury), 'DF', $url);
				}
				
				//$cury -= $height;
			}
			
			//	draw the text that follows each box
			/*
			$texty = isset($this->depthy) ? ($cury[$name] - $this->depthy - 10) : $cury - 10;
			
			if(!$this->getGrandTotal())
				$this->context->addText($curx + 10, $texty, '0%');
			else
				$this->context->addText($curx + 10, $texty, Round(($this->groups[$name]->getTotal() / $this->getGrandTotal()) * 100, 1) . '%');
			*/
			$prevx = $curx;
			$curx += $this->dataEntryThickness + $this->dataEntryMiddleMargin;
		}
	}
	
	function getDataMax()
	{
		return $this->getBiggestItemValue();
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
		return max($totals);
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