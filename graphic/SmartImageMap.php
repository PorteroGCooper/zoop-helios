<?
class SmartImageMap extends SmartGraphic
{
	function SmartImageMap($params)
	{
		$this->SmartGraphic();
		$this->imageMap = new ImageMapContext($params['width'], $params['height']);
		SmartGraphic::setContext($this->imageMap);
	}
	
	function getMap()
	{
		return $this->context->getMap();
	}
}
?>