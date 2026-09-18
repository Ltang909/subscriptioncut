<?php
// Single source of truth for optional/inert features. Every code path that
// touches the Stripe commitment-deposit feature checks this first, so with
// it left false the feature is fully dormant: no Stripe files are require()'d,
// no UI for it renders, no charges/refunds can ever be triggered.
define('DEPOSIT_FEATURE_ENABLED', false);
