# Cutline — launch checklist

A static site + one tiny PHP file. No database needed for this version.

## What it is
Type any company into the letter, get: the real cancellation method, a short
honest description of the known friction, an exact script, and a one-line
counter for retention pushback. ~15 companies are catalogued now
(`assets/app.js`, top of the file — the `COMPANIES` array). Anything not in
the list still gets a working generic script instead of an error.

## To deploy on Hostinger
1. Upload everything in this zip to `public_html/` of whatever domain or
   subdomain you're pointing at this (a fresh subdomain like
   `cutline.yourdomain.com`, or its own domain if you register one today).
2. Open `api/notify.php` and confirm the `NOTIFY_TO` email address near the
   top — it's currently set to `leon@leontang.ca`, change it if you want
   requests routed somewhere else.
3. That's it — no database, no config file to rename. Visit the domain and
   test: type a known company (try "Xfinity" or "Adobe") and an unknown one
   to confirm both paths work, then submit the "don't see yours" form and
   check that the email arrives (same `mail()` deliverability caveat as
   everything else we've built — check spam on the first test).

## Extending the company database
Every entry in `assets/app.js` is a plain object — copy an existing one and
edit the fields. Keep `situation`/`script`/`counter` honest and general
rather than claiming an exact current UI path (interfaces change constantly;
a wrong claim here costs more trust than a vague-but-true one). The `aliases`
array is what search matches against, so add common misspellings/nicknames.

## What's deliberately NOT built yet (fast-follow ideas, not needed for launch)
- No analytics — you're flying blind on which companies people search for
  most. The `notify.php` requests are your only current signal; worth
  logging searches too once you have real traffic, to prioritize which
  companies to add next.
- No SEO beyond basic meta tags — a page per company (e.g. `/cancel/adobe`)
  would let each one rank individually in search instead of just living
  behind the client-side search box. This is probably the single highest-
  leverage next step for organic growth, since "how to cancel [X]" is
  exactly what people already search.
- No email capture for a "we added your company" follow-up loop — the
  notify form takes an optional email but nothing currently emails the
  visitor back when their company gets added.
- No monetization built in. If this gets traction, affiliate links to
  services like Rocket Money (for the harder cases where "cancel it for
  you" beats "here's how") is the most natural fit, without competing head-
  on with that category — this stays the free, no-signup fast path instead.

## One honest note on positioning
This is intentionally a narrow wedge, not a Rocket Money competitor —
Rocket Money and similar tools already own "track and cancel my
subscriptions for me" as a category. Cutline's edge is being genuinely
useful with zero signup, in under 30 seconds, for the specific "I need the
exact script right now" moment — worth keeping it that fast and that free
even if you build monetization in later, since the speed is the whole pitch.
