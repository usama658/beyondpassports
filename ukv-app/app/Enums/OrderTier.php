<?php

namespace App\Enums;

/**
 * Order service tier.
 * from: ukv_tier — WP stored the label ("Standard"); normalized to lowercase key.
 * SLA hours derived in config from this key (express=24, premium=12, else standard=72).
 */
enum OrderTier: string
{
    case Standard = 'standard';
    case Express = 'express';
    case Premium = 'premium';
}
