<?
class GraphicContext
{
	//	general stuff
	var $width;
	var $height;
	
	//	the font stuff
	var $textSize;
	var $textStyle;
	var $textFontName;
	
	//	the color stuff
	var $colors;
	var $curLineColor;
	var $lineColorStack;
	var $curFillColor;
	var $curTextColor;
    
	function GraphicContext($width, $height)
	{
		//	general stuff
		$this->width = $width;
		$this->height = $height;
		
		//	the font stuff
		$this->textSize = 10;
		$this->textStyle = '';
		$this->textFontName = 'Arial';
		
		//	the color stuff
		$this->colors = array();
		$this->addColor('black', 0, 0, 0);
		$this->addColor('gray', 128, 128, 128);
		$this->addColor('white', 255, 255, 255);
		$this->setCurLineColor('black');
		$this->setCurFillColor('gray');
		$this->setCurTextColor('black');
		$this->lineColorStack = array();
	}
	
	//	it just so happens these are always implemented the same way so far
	function setColor($name, $r, $g, $b)
	{
		$this->addColor($name, $r, $g, $b);
	}	
	
	function getWidth()
	{
		return $this->width;
	}
	
	function getHeight()
	{
		return $this->height;
	}
	
	function getPageHeight()
	{
		return $this->getHeight();
	}
	
	function _getCurTextColor()
	{
		return $this->colors[$this->curTextColor];
	}
	
	function _getCurLineColor()
	{
		return $this->colors[$this->curLineColor];
	}
	
	function _getCurFillColor()
	{
		return $this->colors[$this->curFillColor];
	}
	
	function setCurLineColor($color)
	{
		$this->curLineColor = $color;
	}
	
	function setCurFillColor($color)
	{
		$this->curFillColor = $color;
	}
	
	function setCurTextColor($color)
	{
		$this->curTextColor = $color;
	}
	
	function pushLineColor($color)
	{
		array_push($this->lineColorStack, $this->curLineColor);
		$this->setCurLineColor($color);
	}
	
	function popLineColor()
	{
		//	is this right?  shouldn't we be setting it to what is on the top AFTER we pop the stack
		assert(count($this->lineColorStack) > 0);
		$this->setCurLineColor(array_pop($this->lineColorStack));
	}
	
	function getPageWidth()
	{
		return $this->width;
	}
	
	function getTextSize()
	{
		return $this->textSize;
	}
	
	function setTextSize($size)
	{
		$this->textSize = $size;
	}
	
	function getTextStyle()
	{
		return $this->textStyle;
	}
	
	function setTextStyle($style)
	{
		$this->textStyle = $style;
	}
	
	function getTextFontName()
	{
		return $this->textFontName;
	}
	
	function setTextFontName($font)
	{
		$this->textFontName = $font;
	}
	
	function useKerningHack()
	{
		return 0;
	}
	
	function getLineHeightMultiplier()
	{
		return 1;
	}
}
?>