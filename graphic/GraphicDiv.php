<?
$debug = 0;
class GraphicDiv extends GraphicObject
{
	var $children;
	//var $margin;
	var $margins;
	var $width;
	var $alignment;
	var $valign;
	var $lineSpacing;
//	var $pageBreakable;
	var $onFirstPage;
	var $startLine;
	var $lines;
	var $left;
	var $bgColor;
	var $border;				//	this should really be border width
	var $borderColor;
	var $givenHeight;
	var $header;
	var $contentHeightCache;
	var $breakable;
	var $tempHeight;
	var $topIndent;
	var $bottomIndent;
	var $brokenIntoLines;
	var $staticsDrawn, $redrawStatics;
	
	function GraphicDiv(&$context)
	{
		$this->GraphicObject($context);
		$this->children = array();
		$this->alignment = 'left';
		$this->lineSpacing = 0;
		$this->margins = array('all' => 0);
//		$this->pageBreakable = 0;
		$this->startLine = 0;
		$this->border = 0;
		$this->setBorderColor('#000000');
		$this->contentHeightCache = array();
		$this->breakable = 1;
		$this->brokenIntoLines = 0;
		$this->staticsDrawn = 0;
		$this->redrawStatics = 0;
	}
	
	function getUnwrappedWidth()
	{
		$this->breakIntoHardBrokenLines();
		
		$max = 0;
		foreach(array_keys($this->lines) as $lineKey)
		{
			$thisLine = &$this->lines[$lineKey];
			$thisLineWidth = $thisLine->getUnwrappedWidth();
			if($thisLine->getUnwrappedWidth() > $max)
				$max = $thisLineWidth;
		}
		
		return $max;
	}
	
	function redrawStatics()
	{
		$this->redrawStatics = 1;
	}
	function setBottomIndent($indent)
	{
		$this->bottomIndent = $indent;
	}
	
	function setTempHeight($tempHeight)
	{
		$this->tempHeight = $tempHeight;
	}
	
	function clearTempHeight()
	{
		$this->tempHeight = NULL;
	}
	
	function setBreakable($breakable)
	{
		$this->breakable = $breakable;
	}
	
	function isBreakable()
	{
		return $this->breakable;
	}
	
	function setBorderColor($color)
	{
		$rbg = HexToRgb($color);
		$this->context->addColor($color, $rbg[0], $rbg[1], $rbg[2]);
		$this->borderColor = $color;
	}
	
	function setBgColor($color)
	{
		if($color[0] == '#')
		{
			$rbg = HexToRgb($color);
			$this->context->addColor($color, $rbg[0], $rbg[1], $rbg[2]);
			$this->bgColor = $color;
			return;
		}
		
		//	this way of doing things should be depricated
		assert( substr($color, 0, 2) == '0x' );
		
		//	set the line color
		$r = (integer)hexdec(substr($color, 2, 2));
		$g = (integer)hexdec(substr($color, 4, 2));
		$b = (integer)hexdec(substr($color, 6, 2));
//		echo "$r $g $b";
		$this->context->addColor($color, $r, $g, $b);

		$this->bgColor = $color;
	}
	
	function getBgColor()
	{
		return $this->bgColor;
	}
	
	function getBorderColor($side)
	{
		if(is_array($this->borderColor))
			return $this->borderColor[$side];
		return $this->borderColor;
	}
	
	/*
	function setPageBreakable($pageBreakable)
	{
		$this->pageBreakable = $pageBreakable;
		$this->onFirstPage = 1;
	}
	*/
	
	function setLineSpacing($lineSpacing)
	{
		$this->lineSpacing = $lineSpacing;
	}
	
	function setGivenHeight($givenHeight)
	{
		$this->givenHeight = $givenHeight;
	}
	
	function getVAlign()
	{
		if(!$this->valign)
			return 'top';
		
		return $this->valign;
	}
	
	function setVAlign($valign)
	{
		assert(in_array($valign, array('top', 'bottom', 'middle')));
		
		if($valign == 'bottom')
			trigger_error('not yet implemented');
		
		$this->valign = $valign;
	}
	
	function getGivenHeight()
	{
		if($this->tempHeight)
			return $this->tempHeight;
		
		return $this->givenHeight;
	}
	
	function setMargin($margin)
	{
		$this->margins['all'] = $margin;
	}
	
	function getMargin()
	{
		return $this->margins['all'];
	}
	
	function getSideMargin($side)
	{
		if(isset($this->margins[$side]))
			return $this->margins[$side];
		else
			return $this->margins['all'];
	}
	
	function setSideMargin($side, $margin)
	{
		$this->margins[$side] = $margin;
	}
	
	function setBorder($border)
	{
		$this->border = (float)$border;
	}
	
	function getBorder()
	{
		return $this->border;
	}
	
	function setSideBorder($side, $width)
	{
		if(gettype($this->border) != 'array')
			$this->border = array('left' => $this->border, 'right' => $this->border,
									'bottom' => $this->border, 'top' => $this->border);
		if(!is_numeric($width))
		{
			$width = explode(' ', $width);
			$color = $width[0];
			$width = (float)$width[1];
			$this->setSideBorderColor($side, $color);
		}
		$this->border[$side] = (float)$width;
	}
	
	function setSideBorderColor($side, $color)
	{
		$rgb = HexToRgb($color);
		$this->context->addColor($color, $rgb[0], $rgb[1], $rgb[2]);
		if(!is_array($this->borderColor))
			$this->borderColor = array('top' => $this->borderColor, 'bottom' => $this->borderColor, 'left' => $this->borderColor, 'right' => $this->borderColor);
		$this->borderColor[$side] = $color;
	}
	
	function getSideBorder($side)
	{
		if(gettype($this->border) != 'array')
			return $this->border;
		
		return $this->border[$side];
	}
	
	function getWidth()
	{
		if( $this->width !== NULL)
			$width = $this->width;
		else
			$width = $this->parent->getWidth();
		
		return $width;
	}
	
	function getContentWidth()
	{
		return $this->getWidth() - $this->getSideMargin('left') - $this->getSideMargin('right')
						- $this->getSideBorder('left') - $this->getSideBorder('right');
	}
	
	function setLeft($left)
	{
		$this->left = $left;
	}
		
	function setAlignment($alignment)
	{
		$this->alignment = $alignment;
	}
	
	function getAlignment()
	{
		return $this->alignment;
	}
	
	function addChild(&$newChild)
	{
		assert( is_a($newChild, 'GraphicObject') );
		
		if($this->repeatable)
			$newChild->makeRepeatable();
		
		$this->children[] = &$newChild;
	}
	
	function addChildObject(&$newChild)
	{
		assert( is_a($newChild, 'GraphicObject') );
		$newChild->setParent($this);
		$this->children[] = &$newChild;
	}
	
	
	//	the getNewXXX functions
	//	they could possibly get consolidated into one function with a paramater
	//	or at least most of the logic probably could
	
	function &getNewRectangle()
	{
		$object = &new GraphicRectangle($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;
	}
	
	function &getNewImage()
	{
		$object = &new GraphicImage($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;
	}
	
	function &getNewTextRun()
	{
		$object = &new GraphicTextRun($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;
	}
	
	function &getNewDiv()
	{
		$object = &new GraphicDiv($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;		
	}
	
	function &getHeader()
	{
		$this->header = &new GraphicDiv($this->context);
		$this->header->setParent($this);
		$this->header->makeRepeatable();
		return $this->header;		
	}
	
	function &getNewColumnSet()
	{
		$object = &new GraphicColumnSet($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;		
	}
	
	function &getNewLine()
	{
		$object = &new GraphicLine($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;		
	}
	
	function &getNewRect()
	{
		$object = &new GraphicRectangle($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;		
	}
	
	function &getNewText()
	{
		$object = &new GraphicText($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;		
	}
	
	function &getNewPageBreak()
	{
		$object = &new GraphicPageBreak($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;		
	}
	
	function &getNewRaw()
	{
		$object = &new GraphicRaw($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;		
	}
	
	function &getNewList()
	{
		$object = &new GraphicList($this->context);
		$object->setParent($this);
		
		if( is_a($this, 'GraphicListItem') )
		{
			$parentList = $this->getParent();
			$object->setNestLevel( $parentList->getNestLevel() + 1 );
		}
		
		$this->addChild($object);
		return $object;
	}
	
	function &getNewTable()
	{
		$object = &new GraphicTable($this->context);
		$object->setParent($this);
		$this->addChild($object);
		return $object;
	}
	
	function doneDrawing()
	{
		if($this->lines === NULL)
			return false;
		
		return $this->startLine < count($this->lines) ? 0 : 1;
	}
		
	function getMinHeight($width)
	{
		//	if it is not breakable then just return the full height
		if($this->isBreakable())
		{
			if(count($this->lines) == 0)
				$minHeight = 0;
			else
			{
				assert(!$this->doneDrawing());
				$keys = array_keys($this->lines);
				$minHeight = $this->lines[$keys[$this->startLine]]->getMinHeight($width);		
			}
		}
		else
		{
			$minHeight = $this->getHeight($width);
		}
		
		$document = $this->getAncestor('GraphicDocument');
		
		if( $minHeight > $document->getPageContentHeight() )
		{
			trigger_error("line can't fit on a single page");
			die();
		}
		
		return $minHeight;
	}
	
	
	function getContentHeight($width)
	{
		return $this->drawContent(0, 0, $width, 0);
	}
	
	function drawContent($x, $y, $width, $reallyDraw = 1)
	{
		$cury = $y;
		
		//	loop through each hard broken line and draw it
		for($lineKey = $this->startLine; $lineKey < count($this->lines); $lineKey++)
		{
			//	get a reference to the current line
			$thisLine = &$this->lines[$lineKey];
			
			//	we want to take the smaller of the passed in width and the content width
			$cw = $this->getContentWidth();
			$contentWidth = $width < $cw ? $width : $cw;
			
			//	check to see if drawing this line is going to put us past the end of the page
			//	if $this is not breakable then we already know that the whole thing should fit
			//	so we don't need to do this check
			if($this->isBreakable() && $reallyDraw)
			{
				$minHeight = $thisLine->getMinHeight($contentWidth);
				
				$document = $this->getAncestor('GraphicDocument');
				
				if($document)
				{
					if( $cury + $minHeight > $document->getPageBottom() )
						break;
				}
			}
			
			//	the line inherits the alignment of the div
			$thisLine->setAlignment($this->getAlignment());
			
			//	actually draw the line
//			echo "drawing hard broken line $reallyDraw<br>";
			$cury += $thisLine->draw($x, $cury, $contentWidth, $reallyDraw);
			
			//	if we drew part but not all then we need to leave the loop now to make sure we don't move past it
			if($this->isBreakable() && $reallyDraw && !$thisLine->doneDrawing())
				break;
			
			if($thisLine->forcePageBreak())
			{
				$lineKey++;
				break;
			}
		}
		
		//	update the pointer to the current line
		if($reallyDraw && !$this->repeatable)
			$this->startLine = $lineKey;
		
//		echo_backtrace();
//		echo get_class($this) . ' start line = ' . $this->startLine . '<Br>';
		
		return $cury - $y;
	}
	
	function breakIntoHardBrokenLines()
	{
		if($this->brokenIntoLines)
			return;
		
		$this->lines = array();
		$curLine = 0;
		$firstItem = 1;
		$previousItemHadRightSpace = 0;
		$previousItemWasInline = 0;

		if(count($this->children) > 0)
		{
			$this->lines[$curLine] = &new GraphicHardBrokenLine();
			$this->lines[$curLine]->setLineSpacing($this->lineSpacing);
			if($this->bottomIndent)
				$this->lines[$curLine]->setBottomIndent($this->bottomIndent);
		}

		//
		//	loop through all of the children.  If they are dynamically positioned then
		//	form them into hard broken lines. 
		//
//		echo_r(array_keys($this->children));
		foreach(array_keys($this->children) as $childKey)
		{
//			echo "key = $childKey<br>";
			//	get a reference to this child
			$thisChild = &$this->children[$childKey];
			
			//	if it's absolute positioned then we don't want to add it to a line
			if($thisChild->getPosition() == 'container')
				continue;
			
			//	if this item is not inline or the previous item was not inline 
			//		and this is not the very first item then we need to go to the next line
			if((!$thisChild->isInline() || !$previousItemWasInline) && !$firstItem)
			{
				$curLine++;
				$this->lines[$curLine] = &new GraphicHardBrokenLine();
				$this->lines[$curLine]->setLineSpacing($this->lineSpacing);
				if($this->bottomIndent)
					$this->lines[$curLine]->setBottomIndent($this->bottomIndent);
			}
			
			$this->lines[$curLine]->addMember($thisChild);
			$firstItem = 0;
			
			$previousItemWasInline = $thisChild->isInline();
		}
		
//		echo 'count = ' . count($this->lines) . '<br>';
		
		$this->brokenIntoLines = 1;
	}
	
	//	draw returns when whether or not it has finished drawing
	//	if it runs out of room then it stops, remembers where it is and returns false
	//	if it finishes it simply returns true
	function draw($x, $y, $width, $reallyDraw = 1)
	{
		global $debug;
		
		$this->breakIntoHardBrokenLines();
		
//		echo "drawing div $reallyDraw<br>";
//		echo_backtrace();		
//		echo_r(array_keys($this->children));
		if((!$this->staticsDrawn || $this->redrawStatics) && $reallyDraw)
		{
			//	loop through all of the children.  If they are not statically positioned then just draw them
			foreach(array_keys($this->children) as $childKey)
			{
				//	get a reference to this child
				$thisChild = &$this->children[$childKey];
				
//				echo get_class($thisChild) . " $reallyDraw ";
//				echo $thisChild->getPosition();
//				echo '<br>';
				
				switch($thisChild->getPosition())
				{
					case 'absolute':
						trigger_error('not yet implemented');
						continue 2;
						break;
					case 'relative':
						trigger_error('not yet implemented');
						continue 2;
						break;
					case 'container':
						if($reallyDraw)
							$thisChild->draw($x + $thisChild->getLeft(), $y + $thisChild->getTop(), $width, $reallyDraw);
						continue 2;
						break;
					case 'static':
						//	they will be rendered inline
						break;
					default:
						trigger_error('bad position input: ' . $thisChild->getPosition());
						break;
				}
			}
			
			$this->staticsDrawn = 1;
		}
		
		$cury = $y;
		
		//	draw the top border
		//	actually it shouldn't matter when we draw the border so we should do it all together
		$topBorderWidth = $this->getSideBorder('top');
		if($topBorderWidth)
		{
			if($reallyDraw)
			{
				$this->context->pushLineColor($this->getBorderColor('top'));
				//$this->context->addLine($x, $cury, $x + $width, $cury, $topBorderWidth);
				$this->context->addHorizLine($x, $x + $width, $cury, $topBorderWidth);
				$this->context->popLineColor();
			}
			
			$cury += $topBorderWidth;
		}
		
		$leftBorderWidth = $this->getSideBorder('left');
		$rightBorderWidth = $this->getSideBorder('right');
		$contentWidth = $width - ($leftBorderWidth + $rightBorderWidth + $this->getSideMargin('left') + $this->getSideMargin('right'));
		
		if($reallyDraw)
		{
			if($this->getVAlign() == 'middle')
			{
				$realHeight = $this->getHeight($width);
				$contentHeight = $this->getContentHeight($contentWidth);
			}
			else if($this->getBgColor())
			{
				$realHeight = $this->getHeight($width);
			}
		}
		
		$document = $this->getAncestor('GraphicDocument');
		if($document)
		{
			$pageBottom = $document->getPageBottom(); 
			$remaining = $pageBottom - $cury;
		}
		
		
		if(isset($realHeight))
		{
			if($realHeight < $remaining)
			{
				$bgHeight = $realHeight;
				
				$vBorder = $topBorderWidth  + $this->getSideBorder('bottom');
				if($vBorder)
					$bgHeight -= $vBorder;
			}
			else
			{
				$bgHeight = $remaining;
			}
		}
		
		//	draw the background
		if( $this->getBgColor() && $reallyDraw)
		{
			$this->context->setCurFillColor($this->getBgColor());
			
			$bgWidth = $width;
			$hBorder = $this->getSideBorder('left') + $this->getSideBorder('right');
			if($hBorder)
			{
				$bgWidth -= $hBorder;
			}
			
			$bgLeft = $x + $this->getSideBorder('left');
			
			$this->context->addRect($bgLeft, $cury, $bgWidth, $bgHeight, 'F');
		}
		
		$cury += $this->getSideMargin('top');
		
		if( $this->width === NULL)
			$this->width = $width;
		
		
		//
		//	draw the content of the div
		//
		
		$bottomBorderWidth = $this->getSideBorder('bottom');
		
		if($reallyDraw)
		{
			if($this->getVAlign() == 'middle')
			{
				$topFluff = $topBorderWidth + $this->getSideMargin('top');
				$fluff = $topFluff + $bottomBorderWidth + $this->getSideMargin('bottom');
//				if($this->getGivenHeight())
//					$actualHeight = $this->getGivenHeight();
//				else
//					$actualHeight = $bgHeight;
//				$contextVSpace = $actualHeight - $fluff;
				$contextVSpace = $bgHeight - $fluff;
				$contenty = $y + $topFluff + (($contextVSpace - $contentHeight) / 2);
			}
			else
			{
				$contenty = $cury;
			}
		}
		else
		{
			$contenty = $cury;
		}
		
		$contentx = $x + $leftBorderWidth + $this->getSideMargin('left');
		
		$cury += $this->drawContent($contentx, $contenty, $contentWidth, $reallyDraw);
		
		$cury += $this->getSideMargin('bottom');
		
		//	make sure we are at least as tall as the givenHeight
		if($this->getGivenHeight())
		{
			$soFar = $cury - $y;
			if( $soFar + $bottomBorderWidth < $this->getGivenHeight() )
				$cury = $y + $this->getGivenHeight() - $bottomBorderWidth;
		}
		
		//	this is kind of a hack but it will have to do for now
		if(isset($pageBottom))
			$cury = $cury < $pageBottom ? $cury : $pageBottom;
		
		//
		//	draw the rest of the borders
		//
		
		if($bottomBorderWidth)
		{
			if($reallyDraw)
			{
				//$this->context->addLine($x, $cury, $x + $width, $cury, $bottomBorderWidth);
				$this->context->pushLineColor($this->getBorderColor('bottom'));
				$this->context->addHorizLine($x, $x + $width, $cury, $bottomBorderWidth);
				$this->context->popLineColor();
			}
			
			$cury += $bottomBorderWidth;
		}
		
		$leftBorderWidth = $this->getSideBorder('left');
		if($leftBorderWidth)
		{
			if($reallyDraw)
			{
				//$this->context->addLine($x, $y, $x, $cury, $leftBorderWidth);
				$this->context->pushLineColor($this->getBorderColor('left'));
				$this->context->addVertLine($y, $cury, $x, $leftBorderWidth);
				$this->context->popLineColor();
			}
		}
		
		$rightBorderWidth = $this->getSideBorder('right');
		if($rightBorderWidth)
		{
			if($reallyDraw)
			{
				//$this->context->addLine($x + $width, $y, $x + $width, $cury, $rightBorderWidth);
				$this->context->pushLineColor($this->getBorderColor('right'));
				$this->context->addVertLine($y, $cury, $x + $width - $leftBorderWidth, $leftBorderWidth);
				$this->context->popLineColor();
			}
		}
		
		if($debug && $reallyDraw) echo get_class($this) . ' end draw<br>';
		
		/*
		if($reallyDraw)
			echo 'really ' . $cury - $y . '<br>';
		else
			echo 'really ' . $cury - $y . '<br>';
		*/
		
		//echo "graphicDiv::draw ; class = " . get_class($this) . " reallyDraw = $reallyDraw ;  end ; " . ($cury - $y) . "<br>";
		
//		if($cury - $y == 0)
//			echo_backtrace();
		
		return $cury - $y;
	}
	
	function __toString()
	{
		$s = "<GraphicDiv>\n";
		foreach(array_keys($this->children) as $childKey)
		{
			$s .= "\n";
			//	get a reference to this child
			$thisChild = &$this->children[$childKey];
			$s .= (string)$thisChild->__toString() . "\n";
		}
		$s .= "</GraphicDiv>\n";
		
		return $s;
	}
}
?>
