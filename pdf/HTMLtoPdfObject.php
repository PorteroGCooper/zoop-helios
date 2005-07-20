<?
class HTMLtoPdfObject
{
	var $paper_height = kPdf_default_page_height;
	var $paper_width = kPdf_default_page_height;
	var $left_margin = 5;
	var $right_margin = 5;
	var $top_margin = 5;
	var $bottom_margin = 5;
	//contains map from tagname to class name...
	//the params are the attributes in order of parameters to the object's constructor...
	//probably be good if we had some way of mandating after construction configuration...
	/*var $convert = array
	(
		"table" => array
		(
			"class" => "pdfTable",
			"params" => array
			(
				"width" => kPdf_default_page_width, //
				"height" => kPdf_default_page_height, //
				"border" => 0,
				"cellspacing" => 0,
				"align" => "center",
				"valign" => "middle"
			)
		),
		"tr" => array
		(
			"class" => "PdfTableRow",
			"params" => array()
		),
		"td" => array
		(
			"class" => "PdfTableCell",
			"params" => array
			(
				"colspan" => 0
			)
		),
		"img" =>array
		(
			"class" => "PdfImage",
			"params" => array
			(
				"src" => ""
			)
		)
	);*/
	
	function HTMLtoPdfObject($html, $pdf, $paper_height = kPdf_default_page_height, $paper_width = kPdf_default_page_width, $margins = 5) //$extraconvert = array()
	{
		$this->_html = $html;
		$this->_pdf = $pdf;
		
		$this->paper_height = $paper_height;
		
		$this->paper_width = $paper_width;
		
		if(is_array($margins))
		{
			if($margins["top"])
			{
				$this->top_margin = $margins["top"];
			}
			if($margins["bottom"])
			{
				$this->bottom_margin = $margins["bottom"];
			}
			if($margins["left"])
			{
				$this->left_margin = $margins["left"];
			}
			if($margins["right"])
			{
				$this->top_margin = $margins["right"];
			}
		}
		else
		{
			$this->top_margin = $margins;
			$this->bottom_margin = $margins;
			$this->left_margin = $margins;
			$this->right_margin = $margins;
		}
		
	}
	
	function parse()
	{
		
		
		$this->_html = str_replace("<br>", "<br />", $this->_html);
		$this->_html = str_replace("<br/>", "<br />", $this->_html);
		$this->_html = str_replace("<br />", "\r\n", $this->_html);
		include_once("HTML/Tree.php");
		
		// instantiate object
		$tree = new HTML_Tree();
		
		// create string
		// read string into tree
		$root =& $tree->getTreeFromString($this->_html);
		$htmltree = $root;
		echo_r($htmltree);
		$pdfobjects = array();
		$parent = NULL;
		$properties;
		foreach($htmltree->children as $tree)
		{
			$pdfobjects = array_merge($this->parseTree($tree, $parent, $properties), $pdfobjects);
		}
		return $pdfobjects;
	}
	
	function parseTree(&$htmltree, &$parent, $properties)
	{	
		echo("parseTree<br>");	
		switch($htmltree->name)
		{
			case "em":
				$properties->_italics = true;
				$answer = array();
				foreach($htmltree->children as $tree)
				{
					$answer[] = $this->parseTree($tree, $parent, $properties);
				}
				return $answer;
			case "strong":
				$properties->_bold = true;
				$answer = array();
				foreach($htmltree->children as $tree)
				{
					$answer[] = $this->parseTree($tree, $parent, $properties);
				}
				$properties->_bold = false;
				return $answer;
			case "table":
				return $this->parseTable($tree, $parent, $properties);
				break;
			case "ul":
				return $this->parseList($tree, $parent, $properties);
				break;
			case "img":
				return $this->parseImage($tree, $parent, $properties);
				break;
			case "p":
				return $this->parseParagraph($htmltree, $parent, $properties);
				break;
			case "":
				//this is a text box...
				
				$pdfTextBox = $this->createTextBox($htmltree->contents, $parent, $properties);
				if($parent)
				{
					$answer[] = $parent;
				}
				else
				{
					
					$answer[] = $pdfTextBox;
				}
				return $answer;
			default:
				echo("tag: $key is inappropriate, or unhandled here");
				break;
		}		
		echo("/parseTree<br>");	
	}
	
	function createTextBox($contents, &$parent, $properties)
	{
		echo("createTextBox<br>");
		if($parent)
		{
			$pdfTextBox = &$parent->getPdfTextBox($contents);
			$this->setProperties($pdfTextBox, $properties);
			$pdfTextBox = $parent;			
		}
		else
		{
			$pdfTextBox = &new PdfTextBox($this->_pdf, $htmltree->contents);
		}
		echo("/createTextBox<br>");
		return $pdfTextBox;
	}
	
	function setProperties($pdfObject, $properties)
	{
		echo("setProperties<br>");
		echo("/setProperties<br>");
		//hmm....
	}
	
	function parseParagraph(&$tree, &$parent)
	{
		echo("parseParagraph<br>");
		assert($tree->name == "p");
		if($parent)
		{
			if($tree->content)
			{
				$pdfTextBox = &$parent->getPdfTextBox($tree->content);
				$pdfTextBox->setItalics($this->_italics);
				$pdfTextBox->setBold($this->_bold);
			}
			else
			{
				
				if(count($tree->attributes))
				{
					foreach($tree->attributes as $attr => $value)
					{
						$property = "_$attr";
						$this->$property = $value;
					}
				}
				//parse the children(gently)
				foreach($tree->children as $childtree)
				{
					$answer[] = $this->parseTree($childtree, $parent, $properties);
				}				
			}			
		}
		else
		{
			if($tree->content)
			{
				$pdfTextBox = &new PdfTextBox($this->_pdf, $tree->content);
				$pdfTextBox->setItalics($this->_italics);
				$pdfTextBox->setBold($this->_bold);
			}
			else
			{
				//parse the children(gently)
				foreach($tree->children as $childtree)
				{
					$answer[] = $this->parseTree($childtree, $parent, $properties);				
				}
				
			}					
		}
		echo("/parseParagraph<br>");
		return $answer;
	}
			
}
?>