<?php
namespace Ravenly\Lib;

class Set {
    public static function get($array, $keys) {
        $keys = (array) $keys;

        return Set::getMapped($array, array_combine($keys, $keys));
    }

    public static function getMapped($array, $map) {
        $out = array();
        foreach($map as $from => $to) {
            $out[$to] = array_key_exists($from, $array) ? $array[$from] : null;
        }
        return $out;
    }
}
?>