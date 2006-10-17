<?
class SideVerticalBarChart extends VerticalBarChart
{
	var $itemDepth;
	
	function SideVerticalBarChart(&$context)
	{
		$this->VerticalBarChart($context);
	}
	
	function drawData($x, $y, $width, $reallyDraw)
	{
		//if( !isset($this->depthx) || !$this->depthx )
		//	trigger_error('this chart must have depth');
			
		$curx = $this->plotLeft + $this->dataEntryTopMargin;
		foreach($this->groups as $name => $thisGroup)
		{
			if(count($thisGroup->entryCount()) == 0)
				continue;
			
			//	calculate the depth data for each data point in this group
			$itemDepthx = $this->depthx;
			$itemDepthy = $this->depthy;
			
			$barWidth = $this->dataEntryThickness / $thisGroup->entryCount();
			//	draw the text label
			$width = $this->dataEntryThickness + $this->dataEntryMiddleMargin;
			$this->drawTextBox($curx - ($this->dataEntryMiddleMargin/2), $this->plotBottom + 4, $width, 
								$thisGroup->getText(), array('alignment' => 'center', 'textSize' => 8));
			
			//	draw the boxes
			
			$entries = $thisGroup->getEntries();
			
			while(count($entries) > 0)
			{
				$dataPointInfo = array_pop($entries);
				$curGroupx = $curx; //+ (count($entries) * $itemDepthx);
				$curGroupy = $this->plotBottom; //- (count($entries) * $itemDepthy);
				
				$height = $dataPointInfo['value'] * $this->getPixelsPerReal();
				$this->context->setCurFillColor($dataPointInfo['category']);
				
				if( isset($dataPointInfo['url']) )
					$url = $dataPointInfo['url'];
				else
					$url = NULL;
				
				//	the front face
				$this->context->addRect($curGroupx, $curGroupy - $height, $barWidth, $height, 'DF', $url);
				
				//	the top face
				$this->context->addPolygon( array($curGroupx, $curGroupy - $height,
													$curGroupx + $itemDepthx, $curGroupy - $height - $itemDepthy,
													$curGroupx + $itemDepthx + $barWidth, $curGroupy - $height - $itemDepthy,
													$curGroupx + $barWidth, $curGroupy - $height), 'DF', $url);
				//	the side face
				$this->context->addPolygon( array($curGroupx + $barWidth, $curGroupy - $height,
									$curGroupx + $itemDepthx + $barWidth, $curGroupy - $height - $itemDepthy,
									$curGroupx + $itemDepthx + $barWidth, $curGroupy - $itemDepthy,
									$curGroupx + $barWidth, $curGroupy), 'DF', $url);
				$curx += $barWidth;
			}
			
			$curx += $this->dataEntryMiddleMargin;
		}
	}
	
	function getDataMax()
	{
		return $this->getBiggestItemValue();
	}
}
?>