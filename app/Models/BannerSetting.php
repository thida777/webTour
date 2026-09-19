<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BannerSetting extends Model
{
    // The website uses only ONE row in this table. Get it with BannerSetting::first().
    protected $fillable = [
        'logo',
        'brand_name',
        'small_title',
        'main_title',
        'highlight_text',
        'tagline',
        'button_text',
        'button_url',
        'phone',
        'website',
        'image_1',
        'image_2',
        'image_3',
        'include_1',
        'include_2',
        'include_3',
        'include_4',
        'offer_title',
        'discount',
        'offer_suffix',
        'show_offer',
        'facebook_url',
        'instagram_url',
        'youtube_url',
        'x_url',
        'is_active',
    ];

    protected $casts = [
        'discount' => 'float',
        'show_offer' => 'boolean',
        'is_active' => 'boolean',
    ];
}
