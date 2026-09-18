# Cutline — subscription tracker

Track what you're actually paying for, see it broken down by category, and
get emailed a few days before anything renews so cancelling (or keeping it)
is a decision you make on purpose.

## What it is

- **Onboarding checklist** seeded from ~30 well-known subscriptions
  (`data/seed-catalog.sql`) — check off what you have, pick a tier and
  billing cadence, and it's tracked. Anything else, add manually.
- **Dashboard**: total monthly/yearly spend, a breakdown by category, and a
  list of upcoming renewals.
- **Renewal reminder emails**, sent by a daily cron job a few days (default
  3, configurable per-account) before each subscription renews.
- **Passwordless sign-in**: enter your email, click the link we send you.
  No passwords stored anywhere.
- **Commitment deposit (optional, off by default)**: a $25 deposit you get
  back if you cancel all your tracked free trials before their deadlines —
  see "Turning on the deposit feature" below. It's fully inert until you
  configure it.

No build step, no framework: plain PHP + vanilla JS + SQLite, deployed by
uploading files — same model as before.

## How it's deployed

Pushing to `main` triggers `.github/workflows/main.yml`, which copies this
repo straight to `domains/builtbylt.com/public_html/subscription-cut` on
Hostinger over SCP. There's no separate manual upload step — merging to
`main` *is* the deploy.

The SQLite database (`data/cutline.sqlite`) is created automatically the
first time any page runs, from `data/schema.sql` and `data/seed-catalog.sql`
— nothing to run by hand after deploying.

## One manual step: the renewal-reminder cron job

Renewal emails only go out if a cron job actually runs the script. Add this
in Hostinger's hPanel (Advanced → Cron Jobs), once, daily:

```
php /home/YOUR_USERNAME/domains/builtbylt.com/public_html/subscription-cut/cron/send-renewal-reminders.php
```

(Adjust the path if your Hostinger username/home directory differs — check
via File Manager or SSH.) The script refuses any request that isn't run
from the command line, and `cron/.htaccess` denies web access to the whole
folder, so it can't be triggered by just visiting a URL.

## Local development

Requires PHP 8+ with the `pdo_sqlite` extension enabled.

```bash
CUTLINE_MAIL_DRIVER=log php -S localhost:8000
```

With `CUTLINE_MAIL_DRIVER=log`, outgoing emails (sign-in links, renewal
reminders) are appended to `data/mail.log` instead of actually being sent —
useful since most local setups don't have a working mail transport. Open the
log after requesting a sign-in link to grab the URL. In production, leave
`CUTLINE_MAIL_DRIVER` unset — it defaults to PHP's `mail()`, same as the
existing `api/notify.php`.

Test the renewal cron by hand:

```bash
php cron/send-renewal-reminders.php
```

## Extending the catalog

`data/seed-catalog.sql` only runs once, when the database file is first
created. To add or edit catalog services afterward, either edit that file
and delete `data/cutline.sqlite` to reseed from scratch (destroys existing
user data — fine locally, not in production), or `INSERT`/`UPDATE`
`catalog_services` directly. Prices there are reference points researched
at a point in time (tagged with `last_verified_date`) — they drift, and
users can override the price when they add a subscription, so don't treat
them as gospel.

## Turning on the deposit feature

The $25 commitment-deposit mechanic is built but fully disabled by default
(`config/features.php` → `DEPOSIT_FEATURE_ENABLED = false`). With it off,
none of the Stripe-related files are ever loaded and none of the UI for it
renders — it's inert, not just hidden.

To turn it on:

1. Create your own Stripe account and get API keys from
   https://dashboard.stripe.com/apikeys.
2. Copy `config/stripe.local.php.example` to `config/stripe.local.php` and
   fill in your keys (this file is gitignored — never commit real keys).
3. Set up a webhook endpoint in Stripe pointing at
   `api/deposit-webhook.php`, and put its signing secret in
   `config/stripe.local.php` too.
4. Add a cron job for `cron/check-trial-deadlines.php` (same pattern as the
   renewal-reminder cron above) — this is what actually resolves deposits
   (refund or forfeit) once a deadline passes.
5. Flip `DEPOSIT_FEATURE_ENABLED` to `true` in `config/features.php`.

One thing worth a few minutes before turning this on for real users: the
"keep the $25 if you forget" framing is a forfeiture/penalty mechanic, and
holding a deposit pending a conditional refund can have money-transmission
or consumer-protection texture depending on your state. Worth a quick read
of the relevant rules (or a lawyer's 15 minutes) before it's live with real
money — not something this code enforces or blocks on, just worth knowing.

## Notes

- `api/notify.php` (the old "request a company be added" contact-form
  handler) is unused by the current UI but left in place — harmless, and
  could be repurposed for a feedback/contact form later.
- Cancelling a subscription in Cutline only stops *Cutline* from tracking
  it — it doesn't cancel anything with the actual company. The dashboard
  and edit pages say this explicitly so it's never ambiguous.
