<?php

namespace Tests\Feature;

use App\Livewire\Home\Dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisualReviewRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_quick_assignment_without_templates_links_to_the_kids_page(): void
    {
        $parent = User::factory()->create();
        $child = User::factory()->create(['is_child' => true]);

        Livewire::actingAs($parent)
            ->test(Dashboard::class)
            ->call('openQuickAssignModal', $child->id)
            ->assertSet('showQuickAssignModal', true)
            ->assertSee('No templates found')
            ->assertSee('href="'.route('kids.index').'"', false);
    }
}
