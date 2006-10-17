<?
class GraphicRosPdfEngine extends CPdf
{
	function GraphicRosPdfEngine($bounds)
	{
		$this->CPdf($bounds);
	}
	
	function setCurrentFont()
	{
		if(!$this->currentBaseFont)
			trigger_error("Base font not set.");
		parent::setCurrentFont();	
	}
	
	function raw($rawPdfData)
	{
		$this->objects[$this->currentContents]['c'] .= $rawPdfData;
	}

	function setStrokeColor($r,$g,$b,$force=0)
	{
		parent::setStrokeColor($r,$g,$b,$force);
		$this->objects[$this->currentContents]['c'] .= " ";
	}
}
?>