<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
abstract class BaseUserAssociation extends Doctrine_Record
{

  public function setTableDefinition()
  {
    $this->setTableName('users_associations');
    $this->hasColumn('user_id', 'integer', null, array('notnull' => false, 'primary' => true));
    $this->hasColumn('association_id', 'integer', null, array('notnull' => false, 'primary' => true));
  }

  public function setUp()
  {
    parent::setUp();
  }

}