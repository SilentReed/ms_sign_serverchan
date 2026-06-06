<?php if (!defined('SYSTEM_ROOT')) { die('Insufficient Permissions'); }
global $m;

// 保存设置
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ms_serverchan_save'])) {
    option::uset('ms_serverchan_enable', intval($_POST['ms_serverchan_enable'] ?? 0));
    option::uset('ms_serverchan_sendkey', trim($_POST['ms_serverchan_sendkey'] ?? ''));
    option::uset('ms_serverchan_time', trim($_POST['ms_serverchan_time'] ?? ''));
    echo '<div class="alert alert-success">设置已保存</div>';
}

// 测试推送
$test_result = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ms_serverchan_test'])) {
    $sendkey = trim($_POST['ms_serverchan_sendkey'] ?? '');
    if (empty($sendkey)) {
        $test_result = '<div class="alert alert-danger">请先填写 SendKey</div>';
    } elseif (!preg_match('/^sctp(\d+)t/', $sendkey, $matches)) {
        $test_result = '<div class="alert alert-danger">SendKey 格式错误，无法提取 uid</div>';
    } else {
        $uid = $matches[1];
        $url = "https://{$uid}.push.ft07.com/send/{$sendkey}.send?" . http_build_query([
            'title' => '测试推送',
            'desp'  => "### ✅ Server酱通知测试成功\n\n" .
                        "- 用户: " . (option::uget('name') ?? '未知') . "\n" .
                        "- 时间: " . date('Y-m-d H:i:s') . "\n" .
                        "- 来源: 贴吧云签到插件",
            'tags'  => '测试',
            'short' => '贴吧签到插件测试',
        ]);
        $response = @file_get_contents($url);
        if ($response !== false) {
            $result = json_decode($response, true);
            if (isset($result['code']) && $result['code'] == 0) {
                $test_result = '<div class="alert alert-success">✅ 推送成功！请检查 Server酱 App</div>';
            } else {
                $test_result = '<div class="alert alert-danger">❌ 推送失败: ' . htmlspecialchars($result['message'] ?? '未知错误') . '</div>';
            }
        } else {
            $test_result = '<div class="alert alert-danger">❌ 请求失败，请检查网络</div>';
        }
    }
}

$enable = option::uget('ms_serverchan_enable');
$sendkey = option::uget('ms_serverchan_sendkey');
$time = option::uget('ms_serverchan_time');
?>

<?php echo $test_result; ?>

<form method="post">
<table class="table table-hover">
<thead><tr><th style="width:30%">设置项</th><th>值</th></tr></thead>
<tbody>
<tr><td>开启签到Server酱通知</td>
<td>
    <input type="radio" name="ms_serverchan_enable" value="1" <?php if ($enable == 1) echo 'checked'; ?>> 是&nbsp;&nbsp;&nbsp;
    <input type="radio" name="ms_serverchan_enable" value="0" <?php if ($enable != 1) echo 'checked'; ?>> 否
</td>
</tr>
<tr><td>Server酱 SendKey</td>
<td>
    <input type="text" class="form-control" name="ms_serverchan_sendkey" value="<?php echo htmlspecialchars($sendkey); ?>" placeholder="sctp606ta-xxxxx...">
    <span class="help-block">从Server酱SendKey页面获取，uid会自动提取</span>
</td>
</tr>
<tr><td>推送时间</td>
<td>
    <input type="time" name="ms_serverchan_time" value="<?php echo htmlspecialchars($time); ?>">
</td>
</tr>
</tbody>
</table>
<button type="submit" name="ms_serverchan_save" class="btn btn-success">保存设置</button>
<button type="submit" name="ms_serverchan_test" class="btn btn-info">测试推送</button>
</form>
