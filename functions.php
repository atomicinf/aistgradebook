<?php
/**************************
AIStGradebook version 1.0.1
Copyright (C) 2009 Atomic Inferno Studios

functions.php
Common functions usable anywhere.

WARNING: This is draft quality code.
Do not use in a production environment.
**************************/

/*
 * Recursively unescapes all characters in an array.
 */

function strip_slashes_recursive($input)
{
    if (is_string($input))
    {
        return stripslashes($input);
    }
    if (is_array($input))
    {
        foreach($input as $i => $value)
        {
            $input[$i] = strip_slashes_recursive($value);
        }
    }
    return $input;
}

/*
 * Finds the median of an array of numbers.
 */

function array_median($arr) {
	sort($arr);

	$n = count($arr);
	$h = intval($n / 2);

	if($n % 2 == 0) {
		$median = ($arr[$h] + $arr[$h-1]) / 2;
	} else {
		$median = $arr[$h];
	}
	return $median;
}

/*
 * Finds the standard deviation of an array of numbers.
 */

function array_stddev($array) {
	sort($array);
	return stats_standard_deviation($array);
}

?>
