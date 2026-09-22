<?php

namespace Tests\Feature\Livewire\Profile;

use App\Livewire\Profile\AddressCard;
use App\Livewire\Profile\DeleteUserForm;
use App\Livewire\Profile\HeaderCard;
use App\Livewire\Profile\PasswordCard;
use App\Livewire\Profile\PersonalInfoCard;
use App\Livewire\Profile\SocialMediaCard;
use App\Livewire\Profile\TwoFactor\RecoveryCodes;
use App\Livewire\Profile\TwoFactorCard;
use App\Livewire\Profile\UserProfileEdit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileCardsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->profile()->update(['role' => 'admin']);

        return $admin;
    }

    public function test_profile_edit_route_renders_for_authenticated_admin()
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('profile.edit'))
            ->assertOk();
    }

    public function test_header_card_renders_ok()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(HeaderCard::class)
            ->assertOk()
            ->assertSee($admin->name);
    }

    public function test_header_card_edit_button_dispatches_the_event_the_modal_actually_listens_for()
    {
        // The "Editar" button in header-card must open the same "profile-info"
        // x-ui.modal that personal-info-card renders, by dispatching the
        // "open-modal" Alpine event with that modal's name. It previously
        // dispatched a bespoke "open-profile-info-modal" event that nothing
        // listened for, so the button did nothing.
        $admin = $this->admin();

        $html = Livewire::actingAs($admin)
            ->test(HeaderCard::class)
            ->assertOk()
            ->html();

        $this->assertStringContainsString("\$dispatch('open-modal', 'profile-info')", $html);
        $this->assertStringNotContainsString('open-profile-info-modal', $html);
    }

    public function test_personal_info_card_renders_and_updates_profile()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(PersonalInfoCard::class)
            ->assertOk()
            ->set('name', 'Updated Name')
            ->set('email', 'updated@example.com')
            ->set('last_name', 'Sobrenome')
            ->set('nickname', 'meu.nick')
            ->set('phone', '11999999999')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $admin->id,
            'last_name' => 'Sobrenome',
            'nickname' => 'meu.nick',
            'phone' => '11999999999',
        ]);
    }

    public function test_address_card_renders_and_saves_address()
    {
        // zip_code goes through updatedZipCode(), which calls out to viacep.com.br
        // once it looks like a full 8-digit CEP. Fake it so the test is
        // hermetic and doesn't depend on a live third-party API.
        Http::fake();

        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(AddressCard::class)
            ->assertOk()
            ->set('address', 'Rua das Flores, 123')
            ->set('city', 'São Paulo')
            ->set('state', 'SP')
            ->set('zip_code', '01000-000')
            ->call('saveAddress')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $admin->id,
            'address' => 'Rua das Flores, 123',
            'city' => 'São Paulo',
            'state' => 'SP',
            'zip_code' => '01000-000',
        ]);
    }

    public function test_social_media_card_renders_and_updates_links()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(SocialMediaCard::class)
            ->assertOk()
            ->set('instagram', 'meu.instagram')
            ->set('facebook', 'meu.facebook')
            ->set('x', 'meu_x')
            ->set('youtube', 'meucanal')
            ->set('tiktok', 'meutiktok')
            ->set('mere', 'https://mere.example/perfil')
            ->call('updateSocialMedia')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('profiles', [
            'user_id' => $admin->id,
            'instagram' => 'meu.instagram',
            'facebook' => 'meu.facebook',
            'x' => 'meu_x',
            'youtube' => 'meucanal',
            'tiktok' => 'meutiktok',
            'mere' => 'https://mere.example/perfil',
        ]);
    }

    public function test_password_card_renders_and_updates_password()
    {
        $admin = $this->admin();
        $admin->forceFill(['password' => Hash::make('old-password')])->save();

        Livewire::actingAs($admin)
            ->test(PasswordCard::class)
            ->assertOk()
            ->set('current_password', 'old-password')
            ->set('password', 'new-strong-password')
            ->set('password_confirmation', 'new-strong-password')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('new-strong-password', $admin->refresh()->password));
    }

    public function test_password_card_rejects_wrong_current_password()
    {
        $admin = $this->admin();
        $admin->forceFill(['password' => Hash::make('old-password')])->save();

        Livewire::actingAs($admin)
            ->test(PasswordCard::class)
            ->set('current_password', 'wrong-password')
            ->set('password', 'new-strong-password')
            ->set('password_confirmation', 'new-strong-password')
            ->call('updatePassword')
            ->assertHasErrors('current_password');
    }

    public function test_delete_user_form_renders_and_deletes_account_with_correct_password()
    {
        $admin = $this->admin();
        $admin->forceFill(['password' => Hash::make('secret-password')])->save();

        Livewire::actingAs($admin)
            ->test(DeleteUserForm::class)
            ->assertOk()
            ->set('password', 'secret-password')
            ->call('deleteUser');

        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_delete_user_form_rejects_wrong_password()
    {
        $admin = $this->admin();
        $admin->forceFill(['password' => Hash::make('secret-password')])->save();

        Livewire::actingAs($admin)
            ->test(DeleteUserForm::class)
            ->set('password', 'wrong-password')
            ->call('deleteUser')
            ->assertHasErrors('password');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_two_factor_card_renders_ok_when_reached_through_password_card_modal()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(TwoFactorCard::class)
            ->assertOk();
    }

    public function test_recovery_codes_component_renders_ok()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(RecoveryCodes::class)
            ->assertOk();
    }

    public function test_user_profile_edit_wrapper_renders_ok()
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(UserProfileEdit::class)
            ->assertOk();
    }
}
