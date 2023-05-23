<?php

use PHPHtmlParser\Dom;

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}

function greetOfNow($date = null)
{
    $date = $date ?: now();
    $hour = $date->format('H');
    if ($hour < 12) {
        return 1;
    }
    if ($hour < 18) {
        return 2;
    }
    return 3;
}

function greetOfNowCn($date = null)
{
    $r = greetOfNow($date);
    $l = [
        1 => '早上',
        2 => '下午',
        3 => '晚上',
    ];
    return data_get($l, $r);
}

function isUrl($url)
{
    $urlParse = parse_url($url);

    if (isset($urlParse['host']) && isset($urlParse['query'])) {
        parse_str($urlParse['query'], $get_array);
        return implode('-', array_keys($get_array));
    }
    return false;
}


function tableToObject($data)
{
    $result = [];
    $keys = null;
    foreach ($data as $item) {
        if (!$keys) {
            $keys = $item;
        } else {
            $arr = [];
            foreach ($item as $index => $value) {
                if (is_numeric($index))
                    $arr[$keys[$index]] = $value;
                else {
                    $arr[$index] = $value;
                }
            }
            $result[] = $arr;
        }
    }

    return $result;
}

function parserHtmlTableToObject($body, $tableKey = 'table', $valueType = "innerHTML")
{
    $data = _parserHtmlTable($body, $tableKey, $valueType);

    return tableToObject($data);
}

function _parserHtmlTable($body, $tableKey = 'table', $valueType = "innerHTML")
{
    $result = [];
    $dom = new Dom;
    $dom->loadStr($body);
    $table = $dom->find($tableKey);

    if (count($table)) {
        $table = $table[count($table) - 1];

        $trs = $table->find('tr');
        foreach ($trs as $tr) {
            $tds = $tr->find('td,th');
            if (count($tds) <= 1) {
                continue;
            }
            $arr = [];
            foreach ($tds as $td) {
                $input = @$td->find('input');
                if (count($input)) {
                    $arr += $input->getAttributes() ?? [];
                } else {
                    $value = trim($td->innerHTML);
                    if (strtolower($valueType) === 'innertext') {
                        $value = strip_tags($value);
                    }
                    $arr[] = $value;
                }
            }
            $result[] = $arr;
        }

    }

    return $result;
}


function getProvince(): \Illuminate\Support\Collection
{
    $data = \Illuminate\Support\Facades\Storage::disk('public')->get("province.json");
    return collect($data ? json_decode($data, true) : []);
}

function getCity($province): \Illuminate\Support\Collection
{
    $data = \Illuminate\Support\Facades\Storage::disk('public')->get("city.json");
    if ($data) {
        $data = json_decode($data, true);
        $province = substr($province, 0, 2);
        return collect($data)->filter(function ($item) use ($province) {
            return \Illuminate\Support\Str::startsWith($item['code'], $province);
        })->values();
    } else
        return collect([]);
}

function getDistrict($city): \Illuminate\Support\Collection
{
    $data = \Illuminate\Support\Facades\Storage::disk('public')->get("district.json");
    if ($data) {
        $data = json_decode($data, true);
        $city = substr($city, 0, 4);
        return collect($data)->filter(function ($item) use ($city) {
            return \Illuminate\Support\Str::startsWith($item['code'], $city);
        })->values();
    } else
        return collect([]);
}

function filter_filename($name)
{
    // remove illegal file system characters https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
    $name = str_replace(array_merge(
        array_map('chr', range(0, 31)),
        array('<', '>', ':', '"', '/', '\\', '|', '?', '*')
    ), '', $name);
    // maximise filename length to 255 bytes http://serverfault.com/a/9548/44086
    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $name = mb_strcut(pathinfo($name, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($name)) . ($ext ? '.' . $ext : '');
    return $name;
}

function deleteCacheFiles()
{
    try {
        $files = \Illuminate\Support\Facades\Storage::disk('public')->allFiles('cache_files');
        \Illuminate\Support\Facades\Storage::disk('public')->delete($files);
    } catch (\Exception $exception) {
        \Illuminate\Support\Facades\Log::error($exception->getMessage());
    }
}
