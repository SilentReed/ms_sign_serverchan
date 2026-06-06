<?php
/**
 * 快速测试 Server酱 推送
 * 
 * 用法: php test.php <sendkey> [title] [内容]
 * 
 * 示例: php test.php sctp606ta-xxxxx "测试标题" "测试内容"
 * 
 * 也可以直接浏览器访问:
 * https://<uid>.push.ft07.com/send/<sendkey>.send?title=测试&desp=Hello
 */

$sendkey = $argv[1] ?? '';
$title = $argv[2] ?? '测试推送';
$desp = $argv[3] ?? '这是一条 **Server酱** 测试消息 🎉';

if (empty($sendkey)) {
    echo "用法: php test.php <sendkey> [title] [内容]\n";
    echo "\n";
    echo "SendKey 格式: sctp{uid}t-xxxxx\n";
    echo "从 Server酱 SendKey 页面获取\n";
    exit(1);
}

// 提取 uid
if (!preg_match('/^sctp(\d+)t/', $sendkey, $matches)) {
    echo "❌ SendKey 格式错误，无法提取 uid\n";
    exit(1);
}

$uid = $matches[1];
echo "uid: {$uid}\n";
echo "SendKey: {$sendkey}\n";

// 构建 URL
$url = "https://{$uid}.push.ft07.com/send/{$sendkey}.send?" . http_build_query([
    'title' => $title,
    'desp'  => $desp,
    'tags'  => '测试',
    'short' => '测试推送',
]);

echo "\n请求 URL:\n{$url}\n\n";

// GET 请求
$response = file_get_contents($url);
if ($response === false) {
    echo "❌ 请求失败\n";
    exit(1);
}

echo "响应: {$response}\n";

$result = json_decode($response, true);
if (isset($result['code']) && $result['code'] == 0) {
    echo "\n✅ 推送成功！请检查 Server酱 App\n";
} else {
    echo "\n❌ 推送失败: " . ($result['message'] ?? '未知错误') . "\n";
}
?>