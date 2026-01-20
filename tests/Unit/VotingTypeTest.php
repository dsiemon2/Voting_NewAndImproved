<?php

namespace Tests\Unit;

use App\Models\VotingType;
use App\Models\VotingPlaceConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingTypeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ranked voting type has correct place configurations
     */
    public function test_standard_ranked_has_three_places(): void
    {
        $votingType = VotingType::create([
            'code' => 'standard-ranked-321',
            'name' => 'Standard Ranked (3-2-1)',
            'category' => 'ranked',
            'description' => '3 places with 3-2-1 points',
        ]);

        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 3, 'label' => '1st', 'color' => 'gold']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 2, 'label' => '2nd', 'color' => 'silver']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1, 'label' => '3rd', 'color' => 'bronze']);

        $configs = $votingType->getPlacesArray();

        $this->assertCount(3, $configs);
        $this->assertEquals(3, $configs[0]['points']);
        $this->assertEquals(2, $configs[1]['points']);
        $this->assertEquals(1, $configs[2]['points']);
    }

    /**
     * Test extended ranked voting type has five places
     */
    public function test_extended_ranked_has_five_places(): void
    {
        $votingType = VotingType::create([
            'code' => 'extended-ranked-54321',
            'name' => 'Extended Ranked (5-4-3-2-1)',
            'category' => 'ranked',
            'description' => '5 places',
        ]);

        for ($i = 1; $i <= 5; $i++) {
            VotingPlaceConfig::create([
                'voting_type_id' => $votingType->id,
                'place' => $i,
                'points' => 6 - $i,
                'label' => ordinal($i),
            ]);
        }

        $configs = $votingType->getPlacesArray();

        $this->assertCount(5, $configs);
        $this->assertEquals(5, $configs[0]['points']);  // 1st place
        $this->assertEquals(1, $configs[4]['points']);  // 5th place
    }

    /**
     * Test top-heavy ranked voting type
     */
    public function test_top_heavy_points_distribution(): void
    {
        $votingType = VotingType::create([
            'code' => 'top-heavy-531',
            'name' => 'Top-Heavy (5-3-1)',
            'category' => 'ranked',
            'description' => 'Emphasis on 1st place',
        ]);

        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 1, 'points' => 5, 'label' => '1st']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 2, 'points' => 3, 'label' => '2nd']);
        VotingPlaceConfig::create(['voting_type_id' => $votingType->id, 'place' => 3, 'points' => 1, 'label' => '3rd']);

        $configs = $votingType->getPlacesArray();

        $this->assertEquals(5, $configs[0]['points']);
        $this->assertEquals(3, $configs[1]['points']);
        $this->assertEquals(1, $configs[2]['points']);

        // Verify point spread (top-heavy should have larger spread)
        $spread = $configs[0]['points'] - $configs[2]['points'];
        $this->assertEquals(4, $spread);
    }

    /**
     * Test approval voting category validation
     */
    public function test_approval_voting_category(): void
    {
        $votingType = VotingType::create([
            'code' => 'equal-weight',
            'name' => 'Equal Weight',
            'category' => 'approval',
            'description' => 'All votes equal',
            'settings' => ['points_per_vote' => 1],
        ]);

        $this->assertEquals('approval', $votingType->category);
        $this->assertEquals(1, $votingType->settings['points_per_vote']);
    }

    /**
     * Test rating voting category validation
     */
    public function test_rating_voting_category(): void
    {
        $votingType = VotingType::create([
            'code' => '5-star-rating',
            'name' => '5-Star Rating',
            'category' => 'rating',
            'description' => 'Rate 1-5 stars',
            'settings' => ['min_rating' => 1, 'max_rating' => 5],
        ]);

        $this->assertEquals('rating', $votingType->category);
        $this->assertEquals(1, $votingType->settings['min_rating']);
        $this->assertEquals(5, $votingType->settings['max_rating']);
    }

    /**
     * Test weighted voting category
     */
    public function test_weighted_voting_category(): void
    {
        $votingType = VotingType::create([
            'code' => 'weighted-judges',
            'name' => 'Weighted with Judges',
            'category' => 'weighted',
            'description' => 'Judges count more',
            'settings' => [
                'weights' => [
                    'judge' => 3.0,
                    'public' => 1.0,
                ],
            ],
        ]);

        $this->assertEquals('weighted', $votingType->category);
        $this->assertEquals(3.0, $votingType->settings['weights']['judge']);
        $this->assertEquals(1.0, $votingType->settings['weights']['public']);
    }

    /**
     * Test valid voting categories
     */
    public function test_valid_voting_categories(): void
    {
        $validCategories = ['ranked', 'approval', 'rating', 'weighted'];

        foreach ($validCategories as $index => $category) {
            $votingType = VotingType::create([
                'code' => "test-{$category}-{$index}",
                'name' => "Test {$category}",
                'category' => $category,
                'description' => 'Test',
            ]);

            $this->assertEquals($category, $votingType->category);
            $votingType->delete();
        }
    }
}

/**
 * Helper function for ordinal numbers
 */
if (!function_exists('ordinal')) {
    function ordinal(int $number): string
    {
        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
        if (($number % 100) >= 11 && ($number % 100) <= 13) {
            return $number . 'th';
        }
        return $number . $suffixes[$number % 10];
    }
}
