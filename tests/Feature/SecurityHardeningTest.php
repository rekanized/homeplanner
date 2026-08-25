<?php

namespace Tests\Feature;

use App\Livewire\Kids\KidsManager;
use App\Livewire\Shopping\ShoppingManager;
use App\Models\AuditLog;
use App\Models\Chore;
use App\Models\Setting;
use App\Models\ShoppingItem;
use App\Models\ShoppingList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_dynamic_responses_include_baseline_security_headers(): void
    {
        User::factory()->create();

        $this->get(route('login'))
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_sensitive_settings_are_encrypted_and_redacted_from_audit_logs(): void
    {
        Setting::set('google_client_secret', 'plain-secret', 'auth');

        $stored = Setting::where('key', 'google_client_secret')->firstOrFail();
        $audit = AuditLog::where('auditable_type', Setting::class)->latest('id')->firstOrFail();

        $this->assertNotSame('plain-secret', $stored->value);
        $this->assertSame('plain-secret', Setting::get('google_client_secret'));
        $this->assertSame('[REDACTED]', $audit->new_values['value']);
    }

    public function test_child_cannot_complete_another_childs_chore_or_adjust_points(): void
    {
        $child = User::factory()->create(['is_child' => true, 'accumulated_score' => 0]);
        $otherChild = User::factory()->create(['is_child' => true, 'accumulated_score' => 0]);
        $chore = Chore::factory()->create(['user_id' => $otherChild->id, 'score' => 50, 'is_completed' => false]);

        Livewire::actingAs($child)
            ->test(KidsManager::class)
            ->call('completeChore', $chore->id);

        $this->assertFalse($chore->fresh()->is_completed);
        $this->assertSame(0, $otherChild->fresh()->accumulated_score);

        Livewire::actingAs($child)
            ->test(KidsManager::class)
            ->set('adjustUserId', $child->id)
            ->set('adjustAmount', 100)
            ->call('adjustPoints')
            ->assertStatus(403);
    }

    public function test_shopping_item_actions_are_scoped_to_the_active_list(): void
    {
        $user = User::factory()->create();
        $activeList = ShoppingList::factory()->create(['sort_order' => 1]);
        $otherList = ShoppingList::factory()->create(['sort_order' => 2]);
        $otherItem = ShoppingItem::factory()->create([
            'shopping_list_id' => $otherList->id,
            'name' => 'Keep me',
            'is_checked' => false,
        ]);

        Livewire::actingAs($user)
            ->test(ShoppingManager::class)
            ->set('activeListId', $activeList->id)
            ->call('updateItem', $otherItem->id, 'name', 'Changed')
            ->call('toggleCheck', $otherItem->id);

        $this->assertSame('Keep me', $otherItem->fresh()->name);
        $this->assertFalse($otherItem->fresh()->is_checked);
    }

    public function test_proof_images_require_authentication_and_children_can_only_view_their_own(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('chores/proofs/proof.jpg', 'image-data');

        $child = User::factory()->create(['is_child' => true]);
        $otherChild = User::factory()->create(['is_child' => true]);
        $chore = Chore::factory()->create([
            'user_id' => $child->id,
            'proof_image_path' => 'chores/proofs/proof.jpg',
        ]);

        $this->get(route('kids.proofs.show', $chore))->assertRedirect(route('login'));
        $this->actingAs($otherChild)->get(route('kids.proofs.show', $chore))->assertForbidden();
        $this->actingAs($child)->get(route('kids.proofs.show', $chore))->assertOk();
    }
}
