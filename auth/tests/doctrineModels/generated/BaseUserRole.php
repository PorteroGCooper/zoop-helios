<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseUserRole extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('users_roles');
    $this->hasColumn('user_id', 'integer', null, array('notnull' => false, 'primary' => true));
    $this->hasColumn('role_id', 'integer', null, array('notnull' => false, 'primary' => true));
  }

  public function setUp()
  {
    parent::setUp();
  }

}
