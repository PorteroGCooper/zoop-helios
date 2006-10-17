<?
class HorizontalBarChart extends BarChart
{
	function HorizontalBarChart(&$context)
	{
		$this->BarChart($context);
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
			$this->context->addLine($this->plotLeft,
									$this->plotBottom,
									$this->plotRight - $this->depthx, 
									$this->plotBottom);
			$axisLength = $this->plotRight - $this->plotLeft + $this->depthx;
		}
		else
		{
			$this->context->addLine($this->plotLeft,
									$this->plotBottom,
									$this->plotRight, 
									$this->plotBottom);
			$axisLength = $this->plotRight - $this->plotLeft;
		}
				
		//
		//	draw the y axis
		//
		if($this->depthx)
		{
			$this->context->setCurFillColor('gray');
			$this->context->addPolygon( array($this->plotLeft, $this->plotBottom,
												$this->plotLeft + $this->depthx, $this->plotBottom - $this->depthy,
												$this->plotLeft + $this->depthx, $this->plotTop,
												$this->plotLeft, $this->plotTop + $this->depthy), 'DF' );
		}
		else
		{
			$this->context->addLine($this->plotLeft, $this->plotTop, $this->plotLeft, $this->plotBottom);			
		}
	}
	
	function drawTics($x, $y, $width, $reallyDraw)
	{
		$pixelIncrement = $this->getPixelIncrement();
		$realIncrement = $this->getRealIncrement();
		//echo "$pixelIncrement $realIncrement<br>";die();
		
		if(!$pixelIncrement)
			return;
		
		$curRealValue = $this->min;
		for($curx = $this->plotLeft + $pixelIncrement; $curx <= $this->plotRight; $curx += $pixelIncrement)
		{
			$curRealValue += $realIncrement;
			
			//	draw the tic line
			if($this->depthx)
			{
				$this->context->addLine($curx, $this->plotBottom,	$curx + $this->depthx, $this->plotBottom - $this->depthy);
				$this->context->addLine($curx + $this->depthx, $this->plotBottom - $this->depthy,	$curx + $this->depthx, $this->plotTop);
			}
			else
			{
				$this->context->addLine($curx, $this->plotTop, $curx, $this->plotBottom);
			}
			
			$this->context->setTextSize(10);
			$label = round($curRealValue);
			$labelLen = $this->context->getStringWidth($label);
			//	draw the tic label
			$this->context->addText($curx - ($labelLen / 2), $this->plotBottom + 15, $label);
		}		
	}
	
	function drawData($x, $y, $width, $reallyDraw)
	{
		$depthy = $this->depthy ? $this->depthy : 0;
		$cury = $this->plotTop + $this->dataEntryTopMargin + $depthy;
		
		foreach($this->groups as $name => $thisGroup)
		{
			//	draw the text label
			$this->drawTextBox($x + 10, $cury, $this->getPlotMargin('left') - 20, $thisGroup->getText(), array('alignment' => 'right'));
			
			//	draw the boxes
			$curx = $this->plotLeft;
			foreach($thisGroup->getEntries() as $thisEntry)
			{
				$width = $thisEntry['value'] * $this->getPixelsPerReal();
//				echo_r($thisEntry);
//				echo_r($this->context->colors);
				$this->context->setCurFillColor($thisEntry['category']);
				
				if( isset($thisEntry['url']) )
					$url = $thisEntry['url'];
				else
					$url = NULL;
				
				$this->context->addRect($curx, $cury, $width, $this->dataEntryThickness, 'DF', $url);
				
				if($this->depthx)
				{
					$this->context->addPolygon( array($curx, $cury,
														$curx + $this->depthx, $cury - $this->depthy,
														$curx + $this->depthx + $width, $cury - $this->depthy,
														$curx + $width, $cury), 'DF', $url);
				
					$this->context->addPolygon( array($curx + $width, $cury,
										$curx + $width + $this->depthx, $cury - $this->depthy,
										$curx + $width + $this->depthx, $cury - $this->depthy + $this->dataEntryThickness,
										$curx + $width, $cury + $this->dataEntryThickness), 'DF', $url);
				}
				
				$curx += $width;
			}
			
			//	draw the text that follows each box
			$textx = isset($this->depthx) ? $curx + $this->depthx + 10 : $curx + 10;
			
			
			//echo 'part = ' . $this->calced->groupTotal[$name] . '<br>';
			//echo 'total = ' . $this->calced->grandTotal . '<br>';
			$label = Round(($this->groups[$name]->getTotal() / $this->getGrandTotal()) * 100, 1) . '%';
			$labely = $cury  + ($this->dataEntryThickness / 2) + ($this->context->getTextSize() / 2);
			$this->context->addText($textx, $labely, $label);
			
			$cury += $this->dataEntryThickness + $this->dataEntryMiddleMargin;
		}
	}
	
	function getPixelsPerReal()
	{
		$pixelWidth = $this->plotRight - $this->plotLeft;
		$distance = $this->depthx ? $pixelWidth - $this->depthx : $pixelWidth;
		return $distance / ($this->max - $this->min);
	}
	
	function getPixelIncrement()
	{
		$pixelWidth = $this->plotRight - $this->plotLeft;
		$distance = $this->depthx ? $pixelWidth - $this->depthx : $pixelWidth;
		
		if($this->increment)
			return ($this->increment / ($this->max - $this->min)) * $distance;
		
		if($this->numDivisions)
			return $distance / $this->numDivisions;
		
		return false;
	}
	
	function calcBarSpacing()
	{
		assert($this->dataEntryBarSpaceRatio);
		
		$totalSpace = $this->plotBottom - $this->plotTop - $this->depthy;
		$totalBarLength = $this->dataEntryBarSpaceRatio * $totalSpace;
		$totalSpaceLength = $totalSpace - $totalBarLength;
		
		//	this will also need to change for real grouped charts
		$nEntries = count($this->groups);
		
		$this->dataEntryThickness = $totalBarLength / $nEntries;
		$this->dataEntryMiddleMargin = $totalSpaceLength / ($nEntries + 1);
		$this->dataEntryTopMargin = $this->dataEntryMiddleMargin;
	}
}
?>