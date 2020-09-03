<?php
/**
 * 使用递归来处理
 * @params array $array
 */
function array_format(array &$array)
{
    if ($array) {
        $array = array_values($array);
        foreach ($array as &$item) {
            if (isset($item['children'])) {
                $item['children'] = format($item['children']);
            }
        }
    }
    return $array;
}
