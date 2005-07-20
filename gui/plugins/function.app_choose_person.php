<?
/**
 * This function will do a search for people and return the results as
 * $array[person.id] => person.lastname, person.firstname - person.title
 *
 */
function smarty_function_app_choose_person($params, &$smarty)
	{
		// $name, $titleText, $errorText, $fromclause = "person")
		$name = (isset($params['name'])) ? $params['name']: '';
		$fromclause = (isset($params['fromclause'])) ? $params['fromclause']: '';
		$assign = $params['assign'];

		// Default to person table
		if (!$fromclause) $fromclause = 'person_period person inner join period on person.period_id = period.id and person.period_id = ' . app_current_year;

		// This function must assign the result to a smarty var.
		if (empty($assign))
		{
			$smarty->trigger_error("app_choose_person: missing 'assign' parameter");
			return;
		}


		if($name)
		{
			$sql = "SELECT
					person.person_id as id, 
					person.lastname || ', ' || person.firstname || ' - ' || person.title AS name
				FROM
					$fromclause
				WHERE
					lower(lastname) LIKE lower('{$name}%') AND
					level is not null
				ORDER BY
					lastname, firstname
			";

			// Get all people and assign to $assign
			$smarty->assign($assign, sql_fetch_simple_map($sql, 'id', 'name'));
		} else  {
			$smarty->assign($assign, '');
		}

	}


?>
