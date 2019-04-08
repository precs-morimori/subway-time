<?php
/**
 * 東京メトロの時刻表を配列に出力
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($argv[1])) {
    echo '第一引数にメトロ時刻表のURLを指定して下さい。';
    exit(1);
}

$station_url = $argv[1];

$output_path = sprintf('%s/list.php', __DIR__);

libxml_use_internal_errors(true);

$depertures = [];

$resource = curl_init();
curl_setopt($resource, CURLOPT_HEADER, false);
curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
curl_setopt($resource, CURLOPT_BINARYTRANSFER, true);
curl_setopt($resource, CURLOPT_URL, $station_url);
curl_setopt($resource, CURLOPT_SSLVERSION, 1);

$html = curl_exec($resource);
$dom  = new DOMDocument();
$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$xpath = new DOMXPath($dom);

$timetable_records = $xpath->query('.//table[contains(@class, "v2_tableTimeTableData")] /tr');
$hour_trans        = [0 => '24', 1 => 25, 2 => 26];
$hour_trans_keys   = array_keys($hour_trans);

foreach ($timetable_records as $record) {
    $th_tag    = $xpath->query('.//th', $record);
    $hour      = '';
    $hour_text = $th_tag->item(0)->textContent;
    if (array_key_exists($hour_text, $hour_trans_keys)) {
        $hour = $hour_trans[$hour_text];
    } else {
        $hour = sprintf('%02d', $hour_text);
    }

    $minute_a_tags = $xpath->query('.//td /div /ul /li /span /a', $record);
    foreach ($minute_a_tags as $a_tag) {
        $minute       = $a_tag->textContent;
        $depertures[] = sprintf('%s:%s', $hour, $minute);
    }
}

curl_close($resource);

if (!empty($depertures)) {
    file_put_contents($output_path, print_r($depertures, true));

    // 破損しないよう一度出力してからリネーム
    $output_string = sprintf('<?php%sreturn %s;%s', PHP_EOL, var_export($depertures, true), PHP_EOL);
    
    $tmp_file_path = sprintf('%s.%s', $output_path, getmypid());
    file_put_contents($tmp_file_path, $output_string);
    rename($tmp_file_path, $output_path);
}

echo sprintf('出力成功%s%s', PHP_EOL, $output_path) . PHP_EOL;

exit(0);
