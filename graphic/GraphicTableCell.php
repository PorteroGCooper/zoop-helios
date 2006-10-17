<?
class GraphicTableCell extends GraphicDiv
{
	var $colSpan;
	
	function GraphicTableCell(&$context)
	{
		$this->GraphicDiv($context);
		$this->colSpan = 1;
	}
	
	function isBreakable()
	{
		return 1;
	}
	
	function getAlignment()
	{
		if($this->alignment)
			return $this->alignment;
		
		return $this->parent->parent->getAlignment();
	}
	
	function hasWidth()
	{
		return isset($this->width) ? 1 : 0;
	}
	
	function getBorder()
	{
		return 0; //$this->parent->parent->getBorder();
	}
	
	function setColSpan($colSpan)
	{
		$this->colSpan = $colSpan;
	}
	
	function getColSpan()
	{
		return $this->colSpan;
	}
}

?>