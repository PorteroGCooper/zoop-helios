<?php

class UserTable extends Doctrine_Table {

	function customFindByMultiple($array) {

		if ( ! empty($array) && is_array($array)) {
			$query = Doctrine_Query::create()
				->select('u.id, u.username, u.password')
				->from('users u');

			foreach ($array as $column => $value) {
				$query->addWhere("u.$column = ?", $value);
			}

			$user = $query->execute();
		}

		return $user;

	}

}
