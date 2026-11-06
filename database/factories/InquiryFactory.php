<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'buyer_id' => User::factory()->verified(),
            'clicked_at' => now(),
        ];
    }
}
