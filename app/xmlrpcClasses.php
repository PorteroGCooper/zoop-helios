<?
// Copyright (c) 2005 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

include_once("XML/RPC.php");
class xmlrpc_client extends xmlrpc_requestHandler
{
	var $cert = false;
	var $certpass = false;
	var $timeout = 10;

	function xmlrpc_client()
	{

		//$this->xmlrpc_requestHandler();
		// nothing to do here as of yet
	}

	function callFunction($url, $function, $parms, $username = "", $password = "")
	{
		$url = parse_url($url);
		$this->xmlclient = &new xml_rpc_client($url['path'], $url['scheme'] . "://" . $url['host']);
		//$this->xmlclient->setDebug(1);
		$this->xmlmessage = &new xml_rpc_message($function);
		$parms = $this->buildValues($parms);
		$this->xmlmessage->addParam($parms);
		$response = $this->xmlclient->send($this->xmlmessage);
		$value = $response->value();
		return $this->deconstructValue($value);
	}


};

class xmlrpc_server extends xmlrpc_requestHandler
{
	var $methodname;
	var $params;

	function startServer($postdata = "")
	{
		$this->postdata = (strlen($postdata) > 0) ? $postdata : $GLOBALS["HTTP_RAW_POST_DATA"];


		if ($this->isRequest())
		{
			include_once("XML/RPC/Server.php");
			global $XML_RPC_xh;
			$this->xmlserver = &new XML_RPC_Server(array(),0);
			$response = $this->xmlserver->parseRequest($postdata);
			//$response is an error....
			//Pear's XML_RPC stuff doesn't allow you to just parse the message and handle
			//dispatch yourself, you have to do some dumb dispatch crap.
			//So we do this workaround where it gets an error the first time, just so
			//it parses and we can get the method names from it and such.
			//This is very very bad I think, because we have to know something about
			//the internals of XML_RPC.
			//print_r($XML_RPC_xh);
			//This is one method of doing the dirty dance we are required to dance
			$parser = reset($XML_RPC_xh);
			$this->methodname = $parser['method'];
			//here is another, which is not as safe...
			//actually this doesn't work, because pear doesn't keep parser as a member.
			//It would be better.
			/*
			$parser = $this->xmlserver->parser;
			$this->methodname = $XML_RPC_xh[$parser]['method'];
			*/

			//later (when the zone requests it) we recall the constructor with the a dispatch map that
			//calls our own method to get the parameters so that we can handle the dispatch ourselves.
		}
	}

	function xmlCallBack($message)
	{
		$this->params = array();
		if($message->getNumParams() == 1)
		{
			$rpcvalue = $message->getParam(0);
			$this->params = $this->deconstructValue($rpcvalue);
		}
		else
		{
			for($i = 0; $i < $message->getNumParams(); $i++)
			{
				$rpcvalue = $message->getParam($i);
				$this->params[] = $this->deconstructValue($rpcvalue);
			}
		}
	}

	function getRequestVars()
	{
		$this->xmlserver->XML_RPC_Server(
					array(
						$this->methodname => array(
							'function' => array(
								&$this, "xmlCallback")
							)
						), 0);
		$this->xmlserver->parseRequest($this->postdata);
		return $this->params;
	}

	function returnFault($faultcode, $faultmessage)
	{
		$xml = "<" . "?xml version=\"1.0\"?" . ">
			<methodResponse>
				<fault>
					<value>
						<struct>
							<member>
								<name>faultCode</name>
								<value><int>$faultcode</int></value>
							</member>
							<member>
								<name>faultString</name>
								<value><string>$faultmessage</string></value>
							</member>
						</struct>
					</value>
				</fault>
			</methodResponse>";

		header("Content-Type: text/xml");
		echo $xml;
	}

	function returnValues($mixed)
	{
		$value = $this->buildValues($mixed);
		$response = &new xml_rpc_response($value);
		echo $response->serialize();
		return;
	}


	function isRequest()
	{
		if (!isset($GLOBALS["HTTP_RAW_POST_DATA"]))
		{
			return false;
		}

		$postdata = $GLOBALS["HTTP_RAW_POST_DATA"];
		if (ereg("<methodCall>", $postdata))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

class xmlrpc_requestHandler
{
	function deconstructValue($value)
	{
		if(is_a($value, "xml_rpc_value"))
		{
			$value = $value->getVal();
		}
		if(is_array($value))
		{
			foreach($value as $key => $v)
			{
				$value[$key] = $this->deconstructValue($v);
			}
		}
		return $value;
	}

	function buildValues($mixed)
	{
		$type = '';
		if(is_array($mixed))
		{
			$type = 'struct';
			foreach($mixed as $key => $value)
			{
				$mixed[$key] = $this->buildValues($value);
			}
		}
		return new xml_rpc_value($mixed, $type);
	}
}; // end of class xml
?>