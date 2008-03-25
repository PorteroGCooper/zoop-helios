<?
class VerticalBarChart extends BarChart
{
	function VerticalBarChart(&$context)
	{
		$this->BarChart($context);
		
		$this->setPlotMargin('left', 35);
		$this->setPlotMargin('right', 0);
		$this->setPlotMargin('top', 30);
		$this->setPlotMargin('bottom', 50);
		$this->grouping = 'simple';
	}

	function drawAxis($x, $y, $width, $reallyDraw)
	{
		//	this kind of forces the axis to be drawn first
		$this->plotLeft = $x + $this->getPlotMargin('left');
		$this->plotTop = $y + $this->getPlotMargin('top');
		$this->plotRight = $x + $width - $this->getPlotMargin('right');
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
	
	
	function drawData($x, $y, $width, $reallyDraw)
	{
		$curx = $this->plotLeft + $this->dataEntryTopMargin;
		$grouping = $this->grouping;
		foreach($this->groups as $name => $thisGroup)
		{
			//	draw the text label
			if($grouping != 'side')
				$groupWidth = $this->dataEntryThickness + $this->dataEntryMiddleMargin;
			else
				$groupWidth = $this->dataEntryThickness * count($thisGroup->getEntries()) + $this->dataEntryMiddleMargin;
			$textMargin = 2;
			$this->drawTextBox($curx - (($this->dataEntryMiddleMargin - ($textMargin / 2))/2), 
								$this->plotBottom + 4, $groupWidth - $textMargin, $thisGroup->getText(), 
								array('alignment' => 'center', 'textSize' => 8));
			
			//	draw the boxes
			$cury = $this->plotBottom;
			foreach($thisGroup->getEntries() as $thisEntry)
			{
				$height = $thisEntry['value'] * $this->getPixelsPerReal();
				$this->context->setCurFillColor($thisEntry['category']);
				
				if( isset($thisEntry['url']) )
					$url = $thisEntry['url'];
				else
					$url = NULL;
				
				$this->context->addRect($curx, $cury - $height, $this->dataEntryThickness, $height, 'DF', $url);
				
				if($this->depthx)
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
				if($grouping != 'side')
					$cury -= $height;
				else
					$curx += $this->dataEntryThickness;
			}
			
			//	draw the text that follows each box
			$texty = isset($this->depthy) ? $cury - $this->depthy - 10 : $cury - 10;
			if($grouping != 'side')
			{
				if(!$this->getGrandTotal())
					$this->context->addText($curx, $texty, '0%');
				else
					$this->context->addText($curx, $texty, Round(($this->groups[$name]->getTotal() / $this->getGrandTotal()) * 100, 1) . '%');
				
				$curx += $this->dataEntryThickness + $this->dataEntryMiddleMargin;
			}
			else
			{
				$curx += $this->dataEntryMiddleMargin;
			}
			if($curx - $x  > $width)
			{
				return true;
			}
		}
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
	
	function calcBarSpacing()
	{
		assert($this->dataEntryBarSpaceRatio);
		
		$totalSpace = $this->plotRight - $this->plotLeft - $this->depthx;
		$totalBarLength = $this->dataEntryBarSpaceRatio * $totalSpace;
		$totalSpaceLength = $totalSpace - $totalBarLength;
		
		if($this->grouping == 'side')
		{
			$nEntries = 0;
			$nMargins = 1;
			foreach($this->groups as $group)
			{
				$nEntries += count($group->getEntries());
				$nMargins++;
			}
		}
		else
		{
			$nEntries = count($this->groups);
			$nMargins = $nEntries + 1;
		}
		
		$this->dataEntryThickness = $totalBarLength / $nEntries;
		$this->dataEntryMiddleMargin = $totalSpaceLength / ($nMargins);
		$this->dataEntryTopMargin = $this->dataEntryMiddleMargin;
	}
	
	function setGrouping($grouping)
	{
		$this->grouping = $grouping;
	}
	
	function getDataMax()
	{
		if($this->grouping == 'simple')
			return $this->getBiggestGroupTotal();
		else
			return $this->getBiggestItemValue();
	}
}
?>