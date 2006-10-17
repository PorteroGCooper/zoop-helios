<?
if(version_compare(phpversion(), "5.0.0", "<"))
	require_once('XML/Tree.php');
include_once(dirname(__file__) . "/XmlDom.php");
include_once(dirname(__file__) . "/XmlNode.php");
include_once(dirname(__file__) . "/XmlNodeList.php");
include_once(dirname(__file__) . "/PropertyList.php");
include_once(dirname(__file__) . "/utils.php");
?>