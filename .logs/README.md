# 瑞幸 GEO 智能内容运营中台

## 2026-07-17 22:47 — 项目快照

- 自有 fork：`https://github.com/garetneda-gif/GEOFlow`；上游：`https://github.com/yaojingang/GEOFlow`；目标分支：`feat/luckin-geoflow-theme`。
- 目标：保留 GEOFlow 后端能力，以 Blade/CSS/少量现有 JavaScript 完成瑞幸品牌化概念验证。
- 技术栈：PHP 8.3 / Laravel 12 / Blade / Tailwind 本地运行脚本 / Vite；数据库默认 PostgreSQL，测试使用 SQLite。
- 启动入口：`php artisan serve --host=127.0.0.1 --port=18080`；前台 `/`，后台 `/geo_admin/login`。
- 外部依赖按 `composer.json` / `package.json` 安装；凭据只保存在本机 `.env`，不进入仓库。
- 瑞幸官方数据入口：`/geo_admin/knowledge-bases/luckin-mcp`；凭据为服务端 `LUCKIN_MCP_TOKEN`，只开放门店与商品四项工具。

## 2026-07-18 14:52 — 生产部署快照

- 生产地址：`https://luckin-geoflow.vercel.app`；Vercel 项目 `jikunrens-projects/luckin-geoflow`。
- PHP 使用 `vercel-php@0.7.4` 社区运行时；前端构建输出到 `public/`，Laravel 入口为 `api/index.php`。
- 生产数据库：Supabase 项目 `avpjgysjustocrqtzgus`，东京区 PostgreSQL，业务表位于专用 schema `luckin_geoflow`。
- Vercel 只承载请求与静态资源；队列使用同步模式，本地上传目录在 Serverless 环境不保证持久化。
- 生产凭据仅在 Supabase/Vercel 环境变量中保存，不进入仓库或 `.logs/`。

## 2026-07-18 16:01 — 生产连接与大陆访问边界

- Supabase 使用 6543 transaction pooler；应用保留 PDO 原生参数绑定，但禁用命名 prepared statements。
- Vercel stderr 默认保留异常首行并省略超长堆栈，避免平台截断真正根因。
- Vercel 官方不保证 `.vercel.app` 在中国大陆可用；面向大陆稳定交付需要自有域名，严格保障需另设境内或更可靠线路的部署。
