<?php

namespace Tests\Feature;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\Affiliate;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\AffiliateSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AffiliateScopingTest extends TestCase
{
    use RefreshDatabase;

    private Affiliate $a;

    private Affiliate $b;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([AffiliateSeeder::class, RolesAndPermissionsSeeder::class]);
        Filament::setCurrentPanel('admin');

        [$this->a, $this->b] = Affiliate::orderBy('id')->take(2)->get()->all();
    }

    private function member(string $role, ?Affiliate $affiliate = null): User
    {
        $user = User::factory()->create(['affiliate_id' => $affiliate?->id]);
        $user->assignRole($role);

        return $user;
    }

    public function test_affiliate_admin_only_lists_own_affiliate_users(): void
    {
        $admin = $this->member('affiliate_admin', $this->a);
        $mine = $this->member('affiliate_user', $this->a);
        $theirs = $this->member('affiliate_user', $this->b);
        $national = $this->member('national_user');

        Livewire::actingAs($admin)->test(ListUsers::class)
            ->assertCanSeeTableRecords([$admin, $mine])
            ->assertCanNotSeeTableRecords([$theirs, $national]);
    }

    public function test_affiliate_admin_cannot_open_another_affiliates_user_or_event_by_url(): void
    {
        $admin = $this->member('affiliate_admin', $this->a);
        $theirUser = $this->member('affiliate_user', $this->b);
        $theirEvent = Event::factory()->create(['affiliate_id' => $this->b->id]);

        $this->actingAs($admin)->get(UserResource::getUrl('view', ['record' => $theirUser]))->assertNotFound();
        $this->actingAs($admin)->get(EventResource::getUrl('edit', ['record' => $theirEvent]))->assertNotFound();
    }

    public function test_affiliate_user_sees_only_published_events_of_own_affiliate(): void
    {
        $member = $this->member('affiliate_user', $this->a);
        $published = Event::factory()->create(['affiliate_id' => $this->a->id]);
        $draft = Event::factory()->draft()->create(['affiliate_id' => $this->a->id]);
        $other = Event::factory()->create(['affiliate_id' => $this->b->id]);

        Livewire::actingAs($member)->test(ListEvents::class)
            ->assertCanSeeTableRecords([$published])
            ->assertCanNotSeeTableRecords([$draft, $other]);
    }

    public function test_affiliate_user_cannot_create_events_or_list_members(): void
    {
        $member = $this->member('affiliate_user', $this->a);

        $this->actingAs($member)->get(EventResource::getUrl('create'))->assertForbidden();
        $this->actingAs($member)->get(UserResource::getUrl('index'))->assertForbidden();
    }

    public function test_affiliate_user_can_rsvp_and_cancel(): void
    {
        $member = $this->member('affiliate_user', $this->a);
        $event = Event::factory()->create(['affiliate_id' => $this->a->id]);

        Livewire::actingAs($member)->test(ListEvents::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('rsvp')->table($event));

        $this->assertTrue($event->isAttendedBy($member));

        Livewire::actingAs($member)->test(ListEvents::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('cancelRsvp')->table($event));

        $this->assertFalse($event->fresh()->isAttendedBy($member));
    }

    public function test_affiliate_admin_creates_event_pinned_to_own_affiliate(): void
    {
        $admin = $this->member('affiliate_admin', $this->a);

        Livewire::actingAs($admin)->test(CreateEvent::class)
            ->fillForm([
                'title' => 'Rally',
                'starts_at' => now()->addWeek(),
                'venue' => 'Town Hall',
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $event = Event::where('title', 'Rally')->firstOrFail();
        $this->assertSame($this->a->id, $event->affiliate_id);
        $this->assertSame($admin->id, $event->created_by);
        $this->assertTrue($event->isPublished());
    }

    public function test_national_admin_sees_all_affiliates_users_and_events(): void
    {
        $nationalAdmin = $this->member('national_admin');
        $users = collect(Affiliate::all())->map(fn ($aff) => $this->member('affiliate_user', $aff));
        $events = collect(Affiliate::all())->map(fn ($aff) => Event::factory()->create(['affiliate_id' => $aff->id]));

        $this->assertCount(8, Affiliate::all());

        Livewire::actingAs($nationalAdmin)->test(ListUsers::class)->assertCanSeeTableRecords($users);
        Livewire::actingAs($nationalAdmin)->test(ListEvents::class)->assertCanSeeTableRecords($events);
    }

    public function test_only_national_admin_can_promote_and_demote(): void
    {
        $nationalAdmin = $this->member('national_admin');
        $member = $this->member('affiliate_user', $this->a);

        Livewire::actingAs($nationalAdmin)->test(ListUsers::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('promote')->table($member));
        $this->assertTrue($member->fresh()->hasRole('affiliate_admin'));

        Livewire::actingAs($nationalAdmin)->test(ListUsers::class)
            ->callAction(\Filament\Actions\Testing\TestAction::make('demote')->table($member));
        $this->assertTrue($member->fresh()->hasRole('affiliate_user'));

        $affiliateAdmin = $this->member('affiliate_admin', $this->a);
        Livewire::actingAs($affiliateAdmin)->test(ListUsers::class)
            ->assertActionHidden(\Filament\Actions\Testing\TestAction::make('promote')->table($member));
    }

    public function test_dashboard_renders_for_every_role(): void
    {
        Event::factory()->create(['affiliate_id' => $this->a->id]);

        foreach ([
            ['national_admin', null],
            ['national_user', null],
            ['affiliate_admin', $this->a],
            ['affiliate_user', $this->a],
        ] as [$role, $affiliate]) {
            $this->actingAs($this->member($role, $affiliate))->get('/admin')
                ->assertOk()
                ->assertSee(config('branding.logo'), false)
                ->assertSee(config('branding.motto'), false);
        }
    }

    public function test_every_role_can_open_the_panel_but_plain_accounts_cannot(): void
    {
        foreach (['national_admin', 'national_user'] as $role) {
            $this->assertTrue($this->member($role)->canAccessPanel(Filament::getCurrentPanel()));
        }

        $roleless = User::factory()->create();
        $this->assertFalse($roleless->canAccessPanel(Filament::getCurrentPanel()));
    }
}
