<?
class GraphicRaw extends GraphicObject
{
	var $pdfData;
	
	function GraphicRaw(&$context)
	{
		$this->GraphicObject($context);
		$this->setPosition('container');
	}
	
	function setPdfData($data)
	{
		$this->pdfData = $data;
	}
	
	function draw($x, $y, $width, $reallyDraw)
	{
		if($reallyDraw)
		{
			$this->context->addRaw(base64_decode($this->pdfData));
		}
		
		return 0;
	}
}

?>