<?
class GraphicDocument extends GraphicDiv
{
	var $width;
	var $height;
	var $pageNumberStartPage;
	var $pageNumberLeft;
	var $pageNumberTop;
	var $pageNumber;
	
	function GraphicDocument(&$context)
	{
		$this->GraphicDiv($context);
		$this->width = $context->getPageWidth();
		$this->height = $context->getPageHeight();
		$this->pageNumber = 1;
	}
	
	function numberPages($startPage, $left, $top)
	{
		$this->pageNumberStartPage = $startPage;
		$this->pageNumberLeft = $left;
		$this->pageNumberTop = $top;
	}
	
	//	draw uses the parent class to draw everything that can
	//		be drawn on this page, then breaks the page and 
	//		repeats until everything is done drawing

	function draw($x, $y, $width = NULL, $reallyDraw = 1)
	{
		$pageNum = 1;
		while(!$this->doneDrawing())
		{
			if($pageNum > 1)
				$this->breakPage();
			
			// echo "new page $pageNum<br>";
			
//			echo "new page!<br><br>";
			/*
			//	draw a grid for debugging purposes
			for($lx = 0; $lx < 700; $lx += 10)
			{
				for($ly = 0; $ly < 800; $ly += 10)
				{
					$this->context->addLine($lx, $ly, $lx + 0.1, $ly + 0.1);
				}
			}
			*/
			
			$this->pageNumber = $pageNum;
			// echo "setting page number = " . $this->pageNumber . '<br>';
			
			GraphicDiv::draw($x, $y, $this->width, $reallyDraw);
//			echo 'document start line ' . $this->startLine . '<br>';
//			echo $this->doneDrawing();
//			die();
			
//			if($pageNum > 2)
//				break;
			
			if($this->pageNumberStartPage && $pageNum >= $this->pageNumberStartPage)
			{
				$pageNumberText = (string)($pageNum - $this->pageNumberStartPage + 1);
				$style = new GraphicTextStyle();
				$this->context->setTextColor($style->color[0], $style->color[1], $style->color[2]);
				$this->context->setTextFont($style);				
				$this->context->addText($this->pageNumberLeft, $this->pageNumberTop, $pageNumberText);
			}
			
			$pageNum++;
			
		}
	}
	
	function getPageNumber()
	{
		// echo "getting page number = " . $this->pageNumber . '<br>';
		return $this->pageNumber;
	}
	
	function breakPage()
	{
		$this->context->breakPage();
	}
	
	function getPageBottom()
	{
		return $this->context->getPageHeight() - $this->getSideMargin('bottom');
		
	}
	
	function getPageContentHeight()
	{
		return $this->context->getPageHeight() - $this->getSideMargin('bottom') - $this->getSideMargin('top');
		
	}
}
?>