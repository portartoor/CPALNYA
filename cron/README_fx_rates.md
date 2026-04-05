## FX Cron Setup

### 1) Create tables
Run SQL file:

```sql
source /home/geoip/public_html/currency_rates_setup.sql;
```

or import it with your DB tool.

### 2) Add cron job (every 5 minutes)

```bash
*/5 * * * * /usr/bin/php /home/geoip/public_html/cron/update_fx_rates.php >> /home/geoip/logs/fx_rates_cron.log 2>&1
```

Adjust PHP binary path if needed (`which php`).

### 3) Data sources
- Fiat rates (USD base): `https://open.er-api.com/v6/latest/USD`
- Crypto rates (BTC, ETH, BNB, TON): `https://api.coingecko.com/api/v3/simple/price`

Total external requests per run: **2**.

