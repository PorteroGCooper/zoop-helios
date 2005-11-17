<?
/**
 * Zoop Smarty plugin
 * @package gui
 * @subpackage plugins
 */
function smarty_function_tree($params, &$smarty, $print_result = true)
{
    extract($params);

    $html_result = '';

    settype($from, 'array');
    if (!isset($params["nocell"]))
    {
		$html_result .= "
	    <tr>
	    	<td>";
    }
	if(!isset($children))
	{
		$children = "children";
	}
	if(!isset($linkField))
	{
		$linkField = "link";
	}
	if(!isset($onClick))
	{
		$onClick = "onclick";
	}
	if(!isset($classField))
	{
		$classField = "class";
	}
	if(!isset($idField))
	{
		$idField = 'id';
	}
	
	if (!isset($highlight_items))
	{
		$highlight_items = array();
	}
	
	if (!isset($highlight_color))
	{
		$highlight_color = "#ffffff";
	}
	
	if (!isset($highlight_background))
	{
		$highlight_background = "#ffcc00";
	}
	
	$html_result .= __smarty_buildtree($from, $children, $outputField,$linkField, $onClick, $classField, $idField, $highlight_items, $highlight_color, $highlight_background);
    if (!isset($params["nocell"]))
    {
		$html_result .= "
			</td>
		</tr>";
	}
	if($print_result)
	{
		echo($html_result);
	}   	
}

function __smarty_buildtree($node, $children, $outputField, $linkField, $onClick, $classField, $idField, $highlighted = array(), $highlightcolor = "#ffffff", $highlightbackground = "#ffcc00", $depth = 0)
{
	//echo("here");
	//html_print_r($node);
	//die();
	$result = "
<table border='0' cellspacing='0' cellpadding='0' class='treetable'>
	<tr class='treerow'>
		<td colspan='2' class='treecell'>";
	if(isset($node[$children]) && count($node[$children]) > 0)
	{
		$result .= "
			<a href='' onclick='ToggleChildren(this); return false;' class='treebutton'>+</a>";
	}
	$result .= "
			";
	if(isset($node[$linkField]) || isset($node[$onClick]))
	{
		if(isset($node[$onClick]))
		{
			$action = $node[$onClick];
		}
		else
		{
			$action = "return true;";
		}
		if(isset($node[$linkField]))
		{
			$href = $node[$linkField];
		}
		else
		{
			$href = "#";
		}
		$result .= "<a href='$href' onclick='$action' class='";
		if(isset($node[$classField]))
		{
			$result .= "$node[$classField]";
		}
		else
			$result .= "treelink";
		$result .= "'";
		if(isset($node[$idField]))
			$result .= " id=\"$node[$idField]\"";
		if(in_array($node["id"], $highlighted))
		{
			$result .= " style=\"background-color: $highlightbackground; color: $highlightcolor;\"";
			$result .= " oldColor=\"\" oldbgColor=\"\"";
		}
		$result .= ">";
	}
	
	$result .= $node[$outputField];
	if(isset($node[$linkField]) || isset($node[$onClick]))
	{
		$result .= "</a>";
	}
	
	$result .="
		</td>
	</tr>";
	if(isset($node[$children]) && count($node[$children]) > 0)
	{
		$result .= "
	<tr";
		if($depth > 0 && (isset($node[$linkField]) || isset($node[$onClick])) && !__function_tree_childrenContain($node, $highlighted) ) $result .= " style='display:none'";
		
		$result .= " class='treerow'>
		<td class='treecell'>
			&nbsp;&nbsp;&nbsp;&nbsp;
		</td>
		<td class='treecell'>
			<table border='0' cellspacing='0' cellpadding='0' class='treetable'>
				<tr>
					<td>";
		foreach($node[$children] as $child)
		{
			$result .= __smarty_buildtree($child, $children, $outputField, $linkField, $onClick, $classField, $idField, $highlighted, $highlightcolor, $highlightbackground, $depth+1);
		}
		$result .= "
					</td>
				</tr>
			</table>
		</td>
	</tr>";
	}
	$result .="	
</table>";
	return $result;
}

function __function_tree_childrenContain($tree, $arr)
{
	if (!is_array($arr))
	{
		echo_r($arr);
		die();
	}
	if (!isset($tree["children"]) || count($tree["children"]) == 0) return false;
	foreach ($tree["children"] as $id => $childTree)
	{
		if (in_array($id, $arr) || __function_tree_childrenContain($childTree, $arr)) return true;
	}
}
				
/*
{$object parent and id}
<table border="0" cellspacing="0" cellpadding="0">
 	<tr>
 		<td>
 			{call with $tree(s)}
 			<table border="0" cellspacing="0" cellpadding="0">
 				{foreach $tree(s) as $tree}
 				
 				<tr>
 					<td colspan="2">{if has children($tree)}<a href="" onclick="ToggleChildren(this); return false;">+</a>{/if}1</td>
 				</tr>
 				{if has Children}
 				<tr>
					<td>&nbsp;</td>
					<td>
						{recursive call with children array...}
						<table border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td>
									<table border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td><a href="" onclick="ToggleChildren(this); return false;">+</a></td><td colspan="2">1.1</td>
										</tr>
										<tr style="display: some">
											<td>&nbsp;</td>
											<td width="10">&nbsp;</td>
											<td>
												<table border="0" cellspacing="0" cellpadding="0">
													<tr>
														<td>
															1.1.1
														</td>
													</tr>
													<tr>
														<td>
															1.1.2
														</td>
													</tr>
													<tr>
														<td>
															1.1.3
														</td>
													</tr>
												</table>
											</td>
										</tr>
 									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td colspan="2">1.2</td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table border="0" cellspacing="0" cellpadding="0">
										<tr>
											<td colspan="2">1.3</td>
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
				</tr>
 			</table>
 		</td>
 	</tr>
 	<tr>
		<td>
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td colspan="2">2</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td>
						<table border="0" cellspacing="0" cellpadding="0">
							<tr>
								<td>
									2.1
								</td>
							</tr>
							<tr>
								<td>
									2.2
								</td>
							</tr>
							<tr>
								<td>
									2.3
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
 	</tr>
</table>

*/
?>