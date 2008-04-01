<?php
//	in order to inherit from this class the sublcass should have the following properties:
//
//	1)	the class corresponds to one specific table in the database
//	2)	one instance of the class corresponds to exactly one row in that table
//	3) 	the table has a single primary key field

class DbObject
{
	var $id;
	var $scalars;
	var $autosave;
	var $originals;
	var $db;
	var $hasMany;
	var $bound;
	
	//	static
	function _getTableName($className)
	{
		//	work around lack of "late static binding"
		$dummy = new $className('static');
		return $dummy->getTableName();
	}
	
	//	static
	function getDefaults($lookupFields)
	{
		return array();
	}
	
	//	static
	function _getOne($className, $lookupFields)
	{
		return DbObject::_get($className, $lookupFields);
	}
	
	//	static
	//	gets one object for one row based on a set of field values that uniquely identify the row
	//	this code is use right now in the DbObjectiveSet class in perform
	//	it should be updated to return an array of item
	//	_getOne should then be updated to verify that there is one result and then return it
	function _get($className, $lookupFields)
	{
		assert(is_array($lookupFields));
		
		$tableName = DbObject::_getTableName($className);
		
		$fieldParts = array();
		foreach($lookupFields as $fieldName => $fieldValue)
		{
			switch(gettype($fieldValue))
			{
				case 'string':
					$fieldValue = ticks($fieldValue);
					break;
				
				case 'integer':
					$fieldValue = ticks($fieldValue);
					break;
				
				default:
					trigger_error('unhandled field type: ' . gettype($fieldValue));
			}
			
			$fieldParts[] = "$fieldName = $fieldValue";
		}
		
		$fieldClause = implode(' and ', $fieldParts);
		$row = sql_fetch_one("select * from $tableName where $fieldClause");
		
		if($row)
		{
			$object = new $className($row);
		}
		else
		{
			$defaults = call_user_func(array($className, 'getDefaults'), $lookupFields);
			$object = new $className(array_merge($lookupFields, $defaults));
		}
		
		return $object;
	}
	
	function DbObject($init = NULL)
	{
		if($init == 'static')
		{
			$this->bound = 0;
			return;
		}
				
		if(is_numeric($init))
			$init = (int)$init;
		
		global $defaultdb;
		$this->db = $defaultdb;
		
		$initType = gettype($init);
		switch($initType)
		{
			case 'integer':
				$this->id = $init;
				break;
			case 'array':
				if( isset($init['id']) && $init['id'] )
				{
					//	in this case we assume that the data has just come from the db so we don't 
					//	update it in the db
					$this->id = $init['id'];
					$this->assignScalars($init);
				}
				else
				{
					//	in this case we assume the object does not yet exist so we must create it
					//	update it in the db
					$this->initScalars($init);
				}
				break;
			case 'NULL':
				$this->initScalars(NULL);
				break;
			default:
				trigger_error("unknown init type: $initType");
		}
		
		$this->bound = 1;
		$this->autosave = 1;
		
		$this->assignMemberVars();
				
		$this->init();
	}
	
	function init()
	{
		//	override this function to setup relationships without having to handle the constructor chaining
	}
	
	/*	
	function getFields()
	{
		$sql = 'select column_name, data_type from information_schema.columns where table_name = ' . $this->getTableName();
		return $this->db->fetch_simple_map($sql, 'column_name', 'data_type');
	}
	*/
	
	function assignMemberVars()
	{
		$varNames = $this->getMemberVarMap();
		foreach($varNames as $fieldName => $varName)
			$this->$varName = $this->getScalar($fieldName);
	}
	
	function getMemberVarMap()
	{
		return array();
	}
	
	function setDb($db)
	{
		$this->db = $db;
	}
	
	function turnAutosaveOff()
	{
		$this->autosave = 0;
		
		//	make sure we load up the current data from the db
		$this->loadScalars();
		
		//	clone the original values
		$this->originals = $this->scalars;
	}
	
	function getId()
	{
		return $this->id;
	}
	
	function getTableName()
	{
		$name = get_class($this);

		//      if there are any capitals after the firstone insert and underscore
		$name = $name[0] . preg_replace('/[A-Z]/', '_$0', substr($name, 1));

		//      lowercase everything and return it
		return strtolower($name);		
	}
	
	function getIdFieldName()
	{
		return 'id';
	}
	
	function hasDeletedField()
	{
		return 0;
	}
	
	function assignScalars($data)
	{
		foreach($data as $member => $value)
		{
			$this->scalars[$member] = $value;
		}
	}
	
	//	initialize the object and the row in the db with these values
	function initScalars($init)
	{
		if(!$init)
		{
			if(!$this->hasDeletedField())
				trigger_error("can't complete insert.  we don't know what any of the fields are");
			
			$tableName = $this->getTableName();
			$sql = "INSERT INTO $tableName (deleted) VALUES (1)";
			$this->id = $this->db->insert($sql, "{$tableName}_id_seq");
		}
		else
		{
			$tableName = $this->getTableName();
			if($this->hasDeletedField())
				$init['deleted'] = 1;
			$fields = implode(', ', array_keys($init));
			$values = implode("', '", array_values($init));
			$sql = "INSERT INTO $tableName ($fields) VALUES ('$values')";
			$this->id = $this->db->insert($sql);
		}
	}
	
	function loadScalars()
	{
		$tableName = $this->getTableName();
		$idFieldName = $this->getIdFieldName();
		$sql = "SELECT * FROM $tableName WHERE $idFieldName = {$this->id}";
		$row = $this->db->fetch_one($sql);
		$this->assignScalars($row);
	}
	
	function setScalars($data)
	{
		if($this->autosave)
		{
			//	if they passed in an id we don't need to reset it
			//		just verify that it is the right one
			if( isset($data['id']) )
			{
				assert($data['id'] == $this->id);
				unset($data['id']);
			}

			$tableName = $this->getTableName();
			$idFieldName = $this->getIdFieldName();
			
			//	by default we want to make sure things are undeleted when they are updated
			if($this->hasDeletedField() && !isset($data['deleted']) )
				$data['deleted'] = 0;
			
			$this->assignScalars($data);
			$updateFields = array();
			foreach($data as $member => $value)
			{
				if($value === null)
					$updateFields[] = "$member = NULL";
				else
					$updateFields[] = "$member = '$value'";
			}
			$updateFields = implode(", ", $updateFields);
			$this->db->query("update $tableName set $updateFields where $idFieldName = $this->id");
		}
		else
		{
			$this->assignScalars($data);
		}
	}
	
	function getScalar($field)
	{
		if(!isset($this->scalars[$field]))
			$this->loadScalars();
		
		return $this->scalars[$field];
	}
	
	function getScalars()
	{
		return $this->scalars;
	}
	
	function setScalar($field, $value)
	{
		$scalars = array($field => $value);
		$this->setScalars($scalars);
	}
	
	function save()
	{
		//	if they passed in an id we don't need to reset it
		//		just verify that it is the right one
		if( isset($data['id']) )
		{
			assert($data['id'] == $this->id);
			unset($data['id']);
		}

		$tableName = $this->getTableName();
		$idFieldName = $this->getIdFieldName();
		
		//	by default we want to make sure things are undeleted when they are updated
		if( !isset($data['deleted']) && $this->hasDeletedField())
			$data['deleted'] = 0;
		
		$updateFields = array();
		foreach($this->scalars as $fieldName => $fieldValue)
		{
			//	only save the data that has actually changed
			if($this->originals[$fieldName] != $this->scalars[$fieldName])
			{
				if($fieldValue === null)
					$updateFields[] = "$fieldName = NULL";
				else
					$updateFields[] = "$fieldName = '$fieldValue'";
			}
		}
		
		//	if nothing changed then we don't need to do the update
		if(count($updateFields) > 0)
		{
			$updateFields = implode(", ", $updateFields);
			$this->db->query("update $tableName set $updateFields where $idFieldName = $this->id");
		}
	}
	
	function hasMany($name, $params = NULL)
	{
		if(isset($params['class']))
			$className = $params['class'];
		else
			$className = $name;
		
		if(isset($params['field']))
			$remoteKey = $params['field'];
		else
			$remoteKey = $this->getTableName() . '_id';
		
		$this->hasMany[$name] = array('className' => $className, 'remoteKey' => $remoteKey);
	}
	
	function getMany($name)
	{
		$className = $this->hasMany[$name]['className'];
		$remoteKey = $this->hasMany[$name]['remoteKey'];
		$sortBy = isset($this->hasMany[$name]['sortBy']) ? $this->hasMany[$name]['sortBy'] : 'id';	
		
		$tableName = call_user_func(array('DbObject', '_getTableName'), $className);
		
		$sql = "select * from $tableName where $remoteKey = " . (int)$this->id . " order by $sortBy";
		$rows = sql_fetch_rows($sql);
		
		$objects = array();
		foreach($rows as $thisRow)
		{
			$objects[] = new $className($thisRow);
		}
		
		return $objects;
	}
	
	function belongsTo($name, $params = NULL)
	{
		//	determine the name of the class we belong to
		$className = isset($params['class']) ? $params['class'] : $name;
		
		$tableName = DbObject::_getTableName($className);
		
		//	get the name of the foreign key in this table
		$localKey = isset($params['key']) ? $params['key'] : $tableName . '_id';
		
		$this->belongsTo[$name] = array('className' => $className, 'localKey' => $localKey);
	}
	
	function getOwner($name)
	{
		$className = $this->belongsTo[$name]['className'];
		$localKey = $this->belongsTo[$name]['localKey'];
		$tableName = DbObject::_getTableName($className);
		return new $className($this->getScalar($localKey));
	}
}
