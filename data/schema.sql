-- Cutline subscription tracker schema (SQLite)

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  email TEXT NOT NULL UNIQUE,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  notify_days_before INTEGER NOT NULL DEFAULT 3,
  deposit_status TEXT NOT NULL DEFAULT 'none'
);

CREATE TABLE IF NOT EXISTS auth_tokens (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  token_hash TEXT NOT NULL UNIQUE,
  purpose TEXT NOT NULL DEFAULT 'login',
  expires_at TEXT NOT NULL,
  used_at TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS sessions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  session_token_hash TEXT NOT NULL UNIQUE,
  expires_at TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS catalog_services (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  category TEXT NOT NULL,
  typical_cadence TEXT NOT NULL DEFAULT 'monthly',
  tiers_json TEXT NOT NULL DEFAULT '[]',
  last_verified_date TEXT,
  is_active INTEGER NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS subscriptions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  catalog_service_id INTEGER REFERENCES catalog_services(id),
  custom_name TEXT,
  category TEXT NOT NULL,
  tier_name TEXT,
  price_cents INTEGER NOT NULL,
  currency TEXT NOT NULL DEFAULT 'USD',
  cadence TEXT NOT NULL,
  started_on TEXT NOT NULL,
  next_renewal_on TEXT NOT NULL,
  status TEXT NOT NULL DEFAULT 'active',
  is_free_trial INTEGER NOT NULL DEFAULT 0,
  trial_ends_on TEXT,
  created_at TEXT NOT NULL DEFAULT (datetime('now')),
  updated_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE TABLE IF NOT EXISTS reminder_log (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  subscription_id INTEGER NOT NULL REFERENCES subscriptions(id) ON DELETE CASCADE,
  renewal_date_at_send TEXT NOT NULL,
  sent_at TEXT NOT NULL DEFAULT (datetime('now')),
  UNIQUE(subscription_id, renewal_date_at_send)
);

-- Phase 3: commitment-deposit feature. Tables exist regardless, but every
-- code path that touches them is gated behind DEPOSIT_FEATURE_ENABLED.
CREATE TABLE IF NOT EXISTS deposits (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
  stripe_session_id TEXT,
  stripe_payment_intent_id TEXT,
  stripe_refund_id TEXT,
  amount_cents INTEGER NOT NULL DEFAULT 2500,
  status TEXT NOT NULL DEFAULT 'created',
  deadline_at TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_subscriptions_user ON subscriptions(user_id);
CREATE INDEX IF NOT EXISTS idx_subscriptions_renewal ON subscriptions(next_renewal_on) WHERE status = 'active';
CREATE INDEX IF NOT EXISTS idx_auth_tokens_hash ON auth_tokens(token_hash);
CREATE INDEX IF NOT EXISTS idx_sessions_hash ON sessions(session_token_hash);
-- Catalog names must be unique so a double-seed (two concurrent first
-- requests) can't create duplicate catalog rows.
CREATE UNIQUE INDEX IF NOT EXISTS idx_catalog_services_name ON catalog_services(name);
