<?
class GraphicTextStyleStack
{
	var $styles;
	
	function GraphicTextStyleStack()
	{
		$this->styles = array();
		$this->getNewTopStyle();
	}
	
	
	//	this function makes a copy of the style currently on the top of the stack
	//		pushes the copy onto the top of the stack and returns the new top of the stack
	
	function &getNewTopStyle()
	{
		//	this will make a copy of the current top style in the stack
		//		to make this work in php5 you will need to explicitly clone the object
		$numStyles = count($this->styles);
		if($numStyles == 0)
			$newTopStyle = &new GraphicTextStyle();
		else
			$newTopStyle = CloneObject($this->styles[ $numStyles - 1 ]);
		
		//	now just put the new one onto the top of the stack
		$this->styles[] = &$newTopStyle;
		
		return $newTopStyle;
	}
	
	
	//	this just returns the top style as is
	
	function &getTopStyle()
	{
		return $this->styles[ count($this->styles) - 1 ];
	}
	
	//	this function takes the top style on the stack and pops it off
	
	function popTopStyle()
	{
		array_pop($this->styles);
	}
}
?>