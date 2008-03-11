<?php
class SmartGraphic
{
	var $smarty;
	var $context;
	var $divParsers;
	var $parsers;
	var $colors;
	var $repeatStatics;
	
	function SmartGraphic()
	{
		$this->smarty = new gui();
		$this->divParsers = array();
		$this->parsers = array();
		$this->colors = array('white' => '#FFFFFF', 'black' => '#000000');
		$this->repeatStatics = 0;
	}
	
	function setSmarty(&$smarty)
	{
		$this->smarty = &$smarty;
	}
	
	function setContext(&$inContext)
	{
		$this->context = &$inContext;
	}
	
	function assign($name, $value = null)
	{
		$this->smarty->assign($name, $value);
	}
	
	function repeatStatics()
	{
		$this->repeatStatics = 1;
	}
	
	function preProcessText($text)
	{
		return str_replace(chr(8), '', $text);
	}
	
	function display($tplFile, $reallyDisplay = 1)
	{
		$xml = $this->smarty->fetch($tplFile);
		$xml = $this->preProcessText($xml);
		
		$xml = '<xml><content>' . $xml . '</content></xml>';
		// header("Content-type: text/xml");
 		// echo_r($xml);
		// echo_r(htmlentities($xml));
		// die();
		
		$dom = new XmlDom();
		$rootNode = $dom->parseText($xml);
//		XmlDisplayNode($rootNode);
//		die();
		$children = $rootNode->getChildren();
		$rootNode = $children->current();
// 		echo_r($rootNode);
// 		die();
		
		$styleStack = &new GraphicTextStyleStack();
		$rootContainer = new GraphicDocument($this->context);
		
		if($this->repeatStatics)
			$rootContainer->redrawStatics();

//		echo_r($rootContainer->context);
		$this->initRootContainer($rootContainer);
		
		$this->handleDocument($rootNode, $styleStack, $rootContainer);
		
//		$rootContainer->context->pdf = 5;
//		$rootContainer->context->fpdf = 5;
//		$rootContainer->children[0]->context = 5;		
//		echo_r($rootContainer);
//		die();
//		echo nl2br(htmlentities($rootContainer->__toString()));
//		die();
		
//		echo_r($this->context->mapItems);
		$rootContainer->draw(0, 0);
//		echo_r($this->context->mapItems);
		
		/*
		$children = $rootNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		//foreach($rootNode->getChildren() as $thisChild)
		{
			print_r($thisChild->name);
		}
//		print_r($rootNode);
		*/
		
		if($reallyDisplay)
			$this->context->display();
	}
	
	function save($tplFile, $filename)
	{
		$xml = $this->smarty->fetch($tplFile);

		$xml = $this->preProcessText($xml);

		$xml = '<xml><content>' . $xml . '</content></xml>';

		$dom = new XmlDom();
		$rootNode = $dom->parseText($xml);
		$children = $rootNode->getChildren();
		$rootNode = $children->current();

		$styleStack = &new GraphicTextStyleStack();
		$rootContainer = new GraphicDocument($this->context);

		$this->initRootContainer($rootContainer);

		$this->handleDocument($rootNode, $styleStack, $rootContainer);

		$rootContainer->draw(0, 0);
		$this->context->save($filename);
	}

	function initRootContainer(&$rootContainer)
	{
		//	default: do nothing
	}
	
	function isStyleTag($tagName)
	{
		$tagName = strtolower($tagName);
		
		$styleTagNames = array('font', 'b', 'u', 'i', 'em', 'strong', 'span');
		if( in_array($tagName, $styleTagNames) )
			return 1;
		else
			return 0;
	}
	
	function isDivTag($tagName)
	{
//		echo "is div tag: tagName = $tagName<br>";
		$tagName = strtolower($tagName);
		
		if( isset($this->divParsers[$tagName]) )
			return 1;
		
		$divTagNames = array('p', 'div', 'blockquote', 'br', 'columnset');
		if( in_array($tagName, $divTagNames) )
			return 1;
		else
			return 0;
	}
	
	function isPageBreakTag($tagName)
	{
		$tagName = strtolower($tagName);
		$listTagNames = array('pb');
		if( in_array($tagName, $listTagNames) )
			return 1;
		else
			return 0;
	}
	
	function isListTag($tagName)
	{
		$tagName = strtolower($tagName);
		$listTagNames = array('ul', 'ol');
		if( in_array($tagName, $listTagNames) )
			return 1;
		else
			return 0;
	}

	function isListItemTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'li')
			return 1;
		else
			return 0;
	}
	
	function isTableTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'table')
			return 1;
		else
			return 0;
	}
	
	function isTableHeaderTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'th')
			return 1;
		else
			return 0;
	}
	
	function isTableRowTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'tr')
			return 1;
		else
			return 0;
	}
	
	function isTableCellTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'td' || $tagName == 'th')
			return 1;
		else
			return 0;
	}
	
	function isLineTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'line')
			return 1;
		else
			return 0;
	}
	
	function isRectTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'rect')
			return 1;
		else
			return 0;
	}
	
	function isImageTag($tagName)
	{
		$tagName = strtolower($tagName);
		if($tagName == 'img')
			return 1;
		else
			return 0;
	}
	
	function isTextNode($tagName)
	{
		if($tagName == '#text')
			return 1;
		else
			return 0;
	}
	
	function addDivParser($parser)
	{
		$tagList = $parser->getTagList();
		
		if(gettype($tagList) == 'string')
		{
			$tagName = $tagList;
			$this->divParsers[strtolower($tagName)] = &$parser;
		}
		else if(gettype($tagList) == 'array')
		{
			foreach($tagList as $tagName)
			{
				$this->divParsers[strtolower($tagName)] = &$parser;
			}
		}
	}
	
	function addParser($parser)
	{
		$tagList = $parser->getTagList();
		
		if(gettype($tagList) == 'string')
		{
			$tagName = $tagList;
			$this->parsers[strtolower($tagName)] = &$parser;
		}
		else if(gettype($tagList) == 'array')
		{
			foreach($tagList as $tagName)
			{
				$this->parsers[strtolower($tagName)] = &$parser;
			}
		}
	}
	
	function handleDocument(&$curNode, &$styleStack, &$curContainer)
	{
		//	now if there is any content for this node then apply the style and add it to the container
		if($curNode->hasContent())
		{
//			echo "<b>document has content</b>";
			$topStyle = &$styleStack->getTopStyle();
			$textRun = &$curContainer->getNewTextRun();
			$textRun->setStyle($topStyle);
			$textRun->setText($this->processText($curNode->getContent()));
		}
		
		$children = $curNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			if($thisChild === false)
				break;
			
			$tagName = strtolower( $thisChild->getName() );
			
//			echo 'handleDocument ' . $tagName . '<br>';
/*
			echo $tagName . '<br>';
			echo_r($thisChild);
			echo "processing child<br>";
			if($thisChild->getName() === NULL)
				echo 'NULL<br>';
			else
				echo '&lt;' . $thisChild->getName() . '&gt;<br>';
//*/
			if( isset($this->parsers[$tagName]) )
			{
				$this->parsers[$tagName]->handle($thisChild, $styleStack, $curContainer);
			}
			else
			{
				if($thisChild->getName() === NULL || $thisChild->getName() === '#text')
					$this->handleTextTag($thisChild, $styleStack, $curContainer);
				else if($this->isPageBreakTag($thisChild->getName()))
					$this->handlePageBreakTag($thisChild, $styleStack, $curContainer);
				else if($this->isStyleTag($thisChild->getName()))
					$this->handleStyleTag($thisChild, $styleStack, $curContainer);
				else if($this->isDivTag($thisChild->getName()))
					$this->handleDivTag($thisChild, $styleStack, $curContainer);
				else if($this->isListTag($thisChild->getName()))
					$this->handleListTag($thisChild, $styleStack, $curContainer);
				else if($this->isTableTag($thisChild->getName()))
					$this->handleTableTag($thisChild, $styleStack, $curContainer);
				else if($this->isLineTag($thisChild->getName()))
					$this->handleLineTag($thisChild, $styleStack, $curContainer);
				else if($this->isRectTag($thisChild->getName()))
					$this->handleRectTag($thisChild, $styleStack, $curContainer);
				else if($this->isImageTag($thisChild->getName()))
					$this->handleImageTag($thisChild, $styleStack, $curContainer);
				else if($thisChild->getName() == 'text')
					$this->handleTextItemTag($thisChild, $styleStack, $curContainer);
				else if($thisChild->getName() == 'raw')
					$this->handleRawTag($thisChild, $styleStack, $curContainer);
				else
					$this->handleDocument($thisChild, $styleStack, $curContainer);
			}
		}
	}
	
	function handleStyleTag(&$curNode, &$styleStack, &$curContainer)
	{
		//	first update the style stack for this node and all of it's children
		$newTopStyle = &$styleStack->getNewTopStyle();
		
		switch( strtolower( $curNode->getName() ) )
		{
			case 'u':
				$newTopStyle->setUnderline(1);
				break;
			case 'strong':
			case 'b':
				$newTopStyle->setBold(1);
				break;
			case 'i':
			case 'em':
				$newTopStyle->setItalics(1);
				break;
			case 'span':
				if( $curNode->hasAttribute('style') )
				{
					$style = $curNode->getAttribute('style');
					$styleAttributes = explode(';', $style);
					$styleInfo = array();
					foreach($styleAttributes as $thisAttribute)
					{
						if(trim($thisAttribute))
						{
							$styleAttParts = explode(':', $thisAttribute);
							$styleAttName = strtolower(trim($styleAttParts[0]));
							$styleAttValue = strtolower(trim($styleAttParts[1]));
							$styleInfo[$styleAttName] = $styleAttValue;
						}
					}
					
					$newTopStyle->addStyles($styleInfo);
				}
				break;
			case 'font':
				if( $curNode->hasAttribute('face') )
				{
					$face = $curNode->getAttribute('face');
					$faceOptions = explode(',', $face);
					$styleInfo = array();
					$styleInfo['font-family'] = $faceOptions[0];
					$newTopStyle->addStyles($styleInfo);
				}
				
				if( $curNode->hasAttribute('size') )
				{
					$size = (integer)$curNode->getAttribute('size');
					switch($size)
					{
						case 1:
							$fontSize = '7.5pt';
							break;
						case 2:
							$fontSize = '10pt';
							break;
						default:
							$fontSize = (5 * $size) . 'pt';
							break;
					}
					$styleInfo['font-size'] = $fontSize;
					$newTopStyle->addStyles($styleInfo);
				}
				break;
			default:
				trigger_error('undefined style tag: ' . $curNode->getName() );
		}
		
		
		//	now if there is any content for this node then apply the style and add it to the container
		if($curNode->hasContent())
		{
			$textRun = &$curContainer->getNewTextRun();
			
			$textRun->setStyle($newTopStyle);
			$textRun->setText($this->processText($curNode->getContent()));
		}
		
		$children = $curNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			if($thisChild === false)
				break;
			
			if($this->isStyleTag($thisChild->getName()))
				$this->handleStyleTag($thisChild, $styleStack, $curContainer);
			else if($this->isDivTag($thisChild->getName()))
				$this->handleDivTag($thisChild, $styleStack, $curContainer);
			else if($this->isListTag($thisChild->getName()))
				$this->handleListTag($thisChild, $styleStack, $curContainer);
			else
				$this->handleDocument($thisChild, $styleStack, $curContainer);
		}
		
		$styleStack->popTopStyle();
	}
	
	
	//	it's not really a tag but it comes to is from the parser as one
	
	function handleTextTag(&$curNode, &$styleStack, &$curContainer)
	{
		//	now if there is any content for this node then apply the style and add it to the container
		if($curNode->hasContent())
		{
			$content = $curNode->getContent();
			if(trim($content['content']))
			{
				$topStyle = &$styleStack->getTopStyle();
				$textRun = &$curContainer->getNewTextRun();
				$textRun->setStyle($topStyle);
				//	this is bad because $curNode->getContent() returns an array not a string but processText deals with it like we passed it a string
				$textRun->setText($this->processText($curNode->getContent()));
			}
		}
	}
	
	function processText($text)
	{
		return str_replace('[nbsp]', ' ', $text);
	}
	
	function handleTableTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newTable = &$curContainer->getNewTable();
		
		if( $curNode->hasAttribute('border') )
			$newTable->setBorder( $curNode->getAttribute('border') );
		if( $curNode->hasAttribute('rowSpacing') )
			$newTable->setRowSpacing( $curNode->getAttribute('rowSpacing') );
		if( $curNode->hasAttribute('breakable') )
		{
			$newTable->setBreakable( $curNode->getAttribute('breakable') ? 1 : 0 );
		}
		
		//
		//	handle the style tag if there is one
		//
		if( $curNode->hasAttribute('style') )
		{
			$style = $curNode->getAttribute('style');
			$styleAttributes = explode(';', $style);
			$styleInfo = array();
			foreach($styleAttributes as $thisAttribute)
			{
				if($thisAttribute)
				{
					$styleAttParts = explode(':', $thisAttribute);
					$styleAttName = strtolower(trim($styleAttParts[0]));
					if( !isset($styleAttParts[1]) )
						trigger_error("style $styleAttName has no value");
					$styleAttValue = strtolower(trim($styleAttParts[1]));
					
					switch($styleAttName)
					{
						case 'text-align':
							switch($styleAttValue)
							{
								case 'left':
								case 'center':
								//case 'right':	is this implemented yet?
									$newTable->setAlignment($styleAttValue);
									break;
								default:
									trigger_error("unrecognized align type: $styleAttValue");
									break;
							}
							break;
						case 'autospace':
							switch($styleAttValue)
							{
								case 'even':
								case 'line-width':
									$newTable->setAutospaceType($styleAttValue);
									break;
								default:
									trigger_error("unrecognized autospace type: $styleAttValue");
									break;
							}
							break;
						default:
							$styleInfo[$styleAttName] = $styleAttValue;
					}
				}
			}
			
			if(count($styleInfo) > 0)
			{
				$newTopStyle = &$styleStack->getNewTopStyle();
				$newTopStyle->addStyles($styleInfo);
			}
		}
		
		$children = $curNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			if($thisChild === false)
				break;
			
			$childTagName = $thisChild->getName();
			
			//	the only thing directly inside a table should be tableRow
			
			if($this->isTableHeaderTag($childTagName))
			{
				$this->handleTableHeaderTag($thisChild, $styleStack, $newTable);
			}
			else if($this->isTableRowTag($childTagName))
			{
				$this->handleTableRowTag($thisChild, $styleStack, $newTable);
			}
			else if($thisChild->getName() !== '#text')
				trigger_error("a table can only contain table rows, not " . $thisChild->getName());
		}
		
		if( isset($styleInfo) && count($styleInfo) > 0)
			$styleStack->popTopStyle();
	}
	
	function handleTableHeaderTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newTableRow = &$curContainer->getHeader();
		$this->_handleTableRowTag($curNode, $styleStack, $newTableRow);
	}
	
	function handleTableRowTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newTableRow = &$curContainer->getNewTableRow();
		$this->_handleTableRowTag($curNode, $styleStack, $newTableRow);
	}
	
	function _handleTableRowTag(&$curNode, &$styleStack, &$newTableRow)
	{
		$children = $curNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			if($thisChild === false)
				break;
			
			//	the only thing directly inside a tableRow should be tableCell
			if($this->isTableCellTag($thisChild->getName()))
			{
				$this->handleTableCellTag($thisChild, $styleStack, $newTableRow);
			}
			else if($thisChild->getName() !== '#text')
				trigger_error("a tablerow can only contain table cells");
		}
	}
	
	function handleTableCellTag(&$curNode, &$styleStack, &$curContainer)
	{
		//	first update the style stack for this node and all of it's children
		$newTopStyle = &$styleStack->getNewTopStyle();
		
		//	now create the new table cell
		$newTableCell = &$curContainer->getNewTableCell();
		
		if( $curNode->hasAttribute('width') )
			$newTableCell->setWidth( $curNode->getAttribute('width') );
		
		if( $curNode->hasAttribute('colspan') )
			$newTableCell->setColSpan( $curNode->getAttribute('colspan') );
		
		//
		//	handle the style tag if there is one
		//
		if( $curNode->hasAttribute('style') )
		{
			$styleInfo = $this->handleDivStyles($curNode, $newTableCell);
			$newTopStyle->addStyles($styleInfo);
		}
		
		$this->handleDocument($curNode, $styleStack, $newTableCell);
		
		$styleStack->popTopStyle();
	}
		
	function handlePageBreakTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newPageBreak = &$curContainer->getNewPageBreak();		
	}
	
	function handleListTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newList = &$curContainer->getNewList();
		
		if($curNode->getName() == 'ol')
		{
			$newList->setBulletType('number');
		}
		
		if( $curNode->hasAttribute('type') )
		{
			switch($curNode->getAttribute('type'))
			{
				case 'a':
					$newList->setBulletType('lower');
					break;
				default:
					trigger_error('not implemented');
			}
		}
		
		$children = $curNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			if($thisChild === false)
				break;
			
			if($this->isListTag($thisChild->getName()))
			{
				//	if there is a list directly inside another list we assume the list item
				//	that isn't there
				
				$newList->setBulletType(NULL);
				
				$newListItem = &$newList->getNewListItem();
				
				$this->handleListTag($thisChild, $styleStack, $newListItem);
			}
			else if($this->isListItemTag($thisChild->getName()))
				$this->handleListItemTag($thisChild, $styleStack, $newList);
			else
				$this->handleDocument($thisChild, $styleStack, $curContainer);
				//$this->handleListTag($thisChild, $styleStack, $curContainer);
		}		
	}
	
	function handleListItemTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newListItem = &$curContainer->getNewListItem();
		
		$this->handleDocument($curNode, $styleStack, $newListItem);
		
		/*
		
		$children = $curNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			if($thisChild === false)
				break;
			
			if($this->isListItemTag($thisChild->getName()))
				$this->handleStyleTag($thisChild, $styleStack, $newDiv);
			else
				$this->handleDocument($thisChild, $styleStack, $newDiv);
		}
		*/
	}
	
	function handleLineTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newLine = &$curContainer->getNewLine();
		
		if( $curNode->hasAttribute('lineWidth') )
			$newLine->setLineWidth( $curNode->getAttribute('lineWidth') );
		if( $curNode->hasAttribute('color') )
			$newLine->setHexColor( $curNode->getAttribute('color') );
		if( $curNode->hasAttribute('height') )
			$newLine->setHeight( $curNode->getAttribute('height') );
		if( $curNode->hasAttribute('position') )
			$newLine->setPosition( $curNode->getAttribute('position') );
		if( $curNode->hasAttribute('left') )
			$newLine->setLeft( $curNode->getAttribute('left') );
		if( $curNode->hasAttribute('top') )
			$newLine->setTop( $curNode->getAttribute('top') );
		if( $curNode->hasAttribute('width') )
			$newLine->setWidth( $curNode->getAttribute('width') );
		if( $curNode->hasAttribute('type') )
			$newLine->setType( $curNode->getAttribute('type') );
		
		$this->handleDocument($curNode, $styleStack, $newTableCell);
	}
	
	function handleRectTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newRect = &$curContainer->getNewRect();
		
		if( $curNode->hasAttribute('position') )
			$newRect->setPosition( $curNode->getAttribute('position') );
		if( $curNode->hasAttribute('left') )
			$newRect->setLeft( $curNode->getAttribute('left') );
		if( $curNode->hasAttribute('top') )
			$newRect->setTop( $curNode->getAttribute('top') );
		if( $curNode->hasAttribute('height') )
			$newRect->setHeight( $curNode->getAttribute('height') );
		if( $curNode->hasAttribute('width') )
			$newRect->setWidth( $curNode->getAttribute('width') );
		if( $curNode->hasAttribute('color') )
			$newRect->setColor( $curNode->getAttribute('color') );
		/*
		if( $curNode->hasAttribute('lineWidth') )
			$newRect->setLineWidth( $curNode->getAttribute('lineWidth') );
		if( $curNode->hasAttribute('color') )
			$newRect->setHexColor( $curNode->getAttribute('color') );
		if( $curNode->hasAttribute('type') )
			$newRect->setType( $curNode->getAttribute('type') );
		*/
		
		$this->handleDocument($curNode, $styleStack, $newTableCell);
	}
	
	function handleImageTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newRect = &$curContainer->getNewImage();
		
		if( $curNode->hasAttribute('position') )
			$newRect->setPosition( $curNode->getAttribute('position') );
		if( $curNode->hasAttribute('left') )
			$newRect->setLeft( $curNode->getAttribute('left') );
		if( $curNode->hasAttribute('top') )
			$newRect->setTop( $curNode->getAttribute('top') );
		if( $curNode->hasAttribute('height') )
			$newRect->setHeight( $curNode->getAttribute('height') );
		if( $curNode->hasAttribute('width') )
			$newRect->setWidth( $curNode->getAttribute('width') );
		if( $curNode->hasAttribute('src') )
		{
			$src = $curNode->getAttribute('src');
			if($src[0] != '/')
				$newRect->setFile( app_dir . '/' . $src );
			else
				$newRect->setFile($src);
		}
			
		/*
		if( $curNode->hasAttribute('lineWidth') )
			$newRect->setLineWidth( $curNode->getAttribute('lineWidth') );
		if( $curNode->hasAttribute('color') )
			$newRect->setHexColor( $curNode->getAttribute('color') );
		if( $curNode->hasAttribute('type') )
			$newRect->setType( $curNode->getAttribute('type') );
		*/
		
		$this->handleDocument($curNode, $styleStack, $newTableCell);
	}
	
	function handleTextItemTag(&$curNode, &$styleStack, &$curContainer)
	{
		$newLine = &$curContainer->getNewText();
		
		if( $curNode->hasAttribute('lineWidth') )
			$newLine->setLineWidth( $curNode->getAttribute('lineWidth') );
		if( $curNode->hasAttribute('color') )
			$newLine->setHexColor( $curNode->getAttribute('color') );
		if( $curNode->hasAttribute('height') )
			$newLine->setHeight( $curNode->getAttribute('height') );
		if( $curNode->hasAttribute('position') )
			$newLine->setPosition( $curNode->getAttribute('position') );
		if( $curNode->hasAttribute('left') )
			$newLine->setLeft( $curNode->getAttribute('left') );
		if( $curNode->hasAttribute('top') )
			$newLine->setTop( $curNode->getAttribute('top') );
		if( $curNode->hasAttribute('width') )
			$newLine->setWidth( $curNode->getAttribute('width') );
		if( $curNode->hasAttribute('type') )
			$newLine->setType( $curNode->getAttribute('type') );
		if( $curNode->hasAttribute('text') )
			$newLine->setText( $curNode->getAttribute('text') );
		if( $curNode->hasAttribute('angle') )
			$newLine->setAngle( $curNode->getAttribute('angle') );
		
		$this->handleDocument($curNode, $styleStack, $newTableCell);
	}
	
	function handleRawTag(&$curNode, &$styleStack, &$curContainer)
	{
		if($curNode->hasContent())
		{
			$newRaw = &$curContainer->getNewRaw();
			$nodeContent = $curNode->getContent();
			$rawPdfData = trim($nodeContent['content']);
			//echo $rawPdfData;die();
			$newRaw->setPdfData($rawPdfData);
		}
	}
	
	function handleDivTag(&$curNode, &$styleStack, &$curContainer)
	{
		//	first update the style stack for this node and all of it's children
		$newTopStyle = &$styleStack->getNewTopStyle();
		
		//	get the correct type of new div
		$tagName = strtolower( $curNode->getName() );
//		echo "handleDivTag: $tagName<br>";
		switch($tagName)
		{
			case 'header':
				$newDiv = &$curContainer->getHeader();
				break;
			case 'columnset':
				$newDiv = &$curContainer->getNewColumnSet();
				break;
			default:
				if( isset($this->divParsers[$tagName]) )
				{
					$newDiv = &$this->divParsers[$tagName]->getNewContainer($curNode, $curContainer->getContext());
					$curContainer->addChildObject($newDiv);
				}
				else
				{
					$newDiv = &$curContainer->getNewDiv();
				}
				break;
		}
		
		//	handle cases for the specific div types
		switch( $tagName )
		{
			case 'blockquote':
				$newDiv->setSideMargin('left', 10);
			case 'p':
				//	add some padding in here
				$newDiv->setSideMargin('top', 5);
				$newDiv->setSideMargin('bottom', 5);
				
				//	read in the attributes
				if( $curNode->hasAttribute('align') )
					$newDiv->setAlignment( $curNode->getAttribute('align') );
				
				break;
			case 'br':
				$newDiv->setSideMargin('top', 5);
				break;
			default:
				break;
		}
		
		//	now handle any attributes that should be available to all div types
		if( $curNode->hasAttribute('width') )
			$newDiv->setWidth( $curNode->getAttribute('width') );
		if( $curNode->hasAttribute('position') )
			$newDiv->setPosition( $curNode->getAttribute('position') );
		if( $curNode->hasAttribute('left') )
			$newDiv->setLeft( $curNode->getAttribute('left') );
		if( $curNode->hasAttribute('top') )
			$newDiv->setTop( $curNode->getAttribute('top') );
		
		//
		//	handle the style tag if there is one
		//
		if( $curNode->hasAttribute('style') )
		{
			$styleInfo = $this->handleDivStyles($curNode, $newDiv);
			$newTopStyle->addStyles($styleInfo);
		}
		
		//	now if there is any content for this node then apply the style and add it to the container
		if($curNode->hasContent())
		{
//			echo get_class($newDiv) . '<br>';
//			echo is_a($newDiv, 'graphicdiv') ? 'is<br>' : 'isnt<br>';
			$textRun = &$newDiv->getNewTextRun();
			$textRun->setStyle($newTopStyle);
			$textRun->setText($this->processText($curNode->getContent()));
		}
		
		//	now loop through the child nodes and handle them
		$children = $curNode->getChildren();
		
		for($thisChild = $children->current(); $children->valid(); $thisChild = $children->next())
		{
			if($thisChild === false)
				break;
			
			if(strtolower($curNode->getName()) == 'columnset')
			{
				//	this should really be handled like those below
				if($thisChild->getName() == 'column')
				{
					$column = &$newDiv->getNewColumn();
					if($thisChild->hasAttribute('width'))
					{
						$column->setWidth( $thisChild->getAttribute('width') );
					}
				}
			}
			
			$childTagName = strtolower( $thisChild->getName() );
			
			if( isset($this->parsers[$childTagName]) )
			{
				$this->parsers[$childTagName]->handle($thisChild, $styleStack, $newDiv);
			}
			else
			{
				if($thisChild->getName() === NULL || $thisChild->getName() === '#text')
					$this->handleTextTag($thisChild, $styleStack, $newDiv);
				else if($this->isStyleTag($thisChild->getName()))
					$this->handleStyleTag($thisChild, $styleStack, $newDiv);
				else if($this->isListTag($thisChild->getName()))
					$this->handleListTag($thisChild, $styleStack, $newDiv);
				else if($this->isTableTag($thisChild->getName()))
					$this->handleTableTag($thisChild, $styleStack, $newDiv);
				else if($this->isLineTag($thisChild->getName()))
					$this->handleLineTag($thisChild, $styleStack, $newDiv);
				else if(!$this->isTextNode($thisChild->getName()))
					$this->handleDivTag($thisChild, $styleStack, $newDiv);
			}
		}
		
		//	any time we put something on the style stack we need to pop it off when we finish 
		//	with the node and its children
		$styleStack->popTopStyle();
	}
	
	function handleDivStyles($curNode, &$newDiv)
	{
		$style = $curNode->getAttribute('style');
		$styleAttributes = explode(';', $style);
		$styleInfo = array();

		foreach($styleAttributes as $thisAttribute)
		{
			if(trim($thisAttribute))
			{
				$styleAttParts = explode(':', $thisAttribute);
				$styleAttName = strtolower(trim($styleAttParts[0]));
				$styleAttValue = strtolower(trim($styleAttParts[1]));

				$nameParts = explode('-', $styleAttName);

				switch($nameParts[0])
				{
					case 'line':
						switch($nameParts[1])
						{
							case 'height':
								sscanf($styleAttValue, "%d%s", $amount, $units);
								if($units == "%")
								{
									$value = (integer)$styleAttValue;
									assert($value > 100);
									$value -= 100;
									$spacing = $value * 0.15;
									$newDiv->setLineSpacing($spacing);
								}
								break;
						}
						break;
					case 'text':
						switch($nameParts[1])
						{
							case 'align':
								switch($styleAttValue)
								{
									case 'left':
										$newDiv->setAlignment('left');
										break;
									case 'center':
										$newDiv->setAlignment('center');
										break;
									case 'right':
										$newDiv->setAlignment('right');
										break;
								}
								break;
							case 'indent':
								if(isset($nameParts[2]))
								{
									switch($nameParts[2])
									{
										case 'lower':
											$newDiv->setBottomIndent($styleAttValue);
											break;
									}
								}
								break;
							default:
								trigger_error("invalid text- attribute: $styleAttValue");
								break;
						}
						break;
					case 'position':
						switch($styleAttValue)
						{
							case 'static':
							case 'container':
								$newDiv->setPosition($styleAttValue);
								break;
							default:
								trigger_error("invalid position- attribute: $styleAttValue");
								break;
						}
						break;
					case 'repeatable':
						$newDiv->makeRepeatable();
						break;
					case 'left':
						$newDiv->setLeft($styleAttValue);
						break;
					case 'top':
						$newDiv->setTop($styleAttValue);
						break;
					case 'height':
						$newDiv->setGivenHeight($styleAttValue);
						break;
					case 'valign':
						$newDiv->setVAlign($styleAttValue);
						break;
					case 'margin':
						if(isset($nameParts[1]))
							switch($nameParts[1])
							{
								case 'v':
									$newDiv->setSideMargin('top', (integer)$styleAttValue);
									$newDiv->setSideMargin('bottom', (integer)$styleAttValue);
									break;
								case 'top':
								case 'bottom':
								case 'left':
								case 'right':
									$newDiv->setSideMargin($nameParts[1], (integer)$styleAttValue);
									break;
							}
						else
							$newDiv->setMargin((integer) $styleAttValue);
						break;
					case 'border':
						if( isset($nameParts[1]) )
						{
							switch($nameParts[1])
							{
								case 'top':
								case 'bottom':
								case 'left':
								case 'right':
									$newDiv->setSideBorder($nameParts[1], $styleAttValue);
									break;
							}
						}
						else
						{
							//	we should make this deal with other sorts of white space
							$valueParts = explode(' ', trim($styleAttValue));
							
							$borderWidth = 0;
							$borderColor = ('#000000');
							foreach($valueParts as $thisPart)
							{
								$part = trim($thisPart);
								if(!$part)
									continue;
								
								if(isset($this->colors[$part]))
									$borderColor = $this->colors[$part];
								else if($part[0] == '#')
									$borderColor = $part;
								else
									$borderWidth = $part;
							}
							
							$newDiv->setBorder((float)$borderWidth);
							$newDiv->setBorderColor($borderColor);
							//$newDiv->setSideMargin('bottom', (integer)$styleAttValue);
						}
						break;
					case 'background':
						switch($nameParts[1])
						{
							case 'color':
								$newDiv->setBgColor($styleAttValue);
								break;
						}
						break;
					default:
						$styleInfo[$styleAttName] = $styleAttValue;
						break;
				}
			}
		}
		
		return $styleInfo;
	}
}
