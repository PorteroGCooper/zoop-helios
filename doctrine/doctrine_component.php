<?php
/**
* @category zoop
* @package doctrine
*/
// Copyright (c) 2008 Supernerd LLC and Contributors.
// All Rights Reserved.
//
// This software is subject to the provisions of the Zope Public License,
// Version 2.1 (ZPL). A copy of the ZPL should accompany this distribution.
// THIS SOFTWARE IS PROVIDED "AS IS" AND ANY AND ALL EXPRESS OR IMPLIED
// WARRANTIES ARE DISCLAIMED, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
// WARRANTIES OF TITLE, MERCHANTABILITY, AGAINST INFRINGEMENT, AND FITNESS
// FOR A PARTICULAR PURPOSE.

 /**
 * @package doctrine
 * @uses component
 * @version $id$
 * @copyright 1997-2008 Supernerd LLC
 * @author Steve Francia <webmaster@supernerd.com> 
 * @license Zope Public License (ZPL) Version 2.1 {@link http://zoopframework.com/ss.4/7/license.html}/
 */
class component_doctrine extends component
{
	function component_doctrine()
	{
#		$this->requireComponent('db');
#		$this->requireComponent('gui');
#		$this->requireComponent('guicontrol');
#		$this->requireComponent('cache');
#		$this->requireComponent('validate');
	}

	function init()
	{
		require_once('Doctrine.php');
		spl_autoload_register(array('Doctrine', 'autoload'));
		Doctrine_Manager::connection(DOC_RDBMS . '://' . DOC_USER . ":" . DOC_PASS . "@" . DOC_HOST . "/" . DOC_DB);

		Doctrine_Manager::getInstance()->setAttribute('model_loading', 'conservative');
		Doctrine::loadModels(app_dir . '/models'); // This call will not require the found .php files

	}
	
#	function getIncludes()
#	{
#		$file = $this->getBasePath();
#		return array(
#				"form2" => $file . "/doctrine2.php",
#				"form" => $file . "/doctrine.php",
#				"table" => $file . "/table.php",
#				"record" => $file . "/record.php",
#				"field" => $file . "/field.php",
#				"cell" => $file . "/cell.php",
#				"xml_serializer" => "XML/Serializer.php"
#		);
#	}
}


