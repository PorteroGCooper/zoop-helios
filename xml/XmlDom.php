<?
class XmlDom
{
	var $xmlTree;
	
	function XmlDom()
	{
		
	}
	
	//	returns the root node
	function parseText($xml)
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			$this->xmlTree = new XML_Tree();
			$tree = $this->xmlTree->getTreeFromString($xml);
			return new XmlNode($tree);
		}
		else
		{
			$this->xmlTree = new DOMDocument();
			$this->xmlTree->loadXML($xml);
			return new XmlNode($this->xmlTree->firstChild);
		}
	}
	
	function parseFile($fileName)
	{
		if(version_compare(phpversion(), "5.0.0", "<"))
		{
			$this->xmlTree = new XML_Tree($fileName);
			$tree = $this->xmlTree->getTreeFromFile();
			return new XMLNode($tree);
		}
		else
		{
			$this->xmlTree = new DOMDocument();
//			print_r($this->xmlTree);
			$this->xmlTree->load($fileName);
//			print_r($this->xmlTree->documentElement->firstChild->nextSibling->nodeName);
//			die('here');
			return new XmlNode($this->xmlTree->documentElement);	
		}
	}
}
?>
