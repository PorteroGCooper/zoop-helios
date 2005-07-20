<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     html_select_date
 * Version:  1.1
 * Purpose:  Prints the dropdowns for date selection.
 * Author:   Andrei Zmievski
 *
 * ChangeLog: 1.0 initial release
 *            1.1 added support for +/- N syntax for begin
 *                and end year values. (Monte)
 * -------------------------------------------------------------
 */
require_once SMARTY_DIR . 'plugins/shared.make_timestamp.php';
require_once SMARTY_DIR . 'plugins/function.html_options.php';
require_once 'Date.php';
function smarty_function_html_select_date_null($params, &$smarty, $print_result = true)
{
    /* Default values. */
    $prefix          = "Date_";
    $time            = 0;
    $start_year      = strftime("%Y");
    $end_year        = $start_year;
    $display_days    = true;
    $display_months  = true;
    $display_years   = true;
    $month_format    = "%b";
    $day_format      = "%02d";
    $year_as_text    = false;
    /* Display years in reverse order? Ie. 2000,1999,.... */
    $reverse_years   = false;
    /* Should the select boxes be part of an array when returned from PHP?
       e.g. setting it to "birthday", would create "birthday[Day]",
       "birthday[Month]" & "birthday[Year]". Can be combined with prefix */
    $field_array     = null;
    /* <select size>'s of the different <select> tags.
       If not set, uses default dropdown. */
    $day_size        = null;
    $month_size      = null;
    $year_size       = null;
    /* Unparsed attributes common to *ALL* the <select>/<input> tags.
       An example might be in the template: all_extra ='class ="foo"'. */
    $all_extra       = null;
    /* Separate attributes for the tags. */
    $day_extra       = null;
    $month_extra     = null;
    $year_extra      = null;
    /* Order in which to display the fields.
       "D" -> day, "M" -> month, "Y" -> year. */
    $field_order      = 'MDY';
    /* String printed between the different fields. */
    $field_separator = "\n";

    extract($params);
	
	// make syntax "+N" or "-N" work with start_year and end_year
	if (preg_match('!^(\+|\-)\s*(\d+)$!', $end_year, $match)) {
		if ($match[1] == '+') {
			$end_year = strftime('%Y') + $match[2];
		} else {
			$end_year = strftime('%Y') - $match[2];
		}
	}
	if (preg_match('!^(\+|\-)\s*(\d+)$!', $start_year, $match)) {
		if ($match[1] == '+') {
			$start_year = strftime('%Y') + $match[2];
		} else {
			$start_year = strftime('%Y') - $match[2];
		}
	}
	$date = &new Date();
	
	if($time != 0)
	{
		if(strpos($time, " ") !== false)
		{			
			$time = substr($time, 0, strpos($time, " "));//in case there's extra things...
		}
		
		$parts = explode("-", $time);
		
		if( isset($parts[0]) )
			$yearPart = $parts[0];
		else
			$yearPart = 0;

		if( isset($parts[1]) )
			$monthPart = $parts[1];
		else
			$monthPart = 0;

		if( isset($parts[2]) )
			$dayPart = $parts[2];
		else
			$dayPart = 0;

		$date->setYear($yearPart);
		$date->setMonth($monthPart);
		$date->setDay($dayPart);
	}
		
	$field_order = strtoupper($field_order);

    $html_result = $month_result = $day_result = $year_result = "";

    if ($display_months) {
		if(isset($emptystring))
		{
			$month_names = array($emptystring);
		}
		else
		{
	        $month_names = array("");
		}

        for ($i = 1; $i <= 12; $i++)
            $month_names[] = strftime($month_format, mktime(0, 0, 0, $i, 1, 2000));

        $month_result .= '<select name=';
        if (null !== $field_array){
            $month_result .= '"' . $field_array . '[' . $prefix . 'Month]"';
        } else {
            $month_result .= '"' . $prefix . 'Month"';
        }
        if (null !== $month_size){
            $month_result .= ' size="' . $month_size . '"';
        }
        if (null !== $month_extra){
            $month_result .= ' ' . $month_extra;
        }
        if (null !== $all_extra){
            $month_result .= ' ' . $all_extra;
        }
        $month_result .= '>'."\n";
		
		if($time == 0)
			$mTime = 0;
		else
			$mTime = $date->getMonth();//strftime("%m", $time);
		
		$month_result .= smarty_function_html_options(array('output'     => $month_names,
                                                            'values'     => range(0, 12),
                                                            'selected'   => $mTime,
                                                            'print_result' => false),
                                                      $smarty);
        $month_result .= '</select>';
    }

    if ($display_days) {
		$days = range(1, 31);
		
		array_unshift($days, "");
		
        for ($i = 1; $i < count($days); $i++)
            $days[$i] = sprintf($day_format, $days[$i]);

        $day_result .= '<select name=';
        if (null !== $field_array){
            $day_result .= '"' . $field_array . '[' . $prefix . 'Day]"';
        } else {
            $day_result .= '"' . $prefix . 'Day"';
        }
        if (null !== $day_size){
            $day_result .= ' size="' . $day_size . '"';
        }
        if (null !== $all_extra){
            $day_result .= ' ' . $all_extra;
        }
        if (null !== $day_extra){
            $day_result .= ' ' . $day_extra;
        }
        $day_result .= '>'."\n";
		
		if($time == 0)
			$dTime = 0;
		else
			$dTime = $date->getDay();//strftime("%d", $time);
		
		$day_result .= smarty_function_html_options(array('output'     => $days,
                                                          'values'     => range(0, 31),
                                                          'selected'   => $dTime,
                                                          'print_result' => false),
                                                    $smarty);
        $day_result .= '</select>';
    }

    if ($display_years) {
        if (null !== $field_array){
            $year_name = $field_array . '[' . $prefix . 'Year]';
        } else {
            $year_name = $prefix . 'Year';
        }
        if ($year_as_text) {
            $year_result .= '<input type="text" name="' . $year_name . '" value="'.strftime('%Y', $time).'" size="4" maxlength="4"';
            if (null !== $all_extra){
                $year_result .= ' ' . $all_extra;
            }
            if (null !== $year_extra){
                $year_result .= ' ' . $year_extra;
            }
            $year_result .= '>';
        } else {
            $years = range((int)$start_year, (int)$end_year);

            if ($reverse_years) {
                rsort($years, SORT_NUMERIC);
            }
			
			array_unshift($years, "");
			$yearsoptions = $years;
			$yearsvalues = $years;
			if(isset($emptystring))
			{
				$yearsoptions[0] = "$emptystring";
				$yearsvalues[0] = "";
			}

            $year_result .= '<select name="' . $year_name . '"';
            if (null !== $year_size){
                $year_result .= ' size="' . $year_size . '"';
            }
            if (null !== $all_extra){
                $year_result .= ' ' . $all_extra;
            }
            if (null !== $year_extra){
                $year_result .= ' ' . $year_extra;
            }
            $year_result .= '>'."\n";
			if($time == 0)
				$timeString = "";
			else
				$timeString = $date->getYear();//strftime("%Y", $time);
			
			$year_result .= smarty_function_html_options(array('output' => $yearsoptions,
                                                               'values' => $yearsvalues,
                                                               'selected'   => $timeString,
                                                               'print_result' => false),
                                                         $smarty);
            $year_result .= '</select>';
        }
    }

    // Loop thru the field_order field
    for ($i = 0; $i <= 2; $i++){
      $c = substr($field_order, $i, 1);
      switch ($c){
        case 'D':
            $html_result .= $day_result;
            break;

        case 'M':
            $html_result .= $month_result;
            break;

        case 'Y':
            $html_result .= $year_result;
            break;
      }
      // Add the field seperator
      $html_result .= $field_separator;
    }

    if ($print_result)
        print $html_result;
    else
        return $html_result;
}

/* vim: set expandtab: */

?>
