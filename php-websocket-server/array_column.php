<?php
/**
* If php version is older then 5.5.0, self defined function array_column is needed.
* (source: https://www.php.net/manual/en/function.array-column)
*/
if(!function_exists("array_column")) {

    function array_column($array,$column_name) {

        return array_map(function($element) use($column_name){return $element[$column_name];}, $array);

    }

}
?>