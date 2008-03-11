<?
class SmartPdf extends SmartGraphic
{
	var $pageNumberStartPage;
	var $pageNumberLeft;
	var $pageNumberTop;
	var $margins;
	
	function SmartPdf($createContext = 1, $dimensions = 'tall')
	{
		$this->SmartGraphic();
		
		if($createContext)
		{
			$context = &new FPdfContext($dimensions);
			$this->setContext($context);
		}
	}
	
	function setSideMargin($side, $width)
	{
		$this->margins[$side] = $width;
	}
	
	function numberPages($startPage, $left, $top)
	{
		$this->pageNumberStartPage = $startPage;
		$this->pageNumberLeft = $left;
		$this->pageNumberTop = $top;
	}
	
	function initRootContainer(&$rootContainer)
	{
		//	this is really something that you should be able to set in the xml file
		//$rootContainer->setMargin(72);
		if(!isset($this->margins))
			$rootContainer->setMargin(36);
		else
		{
			foreach($this->margins as $side => $width)
				$rootContainer->setSideMargin($side, $width);
		}
		
		if($this->pageNumberStartPage)
		{
			$rootContainer->numberPages($this->pageNumberStartPage, $this->pageNumberLeft, $this->pageNumberTop);
		}
	}
}
?>
