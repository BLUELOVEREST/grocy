# Grocy Stitch UI 优化交接

本文记录 Eric Grocy 界面优化过程中用到的 Stitch 项目信息、页面 ID、参考文件和当前代码落点，避免后续继续优化时找不到对应设计稿。

## Stitch 项目

当前 Grocy UI 优化使用的 Stitch 项目：

```text
Project ID: 1343362168483210173
```

设计风格参考项目：

```text
Reference Project ID: 5735290104069377410
Reference Title: Eric Home Assistant Dashboard Redesign
```

参考风格要点：

- 浅色 Apple-like 控制台风格
- 背景：`#F5F7FA`
- 主色：`#2F80ED`
- 成功/状态色：`#34C759`
- 强调色：`#FF9F0A`
- 面板白底，弱边框，轻阴影
- 侧边栏精简，只放常用入口
- 不做营销页，不做大面积装饰渐变
- Dashboard 是实际操作入口，不是介绍页

## 当前使用的 Stitch 页面

当前 Dashboard 实现参考的是下面这个 Stitch screen：

```text
Screen Title: Operational Dashboard Overview
Screen ID: 45c7e13c3b464fc5a6231a2fbf9fb9ce
Resource: projects/1343362168483210173/screens/45c7e13c3b464fc5a6231a2fbf9fb9ce
Device Type: DESKTOP
Width: 2560
Height: 2048
```

这个页面是基于原 Dashboard 重新生成的操作型首页，包含：

- 新版左侧侧边栏
- 全局产品搜索
- 购物清单
- 库存提醒
- 快捷操作
- 最近操作
- 常用入口

## Stock Overview 设计稿

当前 Stock 页面优化基于 Stitch 项目里已有的 `Stock Overview` 页面继续生成，没有直接覆盖原页面。

输入页面：

```text
Screen Title: Stock Overview
Screen ID: 04acec6e699347d7a9e434f4815f33de
Resource: projects/1343362168483210173/screens/04acec6e699347d7a9e434f4815f33de
```

新生成页面：

```text
Screen Title: Stock Overview (Redesign)
Screen ID: 58c96dd8bef14985b6b8849db39930ea
Resource: projects/1343362168483210173/screens/58c96dd8bef14985b6b8849db39930ea
HTML File: projects/1343362168483210173/files/96d495d9297940dcaaecf06ce99f5ace
Screenshot File: projects/1343362168483210173/files/8233bde3cb174b13a44b589fdef6f72a
```

本地设计基准文件：

```text
docs/stitch/stock-overview-redesign.html
```

这个页面的设计目标：

- 和 `/eric-dashboard` 使用统一侧边栏、色彩、面板、间距和字体层级
- 左侧导航中 `Stock` 为当前激活项
- 页面主体保留库存页的实际工作流，不做不可用的展示模块
- 顶部提供库存状态摘要和常用动作
- 中部提供搜索、筛选和库存表格
- 表格字段覆盖产品名、数量、位置、保质期、状态、最近购买/价格和操作
- 状态摘要覆盖低库存、临期、过期、缺货等 Grocy 现有库存概念

## 本地保存的 Stitch 文件

已将 Stitch 生成的 HTML 下载到仓库中作为像素复刻基准：

```text
docs/stitch/operational-dashboard-overview.html
docs/stitch/stock-overview-redesign.html
```

这个文件只作为设计参考，不直接在 Grocy 运行时加载。实现时需要把其中的布局、颜色、间距和模块结构转换成 Grocy 本地 Blade/CSS/JS。

## 当前 Grocy 实现落点

当前代码没有替换 Grocy 原有 Dashboard/Stock/Shopping List/Food Library 页面，而是新增了独立入口：

```text
/eric-dashboard
```

相关代码：

```text
routes.php
controllers/SystemController.php
views/layout/default.blade.php
views/ericdashboard.blade.php
public/viewjs/ericdashboard.js
```

实现原则：

- 原有 Grocy 页面继续可用
- `/eric-dashboard` 内部渲染新版侧边栏
- `/eric-dashboard` 页面隐藏旧 Grocy 全局导航
- 其他页面仍使用 Grocy 原来的侧边栏
- Dashboard 上的按钮必须跳转到现有可用功能，不放不可用的假按钮

## 当前页面行为

`/eric-dashboard` 当前已经实现：

- 新版侧边栏从页面最左侧开始，不再保留旧侧边栏黑色/空白区域
- 搜索区只在回车或点击搜索按钮时执行搜索
- 搜索范围当前是 Grocy 本地所有 active products
- 搜索结果点击进入 `/product/{id}`
- 顶部快捷按钮跳到 `/purchase`、`/consume`、`/inventory`、`/product/new`
- 购物清单模块读取第一张购物清单，条目点击进入现有购物项编辑弹窗
- 库存提醒复用现有 StockService 的缺货、临期、过期数据
- 最近操作复用现有库存流水视图
- 常用入口跳到食品库、产品管理、菜谱、入库、出库、盘点

## 临时本地测试环境

当前开发验证时使用旧 Eric Grocy 镜像加 bind mount 当前改动文件的方式启动临时容器：

```text
Container: eric-grocy-dashboard-bind-test
URL: http://192.168.200.118:18081/eric-dashboard
Image used for bind test: 192.168.200.101:54453/zhangzhicheng/eric-grocy:v4.6.0-eric.13
Data path: /tmp/eric-grocy-dashboard-data
```

这个容器只是临时测试，不代表最终发布镜像。最终发布仍需要提交代码、打 tag、触发镜像构建。

## 已做验证

当前已执行过的验证：

```bash
php -l controllers/SystemController.php
php -l routes.php
php -l views/ericdashboard.blade.php
node --check public/viewjs/ericdashboard.js
git diff --check
curl http://127.0.0.1:18081/eric-dashboard
curl http://127.0.0.1:18081/viewjs/ericdashboard.js
curl http://127.0.0.1:18081/stockoverview
```

验证结果：

- `/eric-dashboard` 返回 `200`
- `/viewjs/ericdashboard.js` 返回 `200`
- 原 `/stockoverview` 返回 `200`
- 容器日志没有 PHP fatal error

## 后续继续优化建议

后续继续做 UI 优化时，优先保持这个策略：

- 先只优化 `/eric-dashboard`
- 不直接替换 Grocy 原页面
- 新增聚合入口，按钮跳转到旧功能页
- 每次从 Stitch 更新设计时，先下载对应 screen HTML 到 `docs/stitch/`
- 文档中补充新的 Stitch screen ID 和本地基准文件

如果后续要把新版侧边栏推广到所有页面，需要单独设计一个全局 layout 方案，不要直接在 `views/layout/default.blade.php` 大范围替换。
