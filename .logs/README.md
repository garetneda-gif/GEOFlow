# 瑞幸 GEO 智能内容运营中台

## 2026-07-17 22:47 — 项目快照

- 上游：`https://github.com/yaojingang/GEOFlow`，目标分支：`feat/luckin-geoflow-theme`。
- 目标：保留 GEOFlow 后端能力，以 Blade/CSS/少量现有 JavaScript 完成瑞幸品牌化概念验证。
- 技术栈：PHP 8.3 / Laravel 12 / Blade / Tailwind 本地运行脚本 / Vite；数据库默认 PostgreSQL，测试使用 SQLite。
- 启动入口：`php artisan serve --host=127.0.0.1 --port=18080`；前台 `/`，后台 `/geo_admin/login`。
- 外部依赖按 `composer.json` / `package.json` 安装；凭据只保存在本机 `.env`，不进入仓库。
