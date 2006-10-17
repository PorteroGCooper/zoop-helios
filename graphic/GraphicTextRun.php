<?
class GraphicTextRun extends GraphicObject
{
	var $context;
	var $text;
	var $style;
	var $leftSpace;
	var $rightSpace;
	var $breakPoints;
	var $charWidths;
	var $totalWidth;
	
	function GraphicTextRun(&$context)
	{
//		echo_backtrace();
		$this->GraphicObject($context);
		$this->text = '';
		$this->leftSpace = 0;
		$this->rightRight = 0;
		$this->style = NULL;
		$this->charWidths = array();
		$this->totalWidth = -1;
	}
	
	function getUnwrappedWidth()
	{
		assert($this->style !== NULL);
		assert($this->totalWidth > -1);
		return $this->totalWidth;
	}
	
	function getWidth()
	{
		assert($this->style !== NULL);
		assert($this->totalWidth > -1);
		return $this->totalWidth;
		
		/*
		$this->context->setTextFont($this->style);
		return $this->context->getStringWidth($this->text);
		*/
	}
	
	function getPartWidth($start, $length)
	{
		assert($this->style !== NULL);
		
		$total = 0;
		for($i = $start; $i < $start + $length; $i++)
			$total += $this->charWidths[$i];
		return $total;
		
		/*
		assert($this->style !== NULL);
		
		if($length == NULL)
			$string = substr($this->text, $start);
		else
			$string = substr($this->text, $start, $length);
		
		$this->context->setTextFont($this->style);
		
		return $this->context->getStringWidth($string);
		*/
	}
	
	function setText($text)
	{
		assert($this->style !== NULL);
		
		//	we need to get rid of the excess white space in the middle
		
		$content = ereg_replace("[[:space:]]+", ' ', $text['content']);
		
		$this->text = $content;
		
		//echo 'START' . $this->text . "END<br>\n";
		
		$this->leftSpace = $text['leftTrim'];
		$this->rightSpace = $text['rightTrim'];
		
		$this->calcCharWidths();
	}
	
	function calcCharWidths()
	{
		assert($this->style !== NULL);
		
		$this->context->setTextFont($this->style);
		
		$len = strlen($this->text);
		
		$totalWidth = 0;
		for($i = 0; $i < $len; $i++)
		{
			if($this->context->useKerningHack())
			{
				if($i == $len - 1)
				{
					$width = $this->context->getStringWidth(substr($this->text, $i, 1));	//	would it be better to use $this->text{$i} here?
					$totalWidth += $width;
					$this->charWidths[$i] = $width;				
				}
				else
				{
					$thisAndNextWidth = $this->context->getStringWidth(substr($this->text, $i, 2));
					$justNextWidth = $this->context->getStringWidth(substr($this->text, $i+ 1, 1));
					$width = $thisAndNextWidth - $justNextWidth;
					
					$totalWidth += $width;
					$this->charWidths[$i] = $width;
				}
			}
			else
			{
				$width = $this->context->getStringWidth(substr($this->text, $i, 1));	//	would it be better to use $this->text{$i} here?
				$totalWidth += $width;
				$this->charWidths[$i] = $width;
			}
		}
		
		$this->totalWidth = $totalWidth;
	}
	
	function getLength()
	{
		return strlen($this->text);
	}
	
	function isBreakChar($inChar)
	{
		$breakChars = array(' ' , '-');
		if(in_array($inChar, $breakChars))
			return 1;
		else
			return 0;
	}
	
	//	this function tell us how much of the string will fit onto the current line
	function getFitsLength($startPos, $remainingWidth)
	{
		$lastGoodPos = -1;
		$width = 0;
		$textLen = strlen($this->text);
		for($i = $startPos; $i < $textLen; $i++)
		{
			$charWidth = $this->charWidths[$i];
			
			if($width + $charWidth > $remainingWidth)
			{
				if($lastGoodPos == -1)
					return $i - $startPos;
				else
					return $lastGoodPos - $startPos + 1;
			}
			
			if($this->isBreakChar($this->text{$i}))
			{
				$lastGoodPos = $i;
			}
			
			$width += $charWidth;
		}
		
		return $textLen - $startPos;
	}
	
	function addLeftSpace()
	{
		$this->text = ' ' . $this->text;
	}
	
	function getLeftSpace()
	{
		return $this->leftSpace;
	}
	
	function getRightSpace()
	{
		return $this->rightSpace;
	}
	
	function setStyle(&$style)
	{
		assert( is_a($style, 'GraphicTextStyle') );
		
		$this->style = $style;
	}
	
	function isInline()
	{
		return 1;
	}
	
	function getHeight()
	{
		return $this->style->getTextSize() * $this->context->getLineHeightMultiplier();
	}
	
	function draw($x, $y)
	{
		assert($this->style !== NULL);
		
		$this->context->setTextColor($this->style->color[0], $this->style->color[1], $this->style->color[2]);
		$this->context->setTextFont($this->style);
		$this->context->addText($x, $y + $this->getHeight(), $this->text);
	}

	function drawPart($x, $y, $start = 0, $length = NULL)
	{
		assert($this->style !== NULL);
		
		$height = $this->getHeight();
		switch($height)
		{
			case 14:
				$tweak = $height * 0.12;
				break;
			default:
				$tweak = 0;
				break;
		}
		
		$y = $y - $tweak;
		
		if($length == NULL)
			$string = substr($this->text, $start);
		else
			$string = substr($this->text, $start, $length);
		
		$this->context->setTextColor($this->style->color[0], $this->style->color[1], $this->style->color[2]);
		$this->context->setTextFont($this->style);
		$this->context->addText($x, $y + $this->getHeight(), $string);
	}
	
	function __toString()
	{
		$s = "<" . get_class($this) . ">";
		$s .= '~' . $this->text . '~';
		$s .= "</" . get_class($this) . ">";
		return $s;
	}
}
?>