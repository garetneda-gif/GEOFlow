# 错误记录

## 2026-07-18 14:36 — Vercel 首次构建误判 Vite 输出目录

- 现象：Vite 7.3.2 构建成功后，Vercel 因未找到默认 `dist` 目录中止。
- 根因：项目被自动识别为 Vite，但 Laravel Vite 插件把产物写入 `public/build`。
- 处理：在 `vercel.json` 显式设置 `outputDirectory: public`，保留 PHP Function 入口与静态资产路由。

## 2026-07-18 14:26 — Supabase 首次迁移落到 public schema

- 现象：专用角色创建 `migrations` 表时返回 `permission denied for schema public`。
- 根因：`config/database.php` 把 PostgreSQL `search_path` 固定为 `public`，覆盖了角色默认的专用 schema。
- 处理：增加 `DB_SCHEMA` 环境配置，迁移随后全部 47 个版本执行完成。

## 2026-07-18 12:18 — 官方快照 API 保护首次回归未拦截

- 现象：新增 API 回归期待 403，首次实际返回 200。
- 根因：通用素材查询为精简列表未选出 `source_type/source_url`，模型属性判定无法识别官方快照。
- 处理：修改为基于主键与官方来源字段的数据库存在性检查，避免精简查询绕过保护。

## 2026-07-18 11:32 — MCP 闭环首轮定向测试断言失配

- `AdminDashboardLuckinMcpTest` 仍断言已删除的巨型说明卡，需改为紧凑数据源入口。
- 任务创建测试库没有分类，Blade 按既有逻辑不渲染表单；改查 `taskForm` 与 `formOptions` View 数据。
- 服务与路由未出现语法、握手、入库或切片异常。

## 2026-07-17 22:47 — mac mini 不可达

- 现象：批处理 SSH 在 8 秒连接窗口内超时。
- 根因：当前网络路径无法到达 `mm-sh`；尚未发现本地项目问题。
- 处理：保留本机执行路径，后续验证不依赖不可达远端。

## 2026-07-17 23:31 — 只读检索中的 shell 反引号被解释

- 现象：检索可见文案时，zsh 将模式中的反引号误解释为命令，返回 `command not found`。
- 根因：检索模式未完全使用安全引号；未执行任何写操作。
- 处理：改用无反引号的固定字符串检索，Blade 编译继续通过。

## 2026-07-17 23:34 — 前台旧文案契约与新品牌文案冲突

- 现象：针对性测试中 1 项失败，旧断言仍期待 `GEOFlow Feed`。
- 根因：活动主题侧栏已按新规格中文化为“已核验知识驱动”，契约未同步。
- 处理：更新原断言为新可见行为，保留轮播、站点名与描述的其余校验。

## 2026-07-18 00:00 — 全局 Composer 与全量 Pint 基线限制

- 现象：`composer test` 无法启动，当前机器 PATH 中没有 `composer`；仓库全量 Pint 报告 19 个既有文件格式问题。
- 根因：Composer CLI 未安装到可执行路径；Pint 报错文件均不在本次改造清单中。
- 处理：使用 Composer 项目脚本的等价 Artisan 测试链，942 项测试通过；对全部变更 PHP/Blade/测试文件执行 Pint 并通过，不改动无关历史文件。

## 2026-07-18 00:08 — 任务创建页无分类状态脚本空引用

- 现象：浏览器访问任务创建页时，控制台出现对空元素调用 `addEventListener` 的错误。
- 根因：脚本使用全页第一个 `form`，无业务分类时误选中布局中的其他表单并继续初始化任务字段。
- 处理：将选择器限定为 `[data-task-form-shell] form`；删除临时分类后重新访问，页面无业务表单且控制台错误为 0。

## 2026-07-18 00:09 — 临时 QA 数据命令首次被 shell 展开

- 现象：首次 Tinker 命令中的 PHP 变量被 zsh 提前展开，产生解析错误，未写入数据。
- 根因：`--execute` 内容使用双引号包裹。
- 处理：改用 shell 单引号保护 PHP 代码，完成临时数据创建、浏览器验收和精确清理。

## 2026-07-17 23:06 — Composer 包发现缺少广播配置

- 现象：首次 `composer install` 在 package discovery 阶段因 Reverb 缺少认证键而退出。
- 根因：仓库尚无本机 `.env`，安装脚本读取了默认广播驱动。
- 处理：安装阶段显式使用 `BROADCAST_CONNECTION=log`，依赖安装与包发现随后通过。

## 2026-07-17 23:43 — 本地 SQLite 全量迁移缺少业务表

- 现象：`APP_ENV=local` 的临时 SQLite 在风险字段迁移处失败，任务、文章与知识库页面缺表。
- 根因：GEOFlow 的 SQLite 最小业务表迁移明确只用于 `testing`，正式开发默认使用 PostgreSQL。
- 处理：仅将 `/tmp` 验收库按 `APP_ENV=testing` 重建；正式启动说明继续使用 PostgreSQL。

## 2026-07-17 23:48 — 移动端工作台横向溢出

- 现象：390 像素视口下两组桌面双栏卡片保留表格最小内容宽度，页面可横向滚动。
- 根因：CSS Grid 子项默认 `min-width: auto`，内部表格把父级撑宽。
- 处理：对工作台直属 Grid 子项设置 `min-width: 0`，复验 `scrollWidth` 与 `clientWidth` 均为 390。

## 2026-07-18 00:02 — 首页 SEO 覆盖影响搜索 JSON-LD

- 现象：固定瑞幸首页标题后，1 项 JSON-LD 测试不再找到搜索词。
- 根因：品牌标题与说明错误地覆盖了搜索和分类页面的动态 SEO 文案。
- 处理：只在默认首页覆盖品牌 SEO；搜索与分类页继续使用控制器动态值，完整测试恢复全绿。

## 2026-07-18 00:14 — 前端依赖审计存在历史风险

- 现象：`npm audit` 报告 8 项风险（1 low、1 moderate、4 high、2 critical）。
- 根因：来自上游锁文件中的既有依赖链，不是本次新增包。
- 处理：记录风险，不执行可能改变依赖主版本的自动修复；生产上线前单独安排依赖升级验证。
## 2026-07-18 00:38 — Composer 测试脚本不透传文件参数

- `composer test -- <files>` 把文件参数传给了前置 `config:clear`，因此命令以参数错误退出，未执行测试。
- 针对性测试改用 `php artisan test <files>`；全量 `composer test` 的既有 942 项结果不受影响。

## 2026-07-18 00:40 — 建议动作首次引用错误路由名

- 现象：失败任务建议回归测试返回 500，提示 `admin.tasks.health-check` 未定义。
- 根因：页面路径是 `/tasks/health-check`，实际命名路由为 `admin.tasks.health`。
- 处理：改用现有命名路由并同步测试，工作台测试恢复全绿。

## 2026-07-18 09:43 — 旧任务测试文件触发 Pint 历史格式差异

- 现象：定向 `pint --test` 仅报告 `tests/Feature/AdminTasksPageTest.php` 需整文件格式化。
- 根因：该旧文件存在与当前 Pint 规则不一致的历史写法，本次仅新增两条断言。
- 处理：不批量改写旧文件；将表单绑定回归移入独立测试，避免扩大无关 diff。

## 2026-07-18 10:14 — 符号链接工作区导致自动 LSP 路径告警

- 现象：`apply_patch` 已成功落盘，但自动 LSP 诊断提示 `repo/...` 不在请求工作目录。
- 根因：工作区通过 `repo` 符号链接指向 `/Users/jikunren/Projects/瑞幸营销`，诊断器按真实路径判断边界。
- 处理：用 `git diff --check`、Pint、Blade 缓存和测试验证实际改动；没有代码诊断错误。
## 2026-07-18 08:32 — 瑞幸 MCP 定向测试首次失败
- `tests/Feature/AdminDashboardLuckinMcpTest.php` 的授权提示键未与 `authorization_required` 状态名对齐，已统一为 `authorization_required_notice`。
- `tests/Unit/LuckinMcpClientTest.php` 原先用换行构造非法会话头，被 PSR-7 先拒绝；改用可传输但业务规则禁止的内嵌制表符覆盖客户端校验。

## 2026-07-18 08:36 — MCP 分页断言类型错误
- 首次 `tools/list` 的空参数按协议使用 `stdClass`，测试回调误按数组取值；增加数组类型判断后只匹配带游标的第二页请求。

## 2026-07-18 08:39 — 全量测试发现葡语语言包缺键
- `AdminPtBrLocaleCoverageTest` 检测到新增页脚和 MCP 首页文案未显式覆盖 `pt_BR`；已补齐葡语作者、微信与全部 MCP 状态文案。

## 2026-07-18 11:08 — 当前机器未安装全局 Composer

- 现象：`composer test` 以 127 退出，提示 `command not found: composer`；前端构建同期正常完成。
- 根因：项目已有 `vendor/`，但当前 MacBook Air 没有全局 Composer 可执行文件。
- 处理：按 `composer.json` 的测试脚本等价执行 `php artisan config:clear` 与 `php artisan test`，不改项目依赖。

## 2026-07-18 12:02 — 页脚仓库断言误扫全页

- 现象：页脚链接回归测试仍命中 Dashboard 其他上游文档链接，导致 1 项失败。
- 根因：断言检查整页 HTML，而用户要求只替换页脚四个入口。
- 处理：将负向断言限定到 `<footer>` 范围，保留其他 GEOFlow 上游资源链接不变。

## 2026-07-18 14:39 — Vercel 首页 PostgreSQL 布尔查询失败

- 现象：生产 `/` 返回 500，后台登录页与 `/up` 正常；异常为 PostgreSQL `boolean = integer`。
- 根因：`PDO::ATTR_EMULATE_PREPARES=true` 把 Laravel 布尔绑定改写为 SQL 整数 `1`。
- 处理：生产恢复 PDO 原生参数绑定，直接以 6543 连接池实测首页与后台查询均成功。

## 2026-07-18 14:42 — Vercel 代理协议未被 Laravel 信任

- 现象：HTTPS 页面生成的资源与表单地址仍为 `http://`，存在浏览器混合内容风险。
- 根因：Vercel 终止 TLS 后通过转发头传递协议，应用未配置可信代理。
- 处理：增加条件式 `TRUSTED_PROXIES` 支持，生产设为 `*`；复验首页和登录页仅生成 HTTPS 地址。

## 2026-07-18 15:10 — Vercel 高层 redirects 未覆盖社区 PHP 路由

- 现象：`vercel.json` 的高层 `redirects` 声明部署成功，但生产根路径仍返回旧公开首页。
- 根因：当前社区 PHP Builder 与自定义 `routes` 组合下，根路径未进入高层重定向。
- 处理：将 `^/$` 的 307 响应放到 `routes` 首项，再处理静态资源与 Laravel 入口；`curl` 与真实浏览器均确认根地址进入后台。

## 2026-07-18 15:35 — 旧 Vercel 部署间歇性返回 500

- 现象：旧部署在 `/geo_admin/dashboard`、`/geo_admin/analytics` 与 `/geo_admin/tasks` 均记录 500，并非任务管理页单点故障。
- 根因：Vercel 保存的错误消息从 Laravel 调用栈中段开始，异常首行已被平台截断，现有日志无法可靠还原具体异常；禁止据此猜测修改业务代码。
- 处理：重新部署相同应用运行时后，用生产管理员会话连续打开并刷新任务页，页面与控制台均正常；新部署 `dpl_qqtjEdTSd1L2qggotDczLTzizoSa` 未记录新的 500。

## 2026-07-18 15:43 — 本机缺少 Pillow 无法直接量取透明像素

- 现象：用 Python 读取 Logo alpha 边界时报 `ModuleNotFoundError: No module named 'PIL'`。
- 根因：当前项目未安装 Pillow，且该依赖不属于应用运行所需。
- 处理：不新增项目依赖，改用现有 FFmpeg 的 `alphaextract` 与 `cropdetect` 量取字标边界。

## 2026-07-18 16:01 — 生产 Dashboard 再次间歇性 500

- 现象：部署 `dpl_4f4AFWniFKMo5SHB7d2tTAe7TsYj` 在同一秒记录两次 Dashboard 500；旧日志因堆栈过长仍缺少异常首行。
- 根因：生产连接到 Supabase 6543 transaction pooler，却使用 PDO 命名 prepared statements；该模式由 Supabase 官方明确标为不支持，符合跨请求偶发失败特征。
- 处理：禁用命名 prepared statements、保留原生参数绑定并压缩 stderr 异常格式；新部署并发请求和已登录 Dashboard 均无 500。

## 2026-07-18 16:01 — 浏览器直连 Vercel 出现连接重置

- 现象：真实浏览器访问生产 Dashboard 首次返回连接重置，第二次导航超时后最终恢复页面。
- 根因：Vercel 官方说明 `.vercel.app` 可能在中国大陆被限速或阻断，且没有大陆节点；这属于应用外的网络可达性问题。
- 处理：不改动用户代理设置；记录自有域名为最低成本缓解方案，稳定大陆交付需独立部署线路。

## 2026-07-18 16:01 — 配置测试未启动 Laravel 应用

- 现象：纯 PHPUnit 测试直接加载配置时缺少 `database_path()` 与 `storage_path()`。
- 根因：测试继承了 `PHPUnit\\Framework\\TestCase`，未创建 Laravel Application。
- 处理：改为继承项目 `Tests\\TestCase`，定向测试与完整回归均通过。
## 2026-07-18 16:14 — route:list 参数不兼容

- `php artisan route:list --columns=...` 在当前 Laravel 版本不支持 `--columns`，命令退出非零。
- 改用 `php artisan route:list --name=admin.knowledge-bases.luckin-mcp` 验证，5 条 MCP 路由均已注册。
## 2026-07-18 16:17 — rg 模式被解析为参数

- 搜索模式以 `->` 开头时，`rg` 将其误判为命令参数并报 `unrecognized flag`。
- 后续在模式前加入 `--`，以明确结束命令参数解析。
## 2026-07-18 16:18 — rg 的 glob 参数位置错误

- 在 `--` 后继续传 `-g '*.php'` 导致 glob 被当作路径，出现文件不存在错误。
- 正确顺序为先写 `-g '*.php'`，再写 `--` 和搜索模式；目标测试不受影响，29 项均通过。
## 2026-07-18 16:20 — composer 不在 PATH

- `composer test` 退出 127，当前 shell 未安装或未暴露 `composer` 命令。
- 改用等价的项目测试入口 `php artisan config:clear` 与 `php artisan test`；前端 `npm run build` 已成功。
## 2026-07-18 16:24 — 浏览器校验探针调用受限

- Browser 插件的页面执行环境不允许直接调用 `HTMLInputElement.checkValidity()`，返回 `not a function`。
- 改读标准 `validity.valid` 与 `validationMessage` 属性验证原生最小长度校验，不影响表单本身。
## 2026-07-18 16:25 — Tab 不提供 setViewportSize

- Browser 插件的 Tab 对象没有直接的 `setViewportSize` 方法，移动端 QA 首次调用失败。
- 改按已加载的 Browser 文档使用 Playwright 子接口调整视口后继续验证。

## 2026-07-18 16:42 — zsh 保留变量名导致状态采集失败

- 现象：完整测试完成后，汇总脚本给 `status` 赋值时被 zsh 以只读变量拒绝。
- 根因：`status` 是 zsh 的特殊只读参数，不能用作普通退出码变量。
- 处理：直接读取已生成的测试日志确认 982 项通过，后续脚本改用任务专用变量名。

## 2026-07-18 16:56 — 视觉资产断言误扫全局页头

- 现象：移除 MCP 区域小 Logo 后，两个负向测试仍命中后台全局导航中的同名 Logo 路径。
- 根因：断言扫描整页 HTML，没有限定 MCP 区域旧元素的尺寸与类名。
- 处理：改为断言 MCP 旧图片独有的尺寸和类名消失，保留全局页头品牌标识。

## 2026-07-18 17:02 — Browser 截图写入误用 Promise API

- 现象：将 Browser 截图保存到临时文件时，直接调用 `fs.writeFile` 未传回调而报错。
- 根因：当前持久 Node 会话加载的是回调版 `fs`，不是 `fs/promises`。
- 处理：用 `Promise` 包装回调式 `writeFile` 后保存截图，页面与截图内容不受影响。

## 2026-07-18 17:05 — Vercel 敏感变量无法拉取用于本地迁移

- 现象：`vercel env pull` 与 `vercel env run` 在本机得到的 `DB_PASSWORD` 为空，迁移连接报 `fe_sendauth: no password supplied`。
- 根因：Vercel 的敏感生产变量不会以明文下载到本地命令环境，但会在生产函数运行时注入。
- 处理：短暂部署仅限已登录超级管理员的迁移表单，在生产函数内执行指定迁移；随后部署干净提交并确认临时路由返回 404。

## 2026-07-18 17:16 — Browser 页面脚本禁止创建请求与 DOM

- 现象：生产 QA 中页面执行环境拒绝 `fetch()` 与 `document.createElement()`。
- 根因：Browser 插件对页面内任意请求和动态 DOM 创建做能力限制。
- 处理：使用服务器渲染的 CSRF 表单和 Browser 原生点击完成一次性迁移，没有绕过浏览器安全限制。

## 2026-07-18 17:22 — 隔离工作树缺少广播环境配置

- 现象：隔离工作树首次执行测试时，Reverb 驱动因缺少 `REVERB_APP_KEY` 无法初始化。
- 根因：Git worktree 不包含被忽略的本地 `.env`，框架回退到未配置凭据的默认广播连接。
- 处理：只读复用主工作树 `.env` 后重跑测试；不修改广播或业务代码。

## 2026-07-18 17:31 — Composer 符号链接串入主工作树代码

- 现象：隔离工作树完整回归出现 17 个瑞幸 MCP 参数不匹配，而品牌化定向测试通过。
- 根因：`vendor` 符号链接使 Composer PSR-4 基准目录解析到主工作树，加载了尚未提交的并行 MCP 改动。
- 处理：将依赖改为写时复制的隔离副本，再在隔离工作树重跑全部测试。

## 2026-07-18 17:19 — 品牌 Logo 默认值覆盖主题资产

- 现象：全量测试中瑞幸前台主题不再渲染自身 Logo，1 项断言失败。
- 根因：全局站点默认值错误加入 `site_logo`，使主题的空值回退分支失效。
- 处理：全局只合并品牌文案，Logo 字段继续留空并由主题使用自身官方资产；完整回归 982 项、7915 个断言通过。

## 2026-07-18 17:28 — 相对 Logo 路径无法直接保存

- 现象：最终只读审查发现新站点直接保存设置时，`/images/...` 默认值会被 URL 校验和浏览器 `type=url` 拒绝。
- 根因：站点 Logo 原有契约只接受完整 URL，相对路径同时不适合导出到缺少该文件的目标站点。
- 处理：恢复 Logo 空默认值，保留设置页头和前台主题各自使用的官方瑞幸资产，不扩张既有字段契约。

## 2026-07-18 17:35 — 品牌默认值与登录别名兼容性复审

- 现象：复审发现全局合并品牌文案会覆盖显式 `SITE_*` 配置，且普通管理员可能占用页面公开提示的 `luckin_admin`。
- 根因：品牌默认值绕过原配置回退链；别名解析又优先真实同名账号。
- 处理：瑞幸文案改为 `config/geoflow.php` 的默认值并保留环境配置优先；别名始终解析默认管理员，同时在管理员创建和改名入口保留该名称。

## 2026-07-18 20:03 — SSH 私钥权限过宽

- 现象：OpenSSH 拒绝加载 0644 的 `renjikun.pem`。
- 根因：私钥对同组/其他用户可读，不满足 OpenSSH 安全要求。
- 处理：将本机私钥改为 0600；核验服务器 ED25519 指纹后始终启用严格主机密钥检查。

## 2026-07-18 20:07 — 服务器无法稳定连接 GitHub 与 Docker Hub

- 现象：HTTPS 克隆 GitHub 长时间无响应，Docker Hub 元数据请求超时。
- 根因：腾讯云主机到两个境外服务的当前线路不稳定，项目代码与镜像本身无异常。
- 处理：从本机干净提交生成 Git bundle 上传；Docker 配置腾讯云镜像源后完成全部生产镜像构建。

## 2026-07-18 20:20 — 外部健康探针把未知 API 打成 500

- 现象：Uptime-Kuma 每分钟请求不存在的 `/api/auth/session`，统一 API 异常兜底错误映射为 500 并持续写日志。
- 根因：`bootstrap/app.php` 的 API `Throwable` 处理未在内部错误前保留 `NotFoundHttpException` 的 404 语义。
- 处理：未知 API 统一返回 `not_found` JSON 404 与 `X-Request-Id`；新增契约测试，23 项、134 个断言通过并在线复测。

## 2026-07-18 20:23 — Docker 镜像列表命令参数错误

- 现象：一次 `docker images` 同时传入两个仓库参数，被 CLI 拒绝。
- 根因：该命令最多接受一个仓库过滤条件。
- 处理：改以 Compose 构建日志与服务状态核对镜像，生产构建和容器启动不受影响。

## 2026-07-18 20:19 — 生产管理员账号处于锁定状态

- 现象：生产登录页正确显示 `luckin_admin`，但账号已被既有失败次数锁定；本地开发密码也与生产密码不同。
- 根因：管理员锁定状态持久化在生产数据库，Vercel 没有远程 Artisan 终端，不能直接运行项目已有的 `geoflow:admin-unlock` 命令。
- 处理：通过一次性维护部署调用既有解锁服务并建立只读 QA 会话；随后恢复干净提交重新部署，两个临时入口均返回 404，对应临时部署也已删除，生产密码未被修改。

## 2026-07-18 20:47 — Vercel CLI 日志筛选参数与当前版本不兼容

- 现象：Vercel CLI 50.28.0 对带部署 URL 的 `logs --since --level` 解释为 follow 模式，改用 `--deployment-id` 又提示未知参数。
- 根因：本机 CLI 的日志参数集与新版文档描述不一致。
- 处理：停止重复尝试，改用 `vercel inspect` 验证最终部署为 Ready，并以生产页面实访和 Chrome 控制台 0 条 error 完成运行时验收。

## 2026-07-18 20:59 — 腾讯云登录页未告知随机初始密码

- 现象：`luckin_admin` 登录提示“用户名或密码错误，或账号已被停用”，访问者不知道部署时生成的随机密码。
- 根因：账号别名与启用状态均正常；生产环境关闭了首次凭据提示，且部署验收已写入 `last_login`，原提示条件不再成立。
- 处理：保留随机密码与服务端哈希校验，新增默认关闭的演示密码展示开关；仅在腾讯云演示环境显式开启。

## 2026-07-18 21:02 — 临时 worktree 回归测试缺少 Vite 清单

- 现象：扩展后台页面测试返回 500，异常为 `Vite manifest not found`。
- 根因：隔离 worktree 未安装 Node 依赖，也没有生成 `public/build/manifest.json`；登录页专项测试不依赖该布局，因此先行通过。
- 处理：执行 `npm ci` 与 `npm run build` 后重跑，20 项、88 个断言全部通过。
