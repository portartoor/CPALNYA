-- Examples/blog migration with multilingual support (EN + RU)
-- Safe to run multiple times.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `examples_articles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `domain_host` VARCHAR(255) NULL DEFAULT '',
  `lang_code` VARCHAR(5) NOT NULL DEFAULT 'en',
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(191) NOT NULL,
  `excerpt_html` TEXT NULL,
  `content_html` MEDIUMTEXT NOT NULL,
  `preview_image_url` VARCHAR(1024) NULL,
  `preview_image_style` VARCHAR(64) NULL,
  `author_name` VARCHAR(120) NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `published_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_examples_published` (`is_published`, `published_at`),
  KEY `idx_examples_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @db_name = DATABASE();

SET @has_lang_col = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'examples_articles'
    AND COLUMN_NAME = 'lang_code'
);
SET @sql_lang = IF(
  @has_lang_col = 0,
  'ALTER TABLE `examples_articles` ADD COLUMN `lang_code` VARCHAR(5) NOT NULL DEFAULT ''en'' AFTER `domain_host`',
  'SELECT 1'
);
PREPARE stmt_lang FROM @sql_lang;
EXECUTE stmt_lang;
DEALLOCATE PREPARE stmt_lang;

SET @has_preview_image_url = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'examples_articles'
    AND COLUMN_NAME = 'preview_image_url'
);
SET @sql_preview_image_url = IF(
  @has_preview_image_url = 0,
  'ALTER TABLE `examples_articles` ADD COLUMN `preview_image_url` VARCHAR(1024) NULL AFTER `content_html`',
  'SELECT 1'
);
PREPARE stmt_preview_image_url FROM @sql_preview_image_url;
EXECUTE stmt_preview_image_url;
DEALLOCATE PREPARE stmt_preview_image_url;

SET @has_preview_image_style = (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'examples_articles'
    AND COLUMN_NAME = 'preview_image_style'
);
SET @sql_preview_image_style = IF(
  @has_preview_image_style = 0,
  'ALTER TABLE `examples_articles` ADD COLUMN `preview_image_style` VARCHAR(64) NULL AFTER `preview_image_url`',
  'SELECT 1'
);
PREPARE stmt_preview_image_style FROM @sql_preview_image_style;
EXECUTE stmt_preview_image_style;
DEALLOCATE PREPARE stmt_preview_image_style;

UPDATE `examples_articles`
SET `lang_code` = 'en'
WHERE `lang_code` IS NULL OR `lang_code` = '';

SET @has_old_uniq = (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'examples_articles'
    AND INDEX_NAME = 'uniq_examples_domain_slug'
);
SET @sql_drop_old_uniq = IF(
  @has_old_uniq > 0,
  'ALTER TABLE `examples_articles` DROP INDEX `uniq_examples_domain_slug`',
  'SELECT 1'
);
PREPARE stmt_drop_old_uniq FROM @sql_drop_old_uniq;
EXECUTE stmt_drop_old_uniq;
DEALLOCATE PREPARE stmt_drop_old_uniq;

SET @has_new_uniq = (
  SELECT COUNT(*)
  FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db_name
    AND TABLE_NAME = 'examples_articles'
    AND INDEX_NAME = 'uniq_examples_domain_slug_lang'
);
SET @sql_add_new_uniq = IF(
  @has_new_uniq = 0,
  'ALTER TABLE `examples_articles` ADD UNIQUE KEY `uniq_examples_domain_slug_lang` (`domain_host`, `slug`, `lang_code`)',
  'SELECT 1'
);
PREPARE stmt_add_new_uniq FROM @sql_add_new_uniq;
EXECUTE stmt_add_new_uniq;
DEALLOCATE PREPARE stmt_add_new_uniq;

INSERT INTO `examples_articles`
(`domain_host`, `lang_code`, `title`, `slug`, `excerpt_html`, `content_html`, `author_name`, `sort_order`, `is_published`, `published_at`, `created_at`, `updated_at`)
VALUES
(
  '',
  'en',
  'What is user_id and how to generate it correctly',
  'what-is-user-id-and-how-to-generate-it-correctly',
  '<p><strong>user_id</strong> is a stable and privacy safe identifier that links antifraud events across sessions. This article explains correct design, common mistakes, and implementations in JavaScript, Python, PHP, Go, Java, and C#.</p>',
  '<h3>Why user_id exists</h3>
<p>Fraud signals become useful only when events are connected to the same logical user over time. If user identity changes every request, country switch, ASN anomalies, or impossible travel checks become noisy and unreliable.</p>

<h3>Requirements for a production grade user_id</h3>
<ul>
  <li><strong>Stable:</strong> same business user always gets the same value.</li>
  <li><strong>Unique:</strong> two users must not share the same identifier.</li>
  <li><strong>Non reversible:</strong> do not expose raw email, phone, or external IDs.</li>
  <li><strong>Versioned:</strong> use prefixes like <code>uid_v1_</code> for future migrations.</li>
  <li><strong>Deterministic:</strong> identical input and secret produce identical output.</li>
</ul>

<h3>Recommended formula</h3>
<p><code>uid_v1_ + base64url(HMAC_SHA256(namespace + ":" + external_user_key, secret))</code></p>
<p>This gives strong consistency while protecting internal business identifiers.</p>

<h3>Common implementation mistakes</h3>
<ol>
  <li>Creating random UUID on every request.</li>
  <li>Using plain email as user_id.</li>
  <li>Using device id as a user identity.</li>
  <li>Running different algorithms in different services.</li>
  <li>Changing format without version prefix.</li>
</ol>

<h3>JavaScript (Node.js)</h3>
<pre class="code-line"><code class="language-js">import crypto from "node:crypto";
function toBase64Url(buf) {
  return buf.toString("base64").replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/g, "");
}
export function buildUserId(externalUserKey, secret, namespace = "geoip_prod") {
  const msg = `${namespace}:${externalUserKey}`;
  const digest = crypto.createHmac("sha256", secret).update(msg, "utf8").digest();
  return `uid_v1_${toBase64Url(digest).slice(0, 32)}`;
}</code></pre>

<h3>Python</h3>
<pre class="code-line"><code class="language-python">import base64, hashlib, hmac
def build_user_id(external_user_key: str, secret: str, namespace: str = "geoip_prod") -&gt; str:
    msg = f"{namespace}:{external_user_key}".encode("utf-8")
    digest = hmac.new(secret.encode("utf-8"), msg, hashlib.sha256).digest()
    return "uid_v1_" + base64.urlsafe_b64encode(digest).decode("ascii").rstrip("=")[:32]</code></pre>

<h3>PHP</h3>
<pre class="code-line"><code class="language-php">&lt;?php
function build_user_id(string $externalUserKey, string $secret, string $namespace = "geoip_prod"): string {
    $msg = $namespace . ":" . $externalUserKey;
    $digest = hash_hmac("sha256", $msg, $secret, true);
    $b64 = rtrim(strtr(base64_encode($digest), "+/", "-_"), "=");
    return "uid_v1_" . substr($b64, 0, 32);
}</code></pre>

<h3>Go</h3>
<pre class="code-line"><code class="language-go">func BuildUserID(externalUserKey, secret, namespace string) string {
  mac := hmac.New(sha256.New, []byte(secret))
  mac.Write([]byte(namespace + ":" + externalUserKey))
  b64u := base64.RawURLEncoding.EncodeToString(mac.Sum(nil))
  if len(b64u) &gt; 32 { b64u = b64u[:32] }
  return "uid_v1_" + b64u
}</code></pre>

<h3>Java</h3>
<pre class="code-line"><code class="language-java">public static String build(String externalUserKey, String secret, String namespace) throws Exception {
    Mac mac = Mac.getInstance("HmacSHA256");
    mac.init(new SecretKeySpec(secret.getBytes(StandardCharsets.UTF_8), "HmacSHA256"));
    byte[] digest = mac.doFinal((namespace + ":" + externalUserKey).getBytes(StandardCharsets.UTF_8));
    String b64u = Base64.getUrlEncoder().withoutPadding().encodeToString(digest);
    return "uid_v1_" + b64u.substring(0, Math.min(32, b64u.length()));
}</code></pre>

<h3>C#</h3>
<pre class="code-line"><code class="language-csharp">public static string Build(string externalUserKey, string secret, string ns = "geoip_prod") {
    using var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(secret));
    var digest = hmac.ComputeHash(Encoding.UTF8.GetBytes($"{ns}:{externalUserKey}"));
    var b64u = Convert.ToBase64String(digest).Replace("+", "-").Replace("/", "_").TrimEnd((char)61);
    if (b64u.Length &gt; 32) b64u = b64u.Substring(0, 32);
    return $"uid_v1_{b64u}";
}</code></pre>

<h3>Integration example</h3>
<pre class="code-line"><code class="language-http">GET /api/?ip=8.8.8.8&amp;user_id=uid_v1_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Authorization: Bearer YOUR_API_KEY</code></pre>',
  'GeoIP Team',
  100,
  1,
  NOW(),
  NOW(),
  NOW()
),
(
  '',
  'ru',
  'Что такое user_id и как его корректно генерировать',
  'what-is-user-id-and-how-to-generate-it-correctly',
  '<p><strong>user_id</strong> это стабильный и безопасный идентификатор пользователя для связывания antifraud событий между сессиями. Ниже практическая схема, ошибки и примеры на разных языках.</p>',
  '<h3>Зачем нужен user_id</h3>
<p>Антифрод работает на последовательности событий. Если в каждом запросе новый идентификатор, система видит набор несвязанных посетителей и теряет важные сигналы поведения.</p>

<h3>Требования к корректному user_id</h3>
<ul>
  <li><strong>Стабильность:</strong> один пользователь получает одно значение.</li>
  <li><strong>Уникальность:</strong> без коллизий между разными пользователями.</li>
  <li><strong>Защита данных:</strong> не передавать email и телефон напрямую.</li>
  <li><strong>Детерминированность:</strong> одинаковые входные данные дают одинаковый результат.</li>
  <li><strong>Версионирование:</strong> префиксы вида <code>uid_v1_</code>.</li>
</ul>

<h3>Рекомендуемая формула</h3>
<p><code>uid_v1_ + base64url(HMAC_SHA256(namespace + ":" + external_user_key, secret))</code></p>

<h3>Типичные ошибки</h3>
<ol>
  <li>Генерация случайного UUID на каждый API запрос.</li>
  <li>Использование email как user_id.</li>
  <li>Использование device id вместо user identity.</li>
  <li>Разные алгоритмы в разных микросервисах.</li>
  <li>Смена формата без версии.</li>
</ol>

<h3>Примеры реализации</h3>
<pre class="code-line"><code class="language-js">const msg = `${namespace}:${externalUserKey}`;
const digest = crypto.createHmac("sha256", secret).update(msg, "utf8").digest();
const userId = `uid_v1_${toBase64Url(digest).slice(0, 32)}`;</code></pre>

<pre class="code-line"><code class="language-python">msg = f"{namespace}:{external_user_key}".encode("utf-8")
digest = hmac.new(secret.encode("utf-8"), msg, hashlib.sha256).digest()
user_id = "uid_v1_" + base64.urlsafe_b64encode(digest).decode("ascii").rstrip("=")[:32]</code></pre>

<pre class="code-line"><code class="language-php">$msg = $namespace . ":" . $externalUserKey;
$digest = hash_hmac("sha256", $msg, $secret, true);
$b64 = rtrim(strtr(base64_encode($digest), "+/", "-_"), "=");
$userId = "uid_v1_" . substr($b64, 0, 32);</code></pre>

<h3>Передача в API</h3>
<pre class="code-line"><code class="language-http">GET /api/?ip=8.8.8.8&amp;user_id=uid_v1_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Authorization: Bearer YOUR_API_KEY</code></pre>',
  'GeoIP Team',
  100,
  1,
  NOW(),
  NOW(),
  NOW()
),
(
  '',
  'en',
  'How to correctly detect user IP address',
  'how-to-correctly-detect-user-ip',
  '<p>Real client IP detection behind CDN and reverse proxy requires trust boundaries. This guide shows a safe extraction order and backend examples.</p>',
  '<h3>Why REMOTE_ADDR is often wrong</h3>
<p>When your app is behind Cloudflare, Nginx, ALB, or ingress, <code>REMOTE_ADDR</code> frequently contains proxy address. For antifraud this produces incorrect geo and risk scoring.</p>

<h3>Safe extraction order</h3>
<ol>
  <li>Define trusted proxy addresses.</li>
  <li>If request is not from trusted proxy, use <code>REMOTE_ADDR</code>.</li>
  <li>If request is from trusted proxy, read <code>CF-Connecting-IP</code>, then <code>X-Real-IP</code>, then first valid IP from <code>X-Forwarded-For</code>.</li>
  <li>Validate every candidate IP.</li>
  <li>Log both selected IP and source header for audit.</li>
</ol>

<h3>Nginx example</h3>
<pre class="code-line"><code class="language-nginx">set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
real_ip_header CF-Connecting-IP;
real_ip_recursive on;</code></pre>

<h3>Node.js</h3>
<pre class="code-line"><code class="language-js">function getClientIp(req, trusted) {
  const remote = req.socket?.remoteAddress || "";
  if (!trusted.has(remote)) return remote;
  const cf = req.header("cf-connecting-ip");
  if (cf && net.isIP(cf)) return cf;
  const xr = req.header("x-real-ip");
  if (xr && net.isIP(xr)) return xr;
  const xff = req.header("x-forwarded-for") || "";
  for (const part of xff.split(",").map(x =&gt; x.trim())) {
    if (net.isIP(part)) return part;
  }
  return remote;
}</code></pre>

<h3>Python</h3>
<pre class="code-line"><code class="language-python">def get_client_ip(request, trusted_proxies):
    remote = request.client.host if request.client else ""
    if remote not in trusted_proxies:
        return remote
    for header in ["cf-connecting-ip", "x-real-ip"]:
        value = request.headers.get(header, "")
        if is_valid_ip(value):
            return value
    for part in request.headers.get("x-forwarded-for", "").split(","):
        ip = part.strip()
        if is_valid_ip(ip):
            return ip
    return remote</code></pre>

<h3>PHP</h3>
<pre class="code-line"><code class="language-php">function get_client_ip(array $server, array $trusted): string {
    $remote = (string)($server["REMOTE_ADDR"] ?? "");
    if (!in_array($remote, $trusted, true)) return $remote;
    $cf = trim((string)($server["HTTP_CF_CONNECTING_IP"] ?? ""));
    if (filter_var($cf, FILTER_VALIDATE_IP)) return $cf;
    $xr = trim((string)($server["HTTP_X_REAL_IP"] ?? ""));
    if (filter_var($xr, FILTER_VALIDATE_IP)) return $xr;
    foreach (array_map("trim", explode(",", (string)($server["HTTP_X_FORWARDED_FOR"] ?? ""))) as $part) {
        if (filter_var($part, FILTER_VALIDATE_IP)) return $part;
    }
    return $remote;
}</code></pre>

<h3>Checklist</h3>
<ul>
  <li>Application is not directly accessible from public internet.</li>
  <li>Trusted proxy list is maintained and monitored.</li>
  <li>Invalid or private addresses are filtered according to policy.</li>
  <li>Unit and integration tests cover header combinations.</li>
</ul>

<h3>API call</h3>
<pre class="code-line"><code class="language-http">GET /api/?ip=203.0.113.42&amp;user_id=uid_v1_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Authorization: Bearer YOUR_API_KEY</code></pre>',
  'GeoIP Team',
  90,
  1,
  NOW(),
  NOW(),
  NOW()
),
(
  '',
  'ru',
  'Как корректно получать IP пользователя',
  'how-to-correctly-detect-user-ip',
  '<p>Получение реального IP за CDN и прокси требует доверенной модели. Ниже безопасный порядок чтения заголовков и примеры для backend.</p>',
  '<h3>Почему одного REMOTE_ADDR недостаточно</h3>
<p>За Cloudflare и reverse proxy значение <code>REMOTE_ADDR</code> часто равно адресу прокси. Это ломает гео и риск анализ.</p>

<h3>Безопасный порядок</h3>
<ol>
  <li>Определить список доверенных прокси.</li>
  <li>Если запрос не от доверенного прокси использовать <code>REMOTE_ADDR</code>.</li>
  <li>Если от доверенного читать <code>CF-Connecting-IP</code>, затем <code>X-Real-IP</code>, затем первый валидный IP из <code>X-Forwarded-For</code>.</li>
  <li>Валидировать каждое значение.</li>
  <li>Логировать выбранный IP и источник.</li>
</ol>

<h3>Nginx</h3>
<pre class="code-line"><code class="language-nginx">set_real_ip_from 173.245.48.0/20;
set_real_ip_from 103.21.244.0/22;
real_ip_header CF-Connecting-IP;
real_ip_recursive on;</code></pre>

<h3>Примеры backend</h3>
<pre class="code-line"><code class="language-js">const remote = req.socket?.remoteAddress || "";
if (!trusted.has(remote)) return remote;</code></pre>

<pre class="code-line"><code class="language-python">remote = request.client.host if request.client else ""
if remote not in trusted_proxies:
    return remote</code></pre>

<pre class="code-line"><code class="language-php">$remote = (string)($server["REMOTE_ADDR"] ?? "");
if (!in_array($remote, $trusted, true)) {
    return $remote;
}</code></pre>

<h3>Проверка перед production</h3>
<ul>
  <li>Прямой доступ к приложению закрыт.</li>
  <li>Список доверенных прокси актуален.</li>
  <li>Есть тесты на комбинации заголовков.</li>
  <li>Private IP фильтруются согласно политике.</li>
</ul>

<h3>Запрос к API</h3>
<pre class="code-line"><code class="language-http">GET /api/?ip=203.0.113.42&amp;user_id=uid_v1_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Authorization: Bearer YOUR_API_KEY</code></pre>',
  'GeoIP Team',
  90,
  1,
  NOW(),
  NOW(),
  NOW()
)
ON DUPLICATE KEY UPDATE
  `title` = VALUES(`title`),
  `excerpt_html` = VALUES(`excerpt_html`),
  `content_html` = VALUES(`content_html`),
  `author_name` = VALUES(`author_name`),
  `sort_order` = VALUES(`sort_order`),
  `is_published` = VALUES(`is_published`),
  `published_at` = VALUES(`published_at`),
  `updated_at` = NOW();
