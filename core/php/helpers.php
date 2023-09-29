<?php

class Helpers{

    public static function isNullOrEmpty($input)
    {
        return (!isset($input) || trim($input)==='');
    }
}

?>
