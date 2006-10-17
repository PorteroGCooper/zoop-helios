<?
class Chart extends GraphicDiv
{
	var $data;
	var $plotHeight;
	
	function Chart(&$context)
	{
		$this->GraphicDiv($context);
		
		$this->data = array();
		$this->plotHeight = 100;
	}
	
	function setPlotHeight($height)
	{
		$this->plotHeight = $height;
	}
	
	function getPlotHeight()
	{
		return $this->plotHeight;
	}
		
	function addDataEntry($data)
	{
		$key = count($this->data);
		$this->data[$key] = $data;
		return $key;
	}
	
	function drawTextBox($x, $y, $width, $text, $params = NULL)
	{
		$div = new GraphicDiv($this->context);
		if( isset($params['alignment']) )
			$div->setAlignment($params['alignment']);
		$textRun = &$div->getNewTextRun();
		$style = new GraphicTextStyle();
		if( isset($params['textSize']) )
			$style->setTextSize($params['textSize']);
		$textRun->setStyle($style);
		$textRun->setText(array('content' => $text, 'leftTrim' => 0, 'rightTrim' => 0));
		$div->draw($x, $y, $width);
	}
	
	/*
	function addTextItem($x, $y, $text)
	{
		$this->context->addText($x, $y, $text);
	}
	
	
	function getMap($x, $y, $width)
	{
		$this->draw($x, $y, $width, 1);
		return $this->context->getMap();
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
//		echo "$width < $this->width";
//		if($width < $this->width)
//			trigger_error('Not enough space to draw graph');
		
		//	do all the drawing
		$this->drawTitles($x, $y, $width, $reallyDraw);
		$this->drawLegend($x, $y, $width, $reallyDraw);
		$this->drawPlotArea($x, $y, $width, $reallyDraw);
		
		if($this->addDate)
			$this->context->addText($this->leftMargin, $this->height + 90, 'Printed: ' . date("F j, Y"));
		
		//	output it to the browser
		$this->context->display();
	}
	
	function drawTitles($x, $y, $width, $reallyDraw)
	{
		if($this->titles)
		{
			$top = 0;
			foreach($this->titles as $thisTitle)
			{
				if($thisTitle['text'] == '')
					continue;
				$top += $thisTitle['size'];
				$this->context->setTextSize($thisTitle['size']);
				$stringWidth = $this->context->getStringWidth($thisTitle['text']);
				$left = $x + (($this->width - $stringWidth) / 2);
				$this->addTextItem($left, $top + $y, $thisTitle['text']);
				$top += $thisTitle['size'] * 0.5;
			}
		}
		else
		{
			if($this->title)
			{
				$stringWidth = $this->context->getStringWidth($this->title);
				$left = $x + (($this->width - $stringWidth) / 2);
				$top = 10;
				$this->addTextItem($left, $top + $y, $this->title);
			}

			if($this->subTitle)
			{
				$stringWidth = $this->context->getStringWidth($this->subTitle);
				$left = $x + (($this->width - $stringWidth) / 2);
				$top = 25;
				$this->addTextItem($left, $top + $y, $this->subTitle);
			}
		}
		
		$this->context->setTextSize(10);
	}
	*/
}
?>