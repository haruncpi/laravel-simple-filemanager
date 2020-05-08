<?php
if(!function_exists('isAssoc')){
    function isAssoc($arr)
    {
        if (is_array($arr)) {
            return array_keys($arr) !== range(0, count($arr) - 1);
        } else {
            return false;
        }
    }
}

