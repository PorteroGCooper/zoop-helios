<?
class PercentHorizontalBarChart extends HorizontalBarChart
{
	var $summary;
	
	function PercentHorizontalBarChart(&$context)
	{
		$this->HorizontalBarChart($context);
		$this->summary = array();
		$this->setPlotMargin('left', 50);
		$this->setPlotMargin('right', 0);
	}
	
	function drawTics()
	{
	}
	
	function drawAxis($x, $y, $width, $reallyDraw)
	{
		$count = count($this->summaryLabel);
		$newWidth = $width - 75 * $count;
		parent::drawAxis($x, $y, $newWidth, $reallyDraw);
		foreach($this->summaryLabel as $label)
		{
			$label->draw($x + $newWidth, $y, 70, $reallyDraw);
			$x += 75;
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
			$fontsize = $this->context->getTextSize();
			$this->context->setTextSize($fontsize - 2);
			foreach($thisGroup->getEntries() as $thisEntry)
			{
				$width = $thisEntry['value'] * $this->getPixelsPerReal($thisGroup);
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
					if($width > 1)
					{
						if($width < 40)
							$value = round($thisEntry['value']);
						else
							$value = round($thisEntry['value']);
						$value = $value . ($thisGroup->getDisplay() == 'percent' ? '%' : '');
						$labely = $cury  + ($this->dataEntryThickness / 2) + ($this->context->getTextSize() / 2);
						$textx = (isset($this->depthx) ? $curx + ($width / 2) + $this->depthx : ($curx + ($width / 2))) - ($this->context->getStringWidth($value) / 2);
						
						$this->context->addText($textx, $labely, $value);
						
					}
				}
				
				$curx += $width;
			}
			$this->context->setTextSize($fontsize);
			foreach($this->summary as $summaryName)
			{
				$summaryData = $thisGroup->getSummaryData($summaryName);
				//draw the text that follows each box
				$textx = isset($this->depthx) ? $curx + $this->depthx : $curx;
				$textx += ($this->getSummaryWidth($summaryName) / 2) - ($this->context->getStringWidth($summaryData) / 2);
				//echo 'part = ' . $this->calced->groupTotal[$name] . '<br>';
				//echo 'total = ' . $this->calced->grandTotal . '<br>';
				$labely = $cury  + ($this->dataEntryThickness / 2) + ($this->context->getTextSize() / 2);
				$this->context->addText($textx, $labely, $summaryData);
				$curx += $this->getSummaryWidth($summaryName) + 5;
			}
			
			$cury += $this->dataEntryThickness + $this->dataEntryMiddleMargin;
		}
	}
	
	function addSummary($name)
	{
		$this->summary[$name] = $name;
		$this->summaryLabel[$name] = &new GraphicDiv($this->context);
		$this->summaryLabel[$name]->setParent($this);
		$tr = &$this->summaryLabel[$name]->getNewTextRun($this);
		$styleStack = &new GraphicTextStyleStack();
		$topStyle = &$styleStack->getTopStyle();
		$this->summaryLabel[$name]->setAlignment('center');
		$tr->setStyle($topStyle);
		$tr->setText(array('content' => $name, 'leftTrim' => '', 'rightTrim' => ''));
	}
	
	function getSummaryWidth($name)
	{
		//break it into two lines, then get the width
		return 70;
		
	}
	
	function getPixelsPerReal(&$thisGroup)
	{
		$pixelWidth = $this->plotRight - $this->plotLeft;
		$distance = $this->depthx ? $pixelWidth - $this->depthx : $pixelWidth;
		//???? This should make it so that each is percentage of total.
		return $distance / ($thisGroup->getTotal());
	}
}
?>
