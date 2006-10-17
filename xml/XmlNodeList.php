<?

//	we should probably have an array itterator class that this inherits from

class XmlNodeList
{
	var $nodeList;
	
	function XmlNodeList($inNodeData)
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
			$this->nodeList = $inNodeData->children;
		else
			$this->nodeList = $inNodeData->firstChild;
	}
	
	function rewind()
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
			reset($this->nodeList);
		else
		{
			if($this->valid())
				$this->nodeList = $this->nodeList->parentNode->firstChild;
		}
	}
	
	function current()
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			$cur = current($this->nodeList);
			return $cur === false ? false : new XmlNode($cur);
		}
		else
		{
			return is_null($this->nodeList) ? false : new XmlNode($this->nodeList);
		}
	}
	
	function key()
	{
		trigger_error("I don't think that we actually use this");
		return key($this->nodeList);
	}
	
	function next()
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			$next = next($this->nodeList);
			return $next === false ? false : new XmlNode($next);
		}
		else
		{
			$this->nodeList = $this->nodeList->nextSibling;
			return is_null($this->nodeList) ? false : new XmlNode($this->nodeList);
		}
	}
	
	function valid()
	{
		return $this->current() !== false;
	}
}
?>
