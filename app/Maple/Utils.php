<?php

namespace sqless\Maple;

class Utils {

    public static function logToFile($tag, string $content) {
        file_put_contents("C:/Users/Morgan/Desktop/laravel_log.txt", "[$tag] " . date("Y-m-d H:i:s") . " - " . $content . "\r\n", FILE_APPEND);
    }

    public static function log($content) {
        self::logToFile("LOG", $content);
    }

    public static function logError($errorMessage) {
        file_put_contents("C:/Users/Morgan/Desktop/sqless_errors.txt", "[ERROR] " . date("Y-m-d H:i:s") . " - " . $errorMessage . "\r\n", FILE_APPEND);
    }

    public static function logJson($obj_or_array) {
        self::logToFile("LOG", json_encode($obj_or_array));
    }

    public static function str_starts_with($subject, $needle) {
        return strpos($subject, $needle) === 0;
    }

    public static function str_ends_with($haystack, $needle) {
        $length = strlen($needle);
        return $length === 0 || (substr($haystack, -$length) === $needle);
    }

    public static function str_contains($string, $value) {
        return strpos($string, $value) !== false;
    }

    public static function replace_first($find, $replace, $subject) {
        return implode($replace, explode($find, $subject, 2));
    }

    public static function str_is_empty($str) {
        return is_string($str) && strlen($str) === 0;
    }

    public static function unquote($str) {
        return str_replace('`', '', $str);
    }

    public static function removerCharEn($string, $indice) {
        return substr($string, 0, $indice++) . substr($string, $indice, strlen($string));
    }

    public static function replace_last($search, $replace, $subject) {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

}

