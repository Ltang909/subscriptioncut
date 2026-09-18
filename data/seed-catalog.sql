-- Cutline catalog seed: well-known subscription services grouped by category.
-- Tier prices are researched reference points (last_verified_date), not
-- guarantees — pricing drifts constantly and varies by promo/region, so the
-- onboarding flow lets the user confirm/adjust the actual price they pay.

INSERT INTO catalog_services (name, category, typical_cadence, tiers_json, last_verified_date) VALUES
('Netflix', 'streaming', 'monthly', '[{"name":"Standard with Ads","price_cents":899},{"name":"Standard","price_cents":1999},{"name":"Premium","price_cents":2299}]', '2026-09-18'),
('Disney+', 'streaming', 'monthly', '[{"name":"Basic (with ads)","price_cents":1199},{"name":"Premium (ad-free)","price_cents":1899}]', '2026-09-18'),
('Max', 'streaming', 'monthly', '[{"name":"With Ads","price_cents":1099},{"name":"Ad-Free","price_cents":1799}]', '2026-09-18'),
('Hulu', 'streaming', 'monthly', '[{"name":"With Ads","price_cents":1199},{"name":"No Ads","price_cents":1899}]', '2026-09-18'),
('Amazon Prime Video', 'streaming', 'monthly', '[{"name":"With Ads (standalone)","price_cents":899},{"name":"Ad-Free add-on","price_cents":1199}]', '2026-09-18'),
('Apple TV+', 'streaming', 'monthly', '[{"name":"Standard","price_cents":1299}]', '2026-09-18'),
('Paramount+', 'streaming', 'monthly', '[{"name":"Essential (with ads)","price_cents":899},{"name":"Premium","price_cents":1399}]', '2026-09-18'),
('Peacock', 'streaming', 'monthly', '[{"name":"Select","price_cents":799},{"name":"Premium","price_cents":1299},{"name":"Premium Plus","price_cents":1999}]', '2026-09-18'),

('Spotify', 'music', 'monthly', '[{"name":"Individual","price_cents":1299},{"name":"Student","price_cents":699},{"name":"Family","price_cents":1999}]', '2026-09-18'),
('Apple Music', 'music', 'monthly', '[{"name":"Individual","price_cents":1099},{"name":"Student","price_cents":599},{"name":"Family","price_cents":1699}]', '2026-09-18'),
('YouTube Music', 'music', 'monthly', '[{"name":"Individual","price_cents":1199},{"name":"Student","price_cents":599},{"name":"Family","price_cents":1899}]', '2026-09-18'),
('Tidal', 'music', 'monthly', '[{"name":"Individual","price_cents":1199},{"name":"Family","price_cents":1999}]', '2026-09-18'),

('Adobe Creative Cloud', 'software', 'monthly', '[{"name":"Photography","price_cents":999},{"name":"Single App","price_cents":2299},{"name":"Creative Cloud Pro (All Apps)","price_cents":6999}]', '2026-09-18'),
('Microsoft 365', 'software', 'monthly', '[{"name":"Personal","price_cents":699},{"name":"Family","price_cents":999}]', '2026-09-18'),
('Notion', 'software', 'monthly', '[{"name":"Plus (annual billing)","price_cents":1000},{"name":"Plus (monthly billing)","price_cents":1200},{"name":"Business","price_cents":1500}]', '2026-09-18'),
('Canva Pro', 'software', 'monthly', '[{"name":"Pro","price_cents":1499}]', '2026-09-18'),
('ChatGPT Plus', 'software', 'monthly', '[{"name":"Plus","price_cents":2000}]', '2026-09-18'),
('GitHub Copilot', 'software', 'monthly', '[{"name":"Pro","price_cents":1000},{"name":"Pro+","price_cents":3900}]', '2026-09-18'),

('Google One', 'cloud_storage', 'monthly', '[{"name":"200GB","price_cents":299},{"name":"2TB","price_cents":999}]', '2026-09-18'),
('iCloud+', 'cloud_storage', 'monthly', '[{"name":"200GB","price_cents":299},{"name":"2TB","price_cents":999}]', '2026-09-18'),
('Dropbox', 'cloud_storage', 'monthly', '[{"name":"Plus (2TB)","price_cents":1199},{"name":"Professional (3TB)","price_cents":1658}]', '2026-09-18'),

('Peloton App', 'fitness', 'monthly', '[{"name":"App One","price_cents":1299},{"name":"App+","price_cents":2400}]', '2026-09-18'),
('Strava', 'fitness', 'monthly', '[{"name":"Subscription","price_cents":1199}]', '2026-09-18'),
('ClassPass', 'fitness', 'monthly', '[{"name":"5 credits","price_cents":6500},{"name":"10 credits","price_cents":12000},{"name":"Unlimited","price_cents":18000}]', '2026-09-18'),

('The New York Times', 'reading', 'monthly', '[{"name":"All Access","price_cents":2500}]', '2026-09-18'),
('Audible', 'reading', 'monthly', '[{"name":"Standard (1 credit)","price_cents":899},{"name":"Premium Plus","price_cents":1495}]', '2026-09-18'),
('Kindle Unlimited', 'reading', 'monthly', '[{"name":"Standard","price_cents":1199}]', '2026-09-18'),
('Medium', 'reading', 'monthly', '[{"name":"Membership","price_cents":500}]', '2026-09-18'),

('Xbox Game Pass', 'gaming', 'monthly', '[{"name":"PC","price_cents":1399},{"name":"Ultimate","price_cents":2299}]', '2026-09-18'),
('PlayStation Plus', 'gaming', 'monthly', '[{"name":"Essential","price_cents":1099},{"name":"Extra","price_cents":1699},{"name":"Premium","price_cents":1999}]', '2026-09-18'),
('Nintendo Switch Online', 'gaming', 'annual', '[{"name":"Individual","price_cents":1999},{"name":"Family","price_cents":3499}]', '2026-09-18'),

('DoorDash DashPass', 'food_delivery', 'monthly', '[{"name":"DashPass","price_cents":999}]', '2026-09-18'),
('Uber One', 'food_delivery', 'monthly', '[{"name":"Uber One","price_cents":999},{"name":"Student","price_cents":499}]', '2026-09-18'),
('Instacart+', 'food_delivery', 'annual', '[{"name":"Instacart+","price_cents":9900}]', '2026-09-18');
