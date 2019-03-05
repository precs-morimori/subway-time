<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 直近最大何件の電車を表示するか
$check_train_cnt = 10;

// 時刻表リスト
$list_path = sprintf('%s/list.php', __DIR__);

// 第1引数があれば
if (isset($argv[1])) {
    if (!preg_match('/^[1-9]\d*$/', $argv[1])) {
        echo '第1引数(最大表示件数)は1以上の数値で指定お願いします。';
        exit(1);
    }
    $check_train_cnt = (int) $argv[1];
}

echo sprintf('直近最大%s件の発車時刻を表示します', $check_train_cnt) . "\n\n";

try {
    if (!file_exists($list_path)) {
        throw new Exception(sprintf("リストファイルが存在しません。\n%s", $list_path));
    }

    $list     = include $list_path;
    $today    = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    $return_list = [];
    $hit_cnt     = 0;

    foreach ($list as $key => $hour_minute) {
        $tmp = explode(':', $hour_minute);
        if (!isset($tmp[1])) {
            throw new Exception(sprintf('"分"が取得できませんでした。(リスト%s個目)', (string) ($key + 1)));
        }
        $hour   = $tmp[0];
        $minute = $tmp[1];

        // 24時以降の表記は明日
        if ((int) $tmp[0] >= 24) {
            $check_date = $tomorrow;
            $hour       = (int) $tmp[0] - 24;
        } else {
            $check_date = $today;
        }

        $target_date = sprintf('%s %s:%s:00', $check_date, $hour, $minute);
        if (strtotime($target_date) === false) {
            throw new Exception(sprintf('日付として認識できませんでした。(リスト%s個目: %s)', (string) ($key + 1), $target_date));
        }

        if (strtotime($target_date) > time()) {
            $second                    = strtotime($target_date) - time();
            $return_list[$target_date] = s2h($second);
            $hit_cnt++;
            if ($hit_cnt >= $check_train_cnt) {
                break;
            }
        }
    }

    if (!empty($return_list)) {
        echo sprintf('[現在時刻]%s', date('Y-m-d H:i:s')) . "\n\n";
        foreach ($return_list as $time => $left) {
            echo sprintf("[発車時刻]%s (残り%s)\n", $time, $left);
        }
    } else {
        echo '残念ながら本日の電車はもうありません。。';
    }
} catch (Exception $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}

/**
 * ●秒を日本語の●日●時●分●秒文字列に変換
 */
function s2h($seconds)
{
    $ret = '';
    if ((int) ($seconds) === 0) {
        return '0秒';
    }
    $days    = floor($seconds / 86400);
    $hours   = floor(($seconds / 3600) % 24);
    $minutes = floor(($seconds / 60) % 60);
    $seconds = $seconds % 60;
    if ($days > 0) {
        $ret .= (string) $days . '日';
    }
    if ($hours > 0) {
        $ret .= (string) $hours . '時間';
    }
    if ($minutes > 0) {
        $ret .= (string) $minutes . '分';
    }
    if ($seconds > 0) {
        $ret .= (string) $seconds . '秒';
    }
    return $ret;
}
