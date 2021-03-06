<?php

/**
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class User extends BaseUser
{


  public function setUp()
  {
	  $this->hasMany('Association', array('local' => 'user_id',
		  								'foreign' =>  'association_id',
										'refClass' => 'UserAssociation'));

	  $this->hasMany('Role', 		array('local' => 'user_id',
		  								'foreign' =>  'role_id',
										'refClass' => 'UserRole'));
  }

}
