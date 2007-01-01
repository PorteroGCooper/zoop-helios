<?

/**
 * define_once
 *
 * use instead of define to insure that the the $name is not being redefined
 * @param mixed $name
 * @param mixed $value
 * @access public
 * @return void
 */
function define_once($name, $value){
        if(!defined($name))
                define($name, $value);
}

?>
