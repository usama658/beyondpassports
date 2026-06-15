<?php

namespace App\Enums;

/**
 * Country-guide topic types (the SEO silo's "spokes").
 * Each backs one published guide per destination (unique destination_id + guide_type).
 *
 * - label()        — human title for cards/headings.
 * - topicSlug()     — the URL segment under /visa/{destination}/{topic}.
 * - fromTopicSlug() — reverse lookup for route resolution.
 */
enum GuideType: string
{
    case DoINeedVisa = 'do_i_need_visa';
    case Documents = 'documents';
    case PassportValidity = 'passport_validity';
    case ProcessingTime = 'processing_time';
    case HowToApply = 'how_to_apply';
    case CostFees = 'cost_fees';
    case WhenToApply = 'when_to_apply';
    case Children = 'children';
    case Refused = 'refused';
    case Residents = 'residents';
    case Transit = 'transit';
    case VisaOnArrival = 'visa_on_arrival';
    case Entries = 'entries';
    case Driving = 'driving';
    case Health = 'health';

    public function label(): string
    {
        return match ($this) {
            self::DoINeedVisa => 'Do I need a visa?',
            self::Documents => 'Documents required',
            self::PassportValidity => 'Passport validity',
            self::ProcessingTime => 'Processing time',
            self::HowToApply => 'How to apply',
            self::CostFees => 'Cost & fees',
            self::WhenToApply => 'When to apply',
            self::Children => 'Children',
            self::Refused => 'Refused applications',
            self::Residents => 'UK residents',
            self::Transit => 'Transit',
            self::VisaOnArrival => 'Visa on arrival',
            self::Entries => 'Entries',
            self::Driving => 'Driving',
            self::Health => 'Health',
        };
    }

    public function topicSlug(): string
    {
        return match ($this) {
            self::DoINeedVisa => 'do-i-need-a-visa',
            self::Documents => 'documents',
            self::PassportValidity => 'passport-validity',
            self::ProcessingTime => 'processing-time',
            self::HowToApply => 'how-to-apply',
            self::CostFees => 'cost',
            self::WhenToApply => 'when-to-apply',
            self::Children => 'children',
            self::Refused => 'refused',
            self::Residents => 'uk-residents',
            self::Transit => 'transit',
            self::VisaOnArrival => 'visa-on-arrival',
            self::Entries => 'entries',
            self::Driving => 'driving',
            self::Health => 'health',
        };
    }

    /** Reverse lookup from a URL topic segment. Null for unknown/missing slugs. */
    public static function fromTopicSlug(?string $slug): ?self
    {
        if ($slug === null) {
            return null;
        }

        foreach (self::cases() as $case) {
            if ($case->topicSlug() === $slug) {
                return $case;
            }
        }

        return null;
    }
}
