<?php

namespace App;

enum PlacementType: string
{
    case Web = 'web';
    case SocialMedia = 'social_media';
    case Programmatic = 'programmatic';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Web',
            self::SocialMedia => 'Social Media',
            self::Programmatic => 'Programmatic',
        };
    }
}
