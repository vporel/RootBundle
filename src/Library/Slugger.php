<?php
namespace RootBundle\Library;

/**
 * @author Vivian NKOUANANG (https://github.com/vporel) <dev.vporel@gmail.com>
 */
class Slugger{

    /**
     * @param string $text
     * @param ?string $divider
     */
    public static function slug($text, string $divider = '-'): string
    {
        $text = preg_replace('~[éèêë]~u', "e", $text);
        $text = preg_replace('~[ïî]~', "i", $text);
        $text = preg_replace('~[àâä]~', "a", $text);
        $text = preg_replace('~[ÿ]~', "y", $text);
        $text = preg_replace('~[ôö]~', "o", $text);
        $text = preg_replace('~[ûü]~', "u", $text);

        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * @param string[] $array
     * @param ?string $divider
     */
    public static function slugArray($array, string $divider = '-'){
        return array_map(function($text) use ($divider) { return self::slug($text, $divider); }, $array);
    }
}