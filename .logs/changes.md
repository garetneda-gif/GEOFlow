# 关键变更

## 2026-07-17 22:47 — 初始化项目日志

- 新增 `.logs/` 五个版本化日志文件。
- 记录项目入口、设计边界、当前进度与远端执行限制。

## 2026-07-17 23:54 — 瑞幸 GEO 演示主题主体完成

- 新增集中主题 CSS，重构后台导航、登录页、运营工作台与活动公开主题。
- 工作台新增真实最近任务查询；知识、审核、生成、分析页增强均不修改原有业务流程。
- 新增品牌契约测试，针对性 35 项测试、540 个断言已通过。

## 2026-07-18 00:13 — 浏览器验收修复与交付说明收口

- 修复 `resources/views/admin/tasks/create.blade.php:623` 无分类状态下误选布局表单导致的空引用。
- 更新 `docs/LUCKIN_DEMO_THEME.md`，记录完整测试结果、浏览器验收、已知限制与真实数据接入位置。
- 临时 QA 管理员、分类、作者和文章已精确删除，未把验收数据留在本地数据库。

## 2026-07-18 00:16 — 响应式与公开前台收口

- 修复工作台 Grid 子项在 390 像素视口下撑宽页面的问题，四档目标宽度均无页面级横向溢出。
- 默认首页 SEO、活动主题品牌标签和版权区统一为“瑞幸 AI 饮品指南”，同时保留 GEOFlow 开源适配声明。
- 完整测试 942 项、7675 个断言与生产构建通过，公开文章来源面板经浏览器临时数据验收后清理。

## 2026-07-18 00:36 — 提交前审计修正

- 公开文章不再把作者或默认品牌知识冒充可核验来源；无来源字段时明确显示“来源未记录 · 待核验”。
- 固定知识缺口面板增加演示数据标识，避免与真实最近任务、实时健康数据混淆。
- 修正桌面页脚侧栏偏移、错误列表布局，以及用户、通知和移动菜单的 ARIA 展开状态。
- 修正后完整测试 942 项、7678 个断言通过；浏览器复验无运行时错误或横向溢出。
- 补回真实失败任务、待审核内容、待向量知识与分发失败的状态化建议，避免非零告警仍显示“无阻塞”。
- 平板导航断点扩展至 1023 像素；最终完整测试 943 项、7686 个断言通过。

## 2026-07-18 00:46 — 校准交付证据计数

- 将交付文档与进度日志中的路由数量校准为 `route:list --except-vendor` 实际报告的 243 条。
- 仅修正文档证据，不改变运行时代码、测试或接口。

## 2026-07-18 09:45 — 恢复 GEOFlow 原版后台并接入瑞幸主题

- 恢复原横向导航、原 Dashboard、原登录卡片及原业务页，删除侧栏重设计、固定模拟数据与品牌故事。
- 新增 `public/css/luckin-admin-theme.css` 和官方 `public/images/luckin-coffee-logo.png`；主题只在后台 body scope 生效。
- 保留任务创建页 `[data-task-form-shell] form` 选择器修复，并新增独立回归测试。
- 相对改动前 HEAD 未修改任何公开前台视图、翻译、默认环境配置或前台主题 CSS。

## 2026-07-18 10:16 — 增强后台首页品牌首屏

- 复用官网归档 1920×300 饮品横幅，将原 Dashboard 标题、副标题、刷新和新建任务包装为响应式 Hero。
- 新增样式全部限定在 `.luckin-dashboard`；后续模块内容、顺序、路由及其他后台页面不变。
- 移动端使用独立遮罩、裁切位置和纵向按钮；补充焦点环、减弱动画偏好与图片失败回退。
- 新增官方资产哈希及公开首页不加载后台横幅的回归断言。

## 2026-07-18 10:24 — 品牌化后台统一页脚

- 将原纯文字页脚包装为深蓝双层布局，加入官网归档白色瑞幸组合标识，并为窄屏提供两档响应式排列。
- 保留 GitHub、更新日志、中英文帮助、项目介绍、作者与 X 入口，以及页脚后的 Reverb/Lucide 脚本。
- 新增官方页脚资产哈希、后台渲染和公开前台不引用该资产的回归断言。

## 2026-07-18 11:03 — 接入瑞幸官方 MCP 商品能力说明

- 新增安全 MCP 客户端与五分钟检测命令，覆盖会话、分页、SSE、404 重连、会话释放和四项商品工具白名单。
- Dashboard 增加官方 MCP 状态卡，仅展示本地缓存的授权与工具声明状态；公开前台不加载该模块。
- 后台页脚作者改为任济坤，联系方式改为微信 `rjk-2006`；中英葡语言包同步。
- 更新演示文档与环境变量样例，明确不接入任何订单工具，Token 不进入仓库、日志或页面。

## 2026-07-18 11:45 — 将瑞幸 MCP 接入 GEOFlow 知识闭环

- 新增超级管理员工作台 `/geo_admin/knowledge-bases/luckin-mcp`，可调用四项门店/商品能力并生成服务端短时预览。
- 官方结果经工具级字段白名单、文本净化和价格边界校验后，两阶段写入现有知识库；切片失败会补偿删除，成功后才发布为已审核。
- 任务创建、API 任务、Worker 与 RAG 召回统一限制未审核瑞幸快照，并重复外部数据不执行指令边界。
- Dashboard 的说明大卡片替换为紧凑入口；公开前台不暴露连接器。新增 13 项功能测试，完整回归 971 项、7853 个断言通过。

## 2026-07-18 12:03 — 收紧官方快照并简化后台首页

- 官方快照改为不可编辑，普通管理员不能查看、重切片或删除；补偿失败不再卡住预览令牌，Worker 只按可用知识 ID 判断 fallback。
- Dashboard 移除“当前建议动作”整栏与绿色空状态卡，Hero 删除底部蓝条、硬边框并减弱阴影；短页面页脚贴住页面底部。
- 新建 `garetneda-gif/GEOFlow` 官方 fork，本地 `origin` 和页脚仓库、更新日志、帮助文档入口均切换到该 fork。
- 完整回归 971 项、7853 个断言及 Vite 生产构建通过。

## 2026-07-18 12:22 — 封闭通用素材 API 快照绕过路径

- `MaterialLibraryService` 在修改或删除前以主键和官方来源字段识别瑞幸 MCP 快照，统一返回 403。
- 新增 API PATCH/DELETE 不可变回归，验证拒绝后正文、来源和记录仍完整。
- 最终完整回归 972 项、7860 个断言，Vite 7.3.2 生产构建、变更范围 Pint 与 `git diff --check` 均通过。

## 2026-07-18 14:52 — 增加 Vercel 与 Supabase 生产部署

- 新增 `api/index.php`、`vercel.json` 与 `.vercelignore`，在 Vercel PHP 8.3 运行时复用现有 Laravel 前端控制器和 `public/` 静态资源。
- PostgreSQL 配置支持自定义 schema、SSL 与 PDO prepared statement 策略；Vercel 通过可信代理头生成 HTTPS 链接。
- Supabase 完成 47 个迁移、首次安装种子和管理员初始化；生产队列使用同步模式，避免依赖常驻 Worker。
- 首次访问弹窗的作者、仓库和更新日志链接同步切换到 `garetneda-gif/GEOFlow` fork。

## 2026-07-18 15:10 — 生产入口收口到后台并校准页头

- `vercel.json` 根路径首先 307 跳转 `/geo_admin`，保证固定域名不再展示废弃公开前台。
- 后台桌面导航标题使用统一的 `luckin-admin-nav-link` 并上移 2px，与官方组合 Logo 完成光学对齐。
- 新增部署配置和页头标记回归断言；公开前台视图未改动。

## 2026-07-18 15:35 — 页脚入口改为轻量文字链接

- `public/css/luckin-admin-theme.css` 将页脚四个入口由两列卡片改为右对齐、自动换行的文字链接，移除边框、圆角与底色。
- `tests/Feature/AdminDashboardQuickStartTest.php` 增加页脚链接无卡片样式回归，防止品牌包装再次退回按钮卡片。

## 2026-07-18 15:43 — 按官方字标视觉中心校准页头

- `public/css/luckin-admin-theme.css` 将后台导航由上移 2px 改为下移 5px，使中文导航与官方 Logo 内的 `luckin coffee` 字标视觉中心对齐。
- `tests/Feature/AdminDashboardQuickStartTest.php` 增加导航位移回归断言，避免后续再次按透明图片盒子误判对齐。

## 2026-07-18 16:01 — 修复 Supabase 事务池间歇性 500

- `config/database.php` 在 6543 transaction pooler 上自动启用 `PDO::PGSQL_ATTR_DISABLE_PREPARES`，同时保持 `PDO::ATTR_EMULATE_PREPARES` 关闭。
- `config/logging.php` 将 stderr 异常改为保留首行、默认省略堆栈，避免 Vercel 日志只剩调用栈尾部。
- `.env.example` 与 `tests/Unit/VercelDeploymentConfigTest.php` 补充连接池和日志配置说明及回归测试。

## 2026-07-18 16:11 — 素材页知识中枢切换为瑞幸主题色

- `resources/views/admin/materials/index.blade.php` 将知识资产中枢的橙色边框、浅底、按钮、进度条与流程图标统一替换为瑞幸蓝色体系。
- `public/css/luckin-admin-theme.css` 补齐浅蓝按钮悬停态，复用 `#172991`、`#eef1ff` 与 `#cbd3ff` 品牌变量；业务风险和健康状态色保持不变。
- `tests/Feature/AdminMaterialsPagesTest.php` 增加知识中枢品牌类回归断言。

## 2026-07-18 16:21 — 首页横幅改为平面瑞幸蓝渐变

- `public/css/luckin-admin-theme.css` 移除首页横幅的 18px 圆角、悬浮阴影和咖啡图背景，改为瑞幸深蓝至亮蓝的双层渐变。
- `resources/views/admin/dashboard.blade.php` 停止渲染旧横幅图变量，保留原有标题、说明和操作入口。
- `tests/Feature/AdminDashboardQuickStartTest.php` 增加无卡片边界、渐变背景和旧横幅不再渲染的回归断言。
## 2026-07-18 16:31 — 瑞幸 MCP 改为用户 API Key

- `LuckinMcpKnowledgeController` 新增 API Key 保存与清除入口，工作台移除服务端环境变量提示，改为密码输入框和官方获取链接。
- `LuckinMcpCredentialStore` 使用现有 `ApiKeyCrypto` 加密，并将密文绑定到 `admins.luckin_mcp_api_key`；客户端按当前管理员凭据查询和缓存。
- 新增管理员凭据迁移、审计脱敏路由和回归测试；移除 `LUCKIN_MCP_TOKEN` 配置入口。
- 瑞幸 MCP 新字段使用 Laravel Encrypter（含完整性校验与历史 APP Key 支持），不改动 GEOFlow 既有凭据格式；校验失败不写 Session，候选 Key 验证失败不会覆盖旧值。
- 根据页面验收移除 API Key 输入区的说明小字，仅保留标题、输入框、保存按钮和官方获取入口。
- 同步移除“查询官方数据”卡片下的只读/订单能力说明小字。
- MCP 工作台页头与 Dashboard 入口左侧不再使用组合 Logo，统一改为官网“幸运在握”手持蓝杯视觉资产的裁切背景图。
- 将“获取 API Key”操作上移 8px，使其与“保存并检测”按钮垂直居中对齐。

## 2026-07-18 17:18 — 恢复咖啡横幅并品牌化网站设置

- `public/css/luckin-admin-theme.css` 与 `resources/views/admin/dashboard.blade.php` 恢复真实咖啡横幅，并叠加自上而下由不透明到半透明的遮罩；继续保持无圆角、无阴影。
- `resources/views/admin/site-settings/index.blade.php` 使用官网资产包中的 1920×300 咖啡场景原图作为品牌横幅，并统一模块图标为瑞幸蓝；原有网站设置结构与交互保持不变。
- `config/geoflow.php` 与 `SiteSettingsController` 提供瑞幸名称、描述、关键词和版权默认值；`config/luckin.php` 提供 `luckin_admin` 品牌别名。
- `config/geoflow.php` 将瑞幸文案放入原有站点配置回退链，显式 `SITE_*` 环境配置和数据库设置仍优先；Logo 字段继续留空，由前台主题使用自身官方资产。
- `luckin_admin` 设为默认管理员的保留登录别名，管理员创建和改名不可占用；历史同名账号也不会截获该别名。

## 2026-07-18 20:26 — 增加腾讯云生产入口与 API 404 契约

- `docker/nginx/default.conf` 将生产根路径 307 跳转到 `/geo_admin`，避免独立服务器继续展示废弃前台。
- `bootstrap/app.php` 为未知 API 路径返回统一 JSON 404，不再把 `NotFoundHttpException` 记录成内部 500。
- `tests/Unit/VercelDeploymentConfigTest.php` 与 `tests/Feature/ApiV1ContractTest.php` 覆盖生产入口及未知 API 响应。
- 腾讯云生产环境同步到 `242edf6`，完成迁移、首次安装、容器自启、端口收口和真实浏览器验收。

## 2026-07-18 21:39 — 接入瑞幸官网产品视觉库

- 新增 `LuckinProductCatalogController`、知识库路由与产品视觉库页面；支持 6 类筛选、名称/标签/说明搜索和官网详情跳转。
- `resources/data/luckin-products.json` 固化官网 33 款产品快照，`public/images/luckin-products/` 收录对应 33 张官方 480×480 产品图。
- 知识库首页增加产品视觉库入口；新增独立样式、交互脚本及 4 项功能测试，覆盖权限、入口、产品数量和本地图片完整性。

## 2026-07-18 22:06 — 精简产品视觉库头图区

- `resources/views/admin/knowledge-bases/luckin-products.blade.php` 删除头图下方的产品数量、分类数量、快照日期和官网入口整条信息区。
- `public/images/luckin-products/catalog-hero.png` 使用用户提供的 2112×474 瑞幸咖啡横幅视觉资产，替换原蓝色几何背景。
- `public/css/luckin-product-catalog.css` 增加左侧可读性遮罩，并删除已不再使用的统计条样式与响应式规则。

## 2026-07-18 22:22 — 登录页密码占位提示与生成式背景

- `resources/views/admin/auth/login.blade.php` 删除密码框下方的独立演示密码行，将经服务端校验的演示密码改为输入框内灰色占位提示。
- `public/images/luckin-admin-login-bg.webp` 使用 ImageGen 生成无文字、无伪 Logo 的瑞幸蓝登录背景，并压缩为约 41 KB WebP；真实品牌 Logo 继续使用项目既有资产。
- `AdminLoginPageTest` 覆盖背景资产、演示密码占位、灰色样式及配置过期时的默认占位回退。

## 2026-07-18 22:24 — 精简产品视觉库箭头样式

- 返回入口移除圆形描边，改为轻量纯箭头与向左位移反馈。
- 商品卡官网入口移除圆形边框，改为瑞幸蓝斜箭头与轻微右上位移反馈。
- 更新独立目录样式版本参数，避免线上继续命中旧箭头缓存。

## 2026-07-18 20:59 — 登录页增加可选演示密码

- `AdminAuthController` 只在显式开关开启、账号启用且环境密码通过数据库哈希校验时向登录页提供密码。
- 登录表单在密码框下展示演示密码；`.env.example` 与 `.env.prod.example` 保持该能力默认关闭。
- `AdminLoginPageTest` 覆盖默认隐藏、匹配时显示和配置过期时隐藏三种状态。
- 腾讯云 `.env.prod` 显式开启展示，容器重建后已在真实登录页验收；仓库示例配置仍默认关闭。

## 2026-07-18 21:58 — 顶栏滚动条不再覆盖菜单

- `resources/views/admin/partials/header.blade.php` 为桌面导航滚动区增加专用类并移除 `scrollbar-width: thin`。
- `public/css/luckin-admin-theme.css` 隐藏原生横向滚动条，但继续保留 `overflow-x-auto`、触控板和触摸滑动能力。
- `AdminDashboardQuickStartTest` 增加导航仍可滚动且三类原生滚动条均隐藏的回归断言。
- 后台布局与登录页为主题 CSS 增加基于文件修改时间的版本参数，避免 Nginx 一周 immutable 缓存阻止样式更新。

## 2026-07-18 22:11 — 补充后台页脚联合作者署名

- `lang/zh_CN/admin.php` 将后台页脚作者调整为“任济坤、铁晋鸾”，微信号保持不变。
- `AdminDashboardLuckinMcpTest` 同步更新页脚署名回归断言。
