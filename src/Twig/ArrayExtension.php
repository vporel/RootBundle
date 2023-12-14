<?php
namespace RootBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ArrayExtension extends AbstractExtension{

    /**
     * Callback for Twig
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('shuffle', [$this, 'shuffle']),
            new TwigFilter('sum', [$this, 'sum']),
            new TwigFilter('product', [$this, 'product']),
            new TwigFilter('values', [$this, 'values']),
            new TwigFilter('as_array', [$this, 'asArray']),
            new TwigFilter('html_attr', [$this, 'htmlAttributes']),
        ];
    }

    /**
     * Shuffle an array
     *
     * @param array $array
     * @return int
     */
    public function shuffle($array)
    {
        if(!isset($array)) return null;
        $newArray = array_merge($array, []);
        shuffle($newArray);
        return $newArray;
    }

    /**
     * Calculate the sum of values in an array
     *
     * @param array $array
     * @return int
     */
    public function sum($array)
    {
        return isset($array) ? array_sum((array)$array) : null;
    }

    /**
     * Calculate the product of values in an array
     *
     * @param array $array
     * @return int
     */
    public function product($array)
    {
        return isset($array) ? array_product((array)$array) : null;
    }

    /**
     * Return all the values of an array or object
     *
     * @param array|object $array
     * @return array
     */
    public function values($array)
    {
        return isset($array) ? array_values((array)$array) : null;
    }

    /**
     * Cast value to an array
     *
     * @param object|mixed $value
     * @return array
     */
    public function asArray($value)
    {
        return is_object($value) ? get_object_vars($value) : (array)$value;
    }

    /**
     * Cast an array to an HTML attribute string
     *
     * @param mixed $array
     * @return string
     */
    public function htmlAttributes($array)
    {
        if (!isset($array)) {
            return null;
        }

        $str = "";
        foreach ($array as $key => $value) {
            if (!isset($value) || $value === false) {
                continue;
            }

            if ($value === true) {
                $value = $key;
            }

            $str .= ' ' . $key . '="' . addcslashes($value, '"') . '"';
        }

        return trim($str);
    }
}