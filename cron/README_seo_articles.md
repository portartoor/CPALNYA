## SEO Articles Cron (OpenAI)

### 1) Configure in `core/config.php`

Set:

- `$OpenAIApiKey` (required)
- `$SeoArticleCronEnabled = true`

Optional tuning:

- `$SeoArticleCronLanguages = ['en', 'ru']`
- `$SeoArticleCronDailyMin = 1`
- `$SeoArticleCronDailyMax = 3`
- `$SeoArticleCronWordMin = 2000`
- `$SeoArticleCronWordMax = 5000`
- `$SeoArticleCronMaxPerRun = 2`
- `$SeoArticleCronDomainHost = ''` (empty = global articles)

OpenAI proxy (optional):

- `$OpenAIProxyEnabled = true`
- `$OpenAIProxyHost = '127.0.0.1'`
- `$OpenAIProxyPort = 1080`
- `$OpenAIProxyType = 'socks5'` (`http` or `socks5`)
- `$OpenAIProxyUsername = ''`
- `$OpenAIProxyPassword = ''`

Proxy pool (optional, preferred):

- `$OpenAIProxyPoolEnabled = true`
- `$OpenAIProxyPool = ['host:port:user:pass', ...]`

### 2) Run SQL migration

Execute:

- `seo_articles_cron_setup.sql`

### 3) Add cron entry

Recommended frequency:

```bash
*/15 * * * * /usr/bin/php /home/geoip/public_html/cron/generate_seo_articles.php >> /home/geoip/logs/seo_articles_cron.log 2>&1
```

Queue mode (recommended for production):

```bash
# 1) every hour: put daily campaign tasks into queue if they are missing
5 * * * * /usr/bin/php /home/cpalnya/public_html/cron/seo_article_queue.php --enqueue-daily >> /home/cpalnya/logs/seo_article_queue.log 2>&1

# 2) every 10 minutes: process queue
*/10 * * * * /usr/bin/php /home/cpalnya/public_html/cron/seo_article_queue.php --work --limit=2 >> /home/cpalnya/logs/seo_article_queue_worker.log 2>&1
```

Image backfill (restore missing preview images for existing articles):

```bash
# every 30 minutes, restore/migrate up to 6 article preview images
*/30 * * * * /usr/bin/php /home/cpalnya/public_html/cron/generate_seo_articles.php --backfill-images --image-limit=6 >> /home/cpalnya/logs/seo_article_backfill.log 2>&1
```

Notes:

- Backfill now saves full image + thumbnail files on server (`/cache/examples_previews/full`, `/cache/examples_previews/thumb`).
- It updates DB fields: `preview_image_url`, `preview_image_thumb_url`, `preview_image_data` (clears base64 after file save).
- Use `--backfill-force` to reprocess already existing image rows.

How scheduling works:

- Script plans deterministic random slots per day and per language.
- Daily target is `1..3` articles (configurable) for each language.
- On each run it checks due slots and generates missing articles.
- State and retry attempts are stored in `seo_article_cron_runs`.

### 4) Debug / forced run

CLI options:

- `--date=YYYY-MM-DD` (override job date)
- `--lang=en` or `--lang=ru` or `--lang=en,ru`
- `--max-per-run=1`
- `--campaign=journal` or `--campaign=playbooks`
- `--force` (ignore time check and run all slots for selected date)
- `--dry-run` (call OpenAI + validate result, but do not insert into DB)
- `--proxy-check` (check all configured proxies from pool/single proxy)

Examples:

```bash
# Safe test: run yesterday in dry-run mode for RU only
/usr/bin/php /home/geoip/public_html/cron/generate_seo_articles.php --date=$(date -u -d 'yesterday' +%F) --lang=ru --force --dry-run --max-per-run=1

# Real forced generation: create 4-8 Journal or Playbooks materials immediately
/usr/bin/php /home/cpalnya/public_html/cron/generate_seo_articles.php --campaign=journal --date=$(date +%F) --lang=ru --force --max-per-run=4
/usr/bin/php /home/cpalnya/public_html/cron/generate_seo_articles.php --campaign=playbooks --date=$(date +%F) --lang=ru --force --max-per-run=6

# Check proxy pool only (no DB writes, no article generation)
/usr/bin/php /home/cpalnya/public_html/cron/generate_seo_articles.php --proxy-check

# Queue: add campaign tasks, then process
/usr/bin/php /home/cpalnya/public_html/cron/seo_article_queue.php --enqueue --campaign=journal --date=$(date +%F) --lang=ru,en --max-per-run=4
/usr/bin/php /home/cpalnya/public_html/cron/seo_article_queue.php --enqueue --campaign=playbooks --date=$(date +%F) --lang=ru,en --max-per-run=6
/usr/bin/php /home/cpalnya/public_html/cron/seo_article_queue.php --work --limit=2
```
