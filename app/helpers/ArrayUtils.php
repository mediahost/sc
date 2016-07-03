<?php

namespace App;


class ArrayUtils {
    
    
    public static function searchByProperty($array, $prop, $val) {
        foreach ($array as $item) {
            if ($item->$prop == $val) {
                return $item;
            }
        }
    }
}
