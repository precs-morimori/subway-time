# subway-time
コンソールでスクリプトを実行すると直近N件(第一引数で指定可能)の発車時刻と  
発車時刻までの残り時間を表示してくれるだけのツール。  
駅のホームで無駄に電車を待つのが嫌なので作成。

![](image/subway-time.png)

## 設定手順

### 手動追加
list.php に時刻を定義 (list.php.sample をコピーすると良いです)  
0時以降は24時、25時といった表記で。
```
<?php
return [
    '05:15',
    '05:27',
    '05:38',
    ︙
    // 0時以降は24時、25時表記で
    '24:06',
    '24:20',
];
```

### 東京メトロであれば下記URLで出力できる(かもしれない)
```
# 第一引数に東京メトロ時刻表のURLを指定
php output_tokyo_metro_list.php https://www.tokyometro.jp/station/yushima/timetable/chiyoda/a/index.html
```

## 実行
```
// デフォルト(引数無し)では直近最大10件表示
php get_subway_time.php

// 直近最大8件表示
php get_subway_time.php 8
```

## alias設定
`~/.bashrc` に alias を追加しておくと楽です。  
### 設定例
```
# 例(1秒ごとに更新させるためにwatchコマンドで指定)
alias yushima='watch -n 1 "php /インストールパス/get_subway_time.php"'
```

### 読み込み
```
source ~/.bashrc
```
