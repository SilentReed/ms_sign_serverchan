# 贴吧签到 Server酱 通知插件

用于[百度贴吧云签到](https://github.com/MoeNetwork/Tieba-Cloud-Sign)平台，每日签到后通过 Server酱 推送通知。

基于 [ms_sign_serverchan](https://github.com/Yuuuuu0/ms_sign_serverchan) 改造，适配 Server酱 系列 API **极简风格**。

## 极简推送

Server酱 API 仅需在浏览器中输入 URL 即可发送推送：

```
https://<uid>.push.ft07.com/send/<sendkey>.send?title=标题&desp=内容
```

- **无需 POST Body**，GET 请求即可
- **uid 自动提取**：从 SendKey 中正则匹配 `/^sctp(\d+)t/`
- 支持 Markdown 格式正文（`desp` 参数）

## 使用说明

1. 将 `ms_sign_serverchan` 文件夹上传到贴吧云签到的 `plugins/` 目录
2. 在后台插件管理中安装并启用
3. 在个人设置中：
   - 开启签到Server酱通知
   - 填写 **SendKey**（从 Server酱 SendKey 页面获取）
   - 选择通知时间

## 文件结构

```
ms_sign_serverchan/
├── ms_sign_serverchan.php   # 插件设置页面（SendKey、通知时间）
├── send.php                  # 定时任务：查询签到结果并推送
├── callback.php              # 注册/注销定时任务
└── README.md
```

## 与原版 Bark 插件的区别

| 项目 | Bark 版 | Server酱 版 |
|------|---------|------------|
| 推送方式 | POST JSON | GET URL（极简） |
| 配置项 | URL + Key + 时间 | SendKey + 时间 |
| uid | 不需要 | 自动从 SendKey 提取 |
| 通知内容 | 纯文本 | Markdown 表格 |

## Server酱 API 参考

- 接口：`https://<uid>.push.ft07.com/send/<sendkey>.send`
- 方法：GET / POST 均支持
- 参数：`title`（标题）、`desp`（正文，支持 Markdown）、`tags`（标签）、`short`（简短描述）
