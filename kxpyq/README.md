# Pyq 主题 — 详细功能总结与技术文档

> **主题名称：** Pyq  
> **作者：** 清酒  
> **版本：** 1.0.0 (2026-06-28)  
> **适配程序：** Typecho 1.3.0  
> **运行环境：** PHP 8.2 + MySQL 5.7  
> **站点地址：** https://p.qu.pw/  
> **主题目录：** `/www/wwwroot/p.qu.pw/usr/themes/pyq/`

---

## 一、主题概述

Pyq 是一款仿朋友圈风格的 Typecho 主题，以说说（短文）为核心，支持图文、音乐、视频、链接等多种内容形式。移动端优先设计，支持深色模式、PJAX 无刷新导航、LRC 歌词滚动等高级功能。

参考主题：wutoutu（同目录下），在此基础上进行了大量功能增强和 UI 优化。

---

## 二、文件结构

```
pyq/
├── index.php              # 首页模板（说说列表 + 分页 + AJAX加载更多）
├── post.php               # 文章详情页（单篇说说 + 评论区）
├── page.php               # 通用页面模板
├── page-about.php         # 关于页面（社交账号弹窗）
├── page-links.php         # 友链页面
├── page-archive.php       # 归档页面（按月分组）
├── header.php             # 公共头部（导航栏 + 封面 + 公告 + 音乐播放器）
├── footer.php             # 公共底部（弹窗公告 + 返回顶部 + JS加载）
├── comments.php           # 评论模板
├── functions.php          # 主题函数（自定义字段 + 工具函数）
├── setting.php            # 后台设置页（Tab式界面）
├── ajax.php               # AJAX接口（加载更多 + 评论提交 + 点赞）
├── music-proxy.php        # 音乐代理（本地Meting库 + 流式传输）
├── img-proxy.php          # 图片代理（防盗链处理）
├── assets/
│   ├── css/
│   │   ├── style.css      # 前台主样式（1193行）
│   │   ├── houtai.css     # 后台设置页样式（354行）
│   │   └── admin-fix.css  # 后台全局修复CSS（130行）
│   ├── js/
│   │   ├── app.js         # 前台主逻辑（1250行）
│   │   └── houtai.js      # 后台设置页逻辑（54行）
│   └── img/               # 主题图片资源
├── lib/
│   └── Meting.php         # Metowolf Meting 音乐API库
└── static/
    └── prism/             # Prism.js 代码高亮（22个文件，16种语言）
```

**总代码量：** 5266 行

---

## 三、功能特性

### 3.1 内容类型

| 类型 | 自定义字段 | 说明 |
|------|-----------|------|
| 图文 | `images`（多行文本） | 多图URL，每行一张，支持懒加载 |
| 视频 | `video_url`（单行文本） | 视频链接，支持 mp4/embed |
| 音乐 | `music_url` + `music_name` + `music_artist` + `music_cover` + `music_lrc` | 音乐卡片，支持封面+歌词 |
| 链接 | `link_url` + `link_title` + `link_desc` + `link_thumb` | 外部链接卡片预览 |
| 位置 | `location`（单行文本） | 显示位置信息 |
| 置顶 | `is_top`（单选：是/否） | 置顶文章 |

### 3.2 音乐系统

#### 顶部播放器
- 导航栏集成迷你播放器（播放/暂停 + 歌名滚动）
- 支持两种音源：
  - `bgm_url`：直接 MP3 链接（优先）
  - `netease_id`：网易云歌曲ID，通过本地 Meting 库代理获取
- 音乐代理 `music-proxy.php`：流式传输 + Range 分块请求支持

#### 音乐卡片（说说内嵌）
- 80px 高度卡片，封面背景 + 歌曲名 + 歌手 + 播放按钮
- 底部进度条（3px，hover 6px），支持拖动 seek
- LRC 歌词滚动：
  - 支持标准 LRC 格式（`[00:12.34]歌词`）
  - 支持网易云 JSON 格式（`{"t":0,"c":[{"tx":"歌词"}]}`）
  - 歌词区在卡片下方展开，背景使用封面模糊图
  - 当前行高亮（白色 15px + 发光阴影）
  - `requestAnimationFrame` 同步滚动，使用 `scrollTo` 避免页面抖动
  - 展开/收起按钮，可手动控制

### 3.3 评论系统

- AJAX 无刷新提交评论
- 必填昵称 + 邮箱（邮箱格式校验）
- 评论 @回复 功能（点击回复按钮自动填充）
- 评论邮件通知：
  - 直接调用 CommentNotifier 插件的 `refinishComment` 方法
  - 绕过异步模式（`yibu=1`），确保邮件即时发送
  - 修复 `ownerId` 为 0 的问题，从 `typecho_contents` 查询文章作者
- 评论树形渲染（`pyq_render_comments_tree`）

### 3.4 交互功能

- **点赞：** AJAX 点赞/取消，实时更新按钮状态和点赞列表
- **分享：** 8 按钮弹窗（2×4 网格）— 微信/微博/QQ/复制链接/带标题复制/更多
- **返回顶部：** 滚动 300px 后显示，平滑滚动
- **图片灯箱：** FancyBox 集成，点击图片放大查看
- **懒加载：** IntersectionObserver 图片懒加载
- **加载更多：** 滚动到底部自动加载下一页（AJAX）

### 3.5 PJAX 无刷新导航

- 全站 PJAX 实现，音乐播放不中断
- `XMLHttpRequest` + `DOMParser` 解析新页面
- `history.pushState` / `popstate` 浏览器前进后退支持
- 换页后自动重新初始化：FancyBox、Prism.js、懒加载、搜索、加载更多
- 内容区域目标：`#pyq-feed`

### 3.6 深色模式

- 跟随系统 `prefers-color-scheme` 自动切换
- CSS 变量体系（`--bg`, `--card`, `--text`, `--text2`, `--text3`, `--border` 等）
- 所有组件完整适配深色/浅色

### 3.7 代码高亮

- Prism.js 集成（16 种语言）
- 暗色主题 + 行号 + 一键复制按钮
- `pyq_parse_code()` 函数处理代码块

---

## 四、后台设置

### 4.1 设置界面

Tab 式布局（5 个标签页）：

| 标签 | 内容 |
|------|------|
| 基础设置 | 头像URL、封面图URL、封面高度、用户名、个性签名、ICP备案、说说分类slug、静态资源URL |
| 音乐设置 | 背景音乐URL、网易云歌曲ID |
| 社交菜单 | GitHub/微博/微信/QQ 链接 + 菜单项配置 |
| 功能设置 | 公告文字、公告背景色（色盘选择）、Gravatar源选择、弹窗公告 |
| 高级设置 | 自定义CSS/JS 代码注入 |

### 4.2 设置备份

- 一键备份当前所有设置
- 列出历史备份，支持一键恢复/删除
- 备份存储在 `typecho_options` 表中

### 4.3 自定义字段

文章编辑页自定义字段支持：
- textarea 高度优化（36px 起，可拖拽调整）
- 文字描述改为 placeholder（`$e->input->setAttribute`）
- Radio 按钮置顶选择（is_top）
- 所有字段支持 JS 拖拽调整宽度（text input）

---

## 五、页面模板

### 5.1 关于页面 (`page-about.php`)

- 社交账号展示（QQ/微信/GitHub/微博）
- 点击弹窗显示账号信息（QQ 弹窗、微信弹窗）
- 复制账号到剪贴板
- 弹窗函数在 `app.js` 中定义（PJAX 兼容）

### 5.2 友链页面 (`page-links.php`)

- 配合links插件使用
- 友链列表展示
- 卡片式布局

### 5.3 归档页面 (`page-archive.php`)

- 按年月分组的文章列表
- 链接格式：`/archives/slug.html`

---

## 六、技术实现细节

### 6.1 IIFE 架构

`app.js` 使用 IIFE（立即执行函数表达式）封装，所有功能通过 `pyq.xxx` 暴露到全局：

```javascript
;(function(){
  // 内部变量
  var pyq = window.pyq = {};
  // 所有功能定义...
  pyq.playCardMusic = function(btn){ ... };
  pyq.parseLrc = function(text){ ... };
  // ...
})();
```

**关键教训：** IIFE 闭合 `})();` 必须在所有 `pyq.xxx` 定义之后，否则后续代码引用 `pyq` 会 ReferenceError。

### 6.2 音乐卡片 HTML 结构

```html
<div class="pyq-music-wrap" data-bg="封面URL">
  <div class="pyq-music-card" data-src="音频URL" data-name="歌名" data-lrc="base64歌词">
    <div class="pyq-music-card-bg" style="background-image:url(...)"></div>
    <div class="pyq-music-card-left"><img src="封面"></div>
    <div class="pyq-music-card-right">
      <div class="pyq-music-card-info">
        <div class="pyq-music-card-title">歌名</div>
        <div class="pyq-music-card-artist">歌手</div>
      </div>
      <div class="pyq-music-card-play" onclick="pyq.playCardMusic(this)">▶</div>
    </div>
    <div class="pyq-music-progress" onmousedown="pyq.seekMusic(event,this)">
      <div class="pyq-music-progress-bar"></div>
      <div class="pyq-music-progress-dot"></div>
    </div>
  </div>
  <div class="pyq-lrc-bar" onclick="pyq.toggleLrc(this)">歌词 ▼</div>
  <div class="pyq-music-lrc">
    <div class="pyq-lrc-bg" style="background-image:url(...)"></div>
    <div class="pyq-lrc-overlay"></div>
    <div class="pyq-lrc-lines"></div>
  </div>
</div>
```

### 6.3 LRC 歌词解析

支持两种格式：

**标准 LRC：**
```
[00:12.34]歌词第一行
[00:15.67]歌词第二行
```

**网易云 JSON：**
```json
[{"t":0,"c":[{"tx":"作词: "},{"tx":"某某"}]},{"t":4000,"c":[{"tx":"歌词"}]}]
```

解析流程：
1. 检测首字符是否为 `{` 或 `[`
2. JSON 格式：解析数组，提取 `t`（毫秒）和 `c[].tx`（文本）
3. LRC 格式：正则 `/[(\d{1,2}):(\d{2})(?:\.(\d{1,3}))?](.*)/g` 匹配
4. 按时间排序

### 6.4 歌词同步机制

- `requestAnimationFrame` 高频循环（60fps）
- 遍历已排序的歌词数组，找到当前时间对应的行
- 高亮当前行（`.active` class）
- `scrollTo` 滚动容器到当前行位置（不用 `scrollIntoView`，避免页面抖动）
- 暂停时停止循环，恢复时重新启动

### 6.5 进度条拖动

- `mousedown/touchstart` 记录起始位置
- `mousemove/touchmove` 实时计算百分比，更新 `audio.currentTime`
- `mouseup/touchend` 清理事件监听
- `getBoundingClientRect` 计算相对位置
- 拖动中添加 `.dragging` class（白点常显 + 进度条变高）

### 6.6 邮件通知修复

三层问题修复：

1. **插件未初始化：** `ajax.php` 需调用 `Plugin::init()` 初始化插件系统
2. **ownerId 为 0：** `typecho_comments.ownerId` 全为 0，需从 `typecho_contents` 查 `authorId` 补全
3. **异步模式失效：** CommentNotifier 的 `yibu=1` 模式在 AJAX handler 中不触发，直接调用 `Plugin::refinishComment($feedback)` 绕过

### 6.7 后台 CSS 注入

通过 `admin/header.php` 直接注入 `admin-fix.css`，修复：
- select 下拉框样式（移动端 font-size 16px 防缩放）
- textarea 可拖拽（覆盖 Typecho 的 `resize: none`）
- 编辑器容器居中（覆盖 `.row { width: 100vw }`）
- 移动端自定义字段、提交按钮全宽适配

---

## 七、CSS 变量

```css
:root {
  --bg: #f0f0f0;          /* 页面背景 */
  --card: #fff;            /* 卡片背景 */
  --card2: #f5f5f5;        /* 次级卡片 */
  --text: #333;            /* 主文字 */
  --text2: #666;           /* 次级文字 */
  --text3: #999;           /* 辅助文字 */
  --border: #eee;          /* 边框 */
  --accent: #07c160;       /* 强调色（微信绿） */
  --max-w: 680px;          /* 最大宽度 */
  --font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.darkmode {
  --bg: #1a1a1a;
  --card: #242424;
  --card2: #2a2a2a;
  --text: #e5e5e5;
  --text2: #aaa;
  --text3: #777;
  --border: #333;
}
```

---

## 八、数据库交互

### 8.1 表结构

- `typecho_contents`：文章内容
- `typecho_fields`：自定义字段（`name` + `str_value` + `cid`）
- `typecho_comments`：评论
- `typecho_users`：用户
- `typecho_options`：设置项

### 8.2 关键查询

```sql
-- 获取自定义字段
SELECT name, str_value FROM typecho_fields WHERE cid = ?

-- 获取评论（带用户信息）
SELECT c.*, u.screenName FROM typecho_comments c 
LEFT JOIN typecho_users u ON c.authorId = u.uid 
WHERE c.cid = ? ORDER BY c.created ASC

-- 文章作者查询（修复 ownerId=0 问题）
SELECT authorId FROM typecho_contents WHERE cid = ?
```

---

## 九、插件依赖

| 插件 | 状态 | 用途 |
|------|------|------|
| CommentNotifier | ✅ 已激活 | 评论邮件通知 |
| Links | ✅ 已激活 | 友链管理 |
| ArticlePoster | ⚠️ 状态未知 | 文章海报生成 |

---

## 十、备份清单

| 备份名 | 时间 | 说明 |
|--------|------|------|
| pyq_backup_20260627_210104 | 06-27 21:01 | 最初完整备份 |
| pyq_backup_20260628_123511 | 06-28 12:35 | Tab式设置界面完成后 |
| pyq_backup_20260628_141400 | 06-28 14:14 | LRC功能尝试前 |
| pyq_backup_20260628_153600 | 06-28 15:36 | LRC回滚后 |
| pyq_backup_20260628_163947 | 06-28 16:39 | 代码清理后 |
| pyq_backup_20260628_173800 | 06-28 17:38 | 移动端优化后 |
| pyq_backup_20260628_181600 | 06-28 18:16 | LRC首轮修复后 |
| pyq_backup_20260628_184200 | 06-28 18:42 | LRC二轮修复后 |
| pyq_backup_20260628_185100 | 06-28 18:51 | 进度条前 |
| pyq_backup_20260628_190200 | 06-28 19:02 | 最新 |

---

## 十一、踩坑记录（关键教训）

1. **Typecho `.row` 默认 `width: 100vw`** — 导致编辑器容器溢出，需覆盖为 `width: 100%`
2. **Typecho `setAttribute()` 设在 `<li>` 上** — 需用 `$e->input->setAttribute()` 设在 `<input>` 上
3. **IIFE 闭合位置错误** — `})();` 必须在所有 `pyq.xxx` 定义之后
4. **`display:none` 的父容器** — 子元素的 `display:flex` 无效，必须同时检查父容器
5. **PJAX 内联 script 不执行** — `innerHTML` 注入的 `<script>` 不会执行，函数必须放外部 JS
6. **`height:0;overflow:hidden` 的容器** — 内部元素永远不可交互，按钮必须放外面
7. **CSS 变量不能用于伪元素 `background-image`** — 跨浏览器不兼容，用真实 DOM 更可靠
8. **`scrollIntoView` 带动页面滚动** — 容器高度动画时使用 `scrollTo` 代替
9. **单引号里 `\n` 是字面量** — 不是换行，需用双引号
10. **Typecho 插件钩子系统** — 命名空间必须是 `TypechoPlugin\PluginName\ClassName`，手动 SQL 激活不会运行构造函数

---

## 十二、性能优化

- 图片懒加载（IntersectionObserver）
- PJAX 无刷新（减少整页重载）
- CSS `will-change` 用于动画元素
- `requestAnimationFrame` 用于高频更新（歌词同步、进度条）
- 音乐代理流式传输（不缓存完整文件）
- Prism.js 按需加载（仅代码块页面）

---

_文档生成时间：2026-06-28 19:02_  
_主题总代码量：5266 行_  
_备份总数：10 个_
