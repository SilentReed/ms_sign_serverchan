<?php
if (!defined('SYSTEM_ROOT')) { die('Insufficient Permissions'); }
global $m;

// 从 SendKey 提取 uid
function extract_uid_from_sendkey($sendkey) {
    if (preg_match('/^sctp(\d+)t/', $sendkey, $matches)) {
        return $matches[1];
    }
    return '';
}

// 极简推送：直接 GET 请求 URL 即可发送
// API: https://<uid>.push.ft07.com/send/<sendkey>.send?title=<title>&desp=<desp>
function send_serverchan_notification($sendkey, $title, $desp, $short = '') {
    $uid = extract_uid_from_sendkey($sendkey);
    if (empty($uid)) {
        return false;
    }

    $params = [
        'title' => $title,
        'desp'  => $desp,
        'tags'  => '贴吧签到',
    ];
    if (!empty($short)) {
        $params['short'] = $short;
    }

    $url = "https://{$uid}.push.ft07.com/send/{$sendkey}.send?" . http_build_query($params);

    // GET 请求，极简风格
    $response = file_get_contents($url);
    if ($response === false) {
        return false;
    }

    $result = json_decode($response, true);
    return isset($result['code']) && $result['code'] == 0;
}

function cron_sign_serverchan() {
    global $m;
    $currentHourMinute = date("H:i");
    $today = date("Y-m-d");

    $query = $m->query("SELECT * FROM `" . DB_NAME . "`.`" . DB_PREFIX . "users`");
    while ($fetch = $m->fetch_array($query)) {
        $name = $fetch['name'];
        $id = $fetch['id'];

        // 获取通知参数设置
        $enable = option::uget('ms_serverchan_enable', $id);
        $sendkey = option::uget('ms_serverchan_sendkey', $id);
        $time = option::uget('ms_serverchan_time', $id);

        if ($enable == 0 || empty($sendkey) || empty($time)) {
            continue; // 未开启通知或参数错误，跳过
        }

        $lastNotificationDate = option::uget('ms_serverchan_last_date', $id);
        // 判断是否已通知 & 是否到达通知时间
        if ($today == $lastNotificationDate || $currentHourMinute != $time) {
            continue;
        }

        // 构建通知内容
        $title = "贴吧签到通知";
        $desp = "### 用户: {$name}\n\n";
        $desp .= "| 贴吧 | 状态 |\n| --- | --- |\n";

        $successCount = 0;
        $failCount = 0;

        $query2 = $m->query("SELECT * FROM `" . DB_NAME . "`.`" . DB_PREFIX . "tieba` WHERE `uid` = $id");
        while ($tiebaInfo = $m->fetch_array($query2)) {
            $tiebaName = $tiebaInfo['tieba'];
            if ($tiebaInfo['status'] == 0) {
                $status = '✅ 成功';
                $successCount++;
            } else {
                $status = '❌ 失败';
                $failCount++;
            }
            $desp .= "| {$tiebaName} | {$status} |\n";
        }

        // 签到总结摘要
        $short = "{$name}: {$successCount}成功";
        if ($failCount > 0) {
            $short .= " {$failCount}失败";
        }

        // 发送 Server酱 通知
        send_serverchan_notification($sendkey, $title, $desp, $short);

        // 更新最后通知日期
        option::uset('ms_serverchan_last_date', $today, $id);
    }
    return 'Server酱通知发送完成！';
}
?>