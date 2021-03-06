<?
class XmlNode
{
	var $nodeData;
	
	function XmlNode($inNodeData)
	{
		$this->nodeData = $inNodeData;
	}
	
	function &getChildren()
	{
		$nodeList = &new XmlNodeList($this->nodeData);
//		print_r($nodeList);
		$nodeList->rewind();
		return $nodeList;
	}
	
	function getName()
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
			return $this->nodeData->name;
		else
		{
//			if($this->nodeData->nodeType !== XML_TEXT_NODE)
				return $this->nodeData->nodeName;
//			else
//				return 'TEXT';
		}
	}
	
	function hasContent()
	{
		/*
		if(version_compare(phpversion(), "5.0.0", "<"))
			$content = trim(ereg_replace('&#([0-9]*);', '', $this->nodeData->content));
		else
			$content = trim(ereg_replace('&#([0-9]*);', '', $this->nodeData->textContent));
		*/
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			$content = ereg_replace('&#([0-9]*);', '', $this->nodeData->content);
			$content = html_entity_decode($content);
		}
		else
		{
			if($this->nodeData->nodeType === XML_TEXT_NODE)
				return true;
			
			return false;
			//$content = ereg_replace('&#([0-9]*);', '', $this->nodeData->textContent);
			//$content = html_entity_decode($content);
		}
		
//		var_dump($content);
		
		//$content = trim(str_replace('&#160;', '', $this->nodeData->content));
		//$content = trim(str_replace('&#160;', '', $this->nodeData->content));

		return $content ? 1 : 0;
	}
	
	function getContent()
	{
		$results = array();
		
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			$originalContent = ereg_replace('&#([0-9]*);', '', $this->nodeData->content);
			$originalContent = html_entity_decode($originalContent);
		}
		else
		{
			if($this->nodeData->nodeType === XML_TEXT_NODE)
			{
				$originalContent = ereg_replace('&#([0-9]*);', '', $this->nodeData->textContent);
				$originalContent = html_entity_decode($originalContent);
			}
			else
			{
				trigger_error('only use content of text nodes.  Look at children for this info otherwise');
			}
		}
		//$originalContent = str_replace('&#160;', '', $this->nodeData->content);
		
		$leftContent = ltrim($originalContent);
		if(strlen($originalContent) == strlen($leftContent))
			$results['leftTrim'] = 0;
		else
			$results['leftTrim'] = 1;
		
		$content = rtrim($leftContent);
		if(strlen($leftContent) == strlen($content))
			$results['rightTrim'] = 0;
		else
			$results['rightTrim'] = 1;
		
		$results['content'] = $content;
		
		return $results;
	}
	
	function getTextContent()
	{
		$results = array();
		
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			$originalContent = ereg_replace('&#([0-9]*);', '', $this->nodeData->content);
			$originalContent = html_entity_decode($originalContent);
		}
		else
		{
			if($this->nodeData->nodeType === XML_TEXT_NODE)
			{
				$originalContent = ereg_replace('&#([0-9]*);', '', $this->nodeData->textContent);
				$originalContent = html_entity_decode($originalContent);
			}
			else
			{
				//	if there is exactly one text node return the contents of that text node
				//	otherwise throw an error
				$children = $this->getChildren();
				$onlyChild = $children->current();
				assert($onlyChild);						//	throw an error if there is not child
				assert(!$children->next());				//	throw an error if there is more than one child
				$content = $onlyChild->getContent();	//	throw an error if the only is not a text node
				$originalContent = ereg_replace('&#([0-9]*);', '', $content['content']);	
				$originalContent = html_entity_decode($originalContent);
			}
		}
		//$originalContent = str_replace('&#160;', '', $this->nodeData->content);
		
		$leftContent = ltrim($originalContent);
		if(strlen($originalContent) == strlen($leftContent))
			$results['leftTrim'] = 0;
		else
			$results['leftTrim'] = 1;
		
		$content = rtrim($leftContent);
		if(strlen($leftContent) == strlen($content))
			$results['rightTrim'] = 0;
		else
			$results['rightTrim'] = 1;
		
		$results['content'] = $content;
		
		return $results;
	}
	
	function hasAttribute($attributeName)
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			if( isset($this->nodeData->attributes[$attributeName]) )
				return 1;
			else
				return 0;
		}
		else
		{
			if( $this->nodeData->hasAttribute($attributeName) )
				return 1;
			else
				return 0;
		}
	}
	
	function getAttribute($attributeName)
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			if( isset($this->nodeData->attributes[$attributeName]) )
				return $this->nodeData->attributes[$attributeName];
		}
		else
		{
			if($this->nodeData->attributes !== null)
			{
				$att = $this->nodeData->attributes->getNamedItem($attributeName);
				if( !is_null($att) )
					return $att->value;
				
			}
		}
		trigger_error('attribute does not exist: ' . $attributeName);
	}
	
	function getAttributes()
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			return $this->nodeData->attributes;
		}
		else
		{
			$atts = array();
			if($this->nodeData->attributes)
			{
				foreach($this->nodeData->attributes as $key => $val)
				{
					$atts[$key] = $val->value;
				}
			}
			
			return $atts;
		}
	}
}
?>
