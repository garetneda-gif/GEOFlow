# 瑞幸 GEO 智能内容运营中台 Demo 主题

> 本演示项目基于 GEOFlow 开源项目进行界面与场景适配。

## 1. 改造范围

本次改造保留 GEOFlow 的认证、路由、数据库、队列、RAG、模型调用、任务调度、审核发布与分发接口，仅调整 Blade 模板、集中 CSS、中文界面文案和演示环境默认站点文案。

- 后台：登录页、全局侧栏与顶部栏、工作台、品牌知识库、任务创建、内容审核、分发与分析入口。
- 前台：通用 `site.*` fallback 与默认活动主题 `toutiao-news-20260426` 的首页、文章、导航和页脚品牌化。
- 数据：不新增 Seeder，不写数据库；固定概念指标只在页面展示，并明确标记“演示数据”或“模拟数据”。
- 权限：普通管理员继续看不到受保护的分发工作流；超级管理员沿用原有权限判断。

## 2. 设计变量

集中主题文件为 `public/css/luckin-theme.css`，核心变量如下：

| 变量 | 值 | 用途 |
| --- | --- | --- |
| `--luckin-primary` | `#174A97` | 主按钮、链接和图表主色 |
| `--luckin-primary-dark` | `#10366F` | 侧栏、深色品牌区域 |
| `--luckin-primary-light` | `#EAF2FF` | 浅色强调背景 |
| `--luckin-accent` | `#E5484D` | 待审核、异常和重点提醒 |
| `--luckin-bg` | `#F5F7FA` | 页面背景 |
| `--luckin-card` | `#FFFFFF` | 卡片背景 |
| `--luckin-text` | `#1F2937` | 主文本 |
| `--luckin-text-secondary` | `#667085` | 次级文本 |
| `--luckin-border` | `#E5EAF0` | 边框 |
| `--luckin-success` | `#1F9D70` | 成功状态 |
| `--luckin-warning` | `#D98B18` | 警告状态 |

字体使用 `"PingFang SC", "Microsoft YaHei", "Noto Sans SC", sans-serif`。品牌标识由文字和项目现有 Lucide `coffee` 图标组成，没有抓取或捆绑非官方 Logo。

## 3. 修改过的主要文件

- `resources/views/admin/layouts/app.blade.php`：加载集中主题、统一页面壳与系统提示。
- `resources/views/admin/partials/header.blade.php`：深蓝侧栏、业务导航、当前空间、通知、帮助、语言与用户入口。
- `resources/views/admin/auth/login.blade.php`：双栏品牌登录页，保留认证字段、CSRF、首次凭据提示和记住登录逻辑。
- `resources/views/admin/dashboard.blade.php`：瑞幸 GEO 运营工作台及明确标识的概念演示模块。
- `app/Http/Controllers/Admin/DashboardController.php`：只读查询最近真实任务，复用现有场景、知识库和内容关系。
- `resources/views/admin/knowledge-bases/index.blade.php`：商品与场景知识治理示例。
- `resources/views/admin/tasks/create.blade.php`：内容生成前检查与原任务表单并存。
- `resources/views/admin/articles/index.blade.php`：品牌内容审核风险标签，不改变审核规则。
- `resources/views/admin/analytics/index.blade.php`：Agent 可见度演示指标与原有真实分析组件并存。
- `resources/views/theme/toutiao-news-20260426/*`、`resources/views/site/*`：可达的公开指南、Demo 声明和来源元数据。
- `lang/zh_CN/*`、`.env.example`：中文业务文案和演示环境默认站点文案。
- `tests/Feature/*`：锁定工作台、分析、登录与活动前台的品牌契约。

## 4. 页面与现有路由映射

| 业务名称 | 现有命名路由 | 默认地址 |
| --- | --- | --- |
| 登录 | `admin.login` | `/geo_admin/login` |
| 运营工作台 | `admin.dashboard` | `/geo_admin/dashboard` |
| 品牌知识资产 | `admin.materials.index` | `/geo_admin/materials` |
| 商品与场景知识库 | `admin.knowledge-bases.index` | `/geo_admin/knowledge-bases` |
| GEO内容任务 | `admin.tasks.index` / `admin.tasks.create` | `/geo_admin/tasks` |
| 品牌内容审核 / GEO内容资产 | `admin.articles.index` | `/geo_admin/articles` |
| 多端内容分发 | `admin.distribution.index` | `/geo_admin/distribution` |
| Agent运营洞察 | `admin.analytics` | `/geo_admin/analytics` |
| AI能力配置 | `admin.ai.configurator` | `/geo_admin/ai-configurator` |
| 公开饮品指南 | `site.home` | `/` |

后台前缀继续由 `ADMIN_BASE_PATH` 控制；所有链接仍使用原命名路由。

## 5. 演示数据说明

- 工作台六项指标、高频 Agent 需求、知识缺口和试点指标均为固定演示数据，并在所在模块标识“演示数据”。
- 分析页新增模块标识“模拟数据”，原分析筛选、漏斗和日志组件继续读取现有控制器数据。
- 商品知识示例不包含具体配方、咖啡因含量、库存、价格或优惠承诺；涉及价格、优惠、门店可售状态时只说明需实时查询。
- 页面不使用助眠、治疗、不心慌等健康功效表达。

## 6. 启动方法

推荐沿用仓库现有 Docker Compose，它会同时启动 PostgreSQL、Redis、应用、队列和调度器：

```bash
cp .env.example .env
docker compose build
docker compose up -d
```

如需直接使用 PHP 开发服务器，先在 `.env` 中配置可用的 PostgreSQL 与 Redis，再执行：

```bash
composer install
php artisan key:generate
npm install
npm run build
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=18080
```

默认前台为 `http://localhost:18080`，后台登录为 `http://localhost:18080/geo_admin/login`。使用 `php artisan serve` 时以命令输出端口为准。

## 7. 测试结果

- `composer test`：943 项通过，7686 个断言。
- 品牌与 SEO 针对性测试：40 项通过，563 个断言。
- `npm run build`：通过，Vite 7.3.2 完成生产构建。
- `php artisan view:cache`、243 条应用路由检查、测试环境全量迁移、CSS 解析和 `git diff --check`：通过。
- 变更范围 Pint：通过；仓库全量 Pint 仍报告 19 个本次改造前已存在的格式问题，未扩大修复范围。
- 浏览器验收：登录、退出、超级管理员核心页面、普通管理员权限边界、公开首页和文章页均通过；390、820、1023、1024、1280、1440 六档宽度无横向溢出或破图。820–1023 像素下导航触发器可见且可展开，1024 像素起恢复固定侧栏。
- 浏览器控制台：任务创建页无分类状态的空引用错误已修复并复验；仅保留项目既有 Tailwind Play CDN 的生产环境提示。
- 截图证据目录：`/Users/jikunren/.codex/visualizations/2026/07/17/019f70bb-0f93-7f22-807d-30d6b42d1d14/`；主要文件为 `luckin-public-mobile-390.png`、`luckin-admin-tablet-768.png`、`luckin-admin-desktop-1024.png`、`luckin-admin-desktop-1280.png`、`luckin-admin-desktop-1440.png`。

当前机器没有全局 `composer` 可执行文件，因此使用经官方校验的临时 Composer 可执行文件运行同一个 `composer test` 脚本。

## 8. 已知限制

- 当前版本没有接入瑞幸正式品牌资产、商品主数据、交易系统、优惠券、门店库存或 Agent 点单接口。
- 当前标识使用文字与 Lucide 图标组合；如进入正式品牌评审，需补充官方矢量 Logo、字体与品牌资产使用规范。
- 固定指标只服务现场叙事，不进入数据库，也不应作为经营决策依据。
- 公开前台品牌化覆盖通用 `site.*` 模板和默认活动主题 `toutiao-news-20260426`；后台若改用其他运行时主题，需在该主题内同步适配。
- 非中文语言包保留 GEOFlow 原文案；本次演示按需求只新增中文品牌文案。
- 依赖锁文件当前由 `npm audit` 报告 8 项既有风险（1 low、1 moderate、4 high、2 critical）；本次没有执行可能引发破坏性升级的自动修复。
- Tailwind Play CDN 脚本是上游已有运行方式，浏览器会给出生产环境提示；正式部署建议改为构建期 Tailwind，但本次未扩大依赖与构建改造范围。
- 本机浏览器验收使用 `APP_ENV=testing` 的临时 SQLite 文件；正式与 Docker 启动继续使用项目默认 PostgreSQL，不应把测试 SQLite 方案当作生产配置。

## 9. 后续真实数据与 Agent 点单 Demo 接入位置

- 品牌知识：通过现有知识库、企业知识和 URL 导入入口接入经核验资料。
- 实时商品、价格、库存、优惠：通过独立只读业务接口在 Agent 调用时查询，不写入静态演示文案。
- Agent 点单：在现有 API Token 和受保护接口边界内新增编排层，先返回候选方案，再由用户确认后调用交易系统。
- 可见度数据：将 AI 搜索引用、Agent 请求和菜单访问事件接入现有分析服务，再替换页面中的模拟指标。
