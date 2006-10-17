<?
class SmartImage extends SmartGraphic
{
	function SmartImage($params)
	{
		$this->SmartGraphic();
		$this->image = new ImageContext($params['width'], $params['height']);
		SmartGraphic::setContext($this->image);
	}
}
?>