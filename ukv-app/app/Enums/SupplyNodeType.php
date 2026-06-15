<?php

namespace App\Enums;

/**
 * Supply-node type.
 * from: supply node `type` (UKV_SUPPLY_TYPES).
 */
enum SupplyNodeType: string
{
    case Centre = 'centre';
    case Courier = 'courier';
    case Paypoint = 'paypoint';
    case Embassy = 'embassy';
}
