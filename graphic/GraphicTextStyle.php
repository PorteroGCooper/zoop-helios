<?
class GraphicTextStyle
{
	var $font;
	var $textSize;
	var $underline;
	var $bold;
	var $italics;
	var $color;
	
	function GraphicTextStyle()
	{
		//	these are the default styles for any document
		//	if we want them to be defined elsewhere either make
		//	them constants for mandatory constructor paramaters
		$this->font = 'Arial';
		$this->textSize = 10;
		$this->underline = 0;
		$this->bold = 0;
		$this->italics = 0;
		$this->color = array(0, 0, 0);
	}
	
	function setUnderline($underline)
	{
		$this->underline = $underline;
	}
	
	function getUnderline()
	{
		return $this->underline;
	}
	
	function setBold($bold)
	{
		$this->bold = $bold;
	}
	
	function getBold()
	{
		return $this->bold;
	}
	
	function setItalics($italics)
	{
		$this->italics = $italics;
	}
	
	function getItalics()
	{
		return $this->italics;
	}
	
	function setTextSize($textSize)
	{
		$this->textSize = $textSize;
	}
	
	function getTextSize()
	{
		return $this->textSize;
	}
	
	/*
	function setColor($r, $g, $b)
	{
		$this->color[0] = $r;
		$this->color[1] = $g;
		$this->color[2] = $b;
	}
	*/
	
	function getFont()
	{
		return $this->font;
	}
	
	function addStyles($styleInfo)
	{
		foreach($styleInfo as $name => $value)
		{
			switch($name)
			{
				case 'font-size':
					sscanf($value, "%f%s", $amount, $units);
					
					switch($units)
					{
						case 'pt':
							$finalAmount = $amount;
							break;
						case 'px':
							$finalAmount = $amount * 0.75;
							break;
						default:
							trigger_error("unhandled unit type: $units");
							break;
					}
					
					$this->setTextSize($finalAmount);
					break;
				case 'font-family':
					switch(strtolower($value))
					{
						case 'verdana':
							$this->font = 'Verdana';
							break;
						case 'arial':
							$this->font = 'Arial';
							break;
						default:
							break;
					}
					break;
				case 'font-weight':
					switch(strtolower($value))
					{
						case 'bold':
							$this->setBold(1);
							break;
						default:
							break;
					}
					break;
				case 'color':
					assert($value[0] == '#');
					
					$rgb = HexToRgb($value);
					$this->color = $rgb;
					break;
				default:
					break;
			}
		}
	}
}
?>