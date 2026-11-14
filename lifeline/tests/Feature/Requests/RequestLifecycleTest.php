<?php

namespace Tests\Feature\Requests;

use App\Models\BloodRequest;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

/** Reference numbers (DEC-006) and the status lifecycle with its history. */
class RequestLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function details(): array
    {
        return [
            'hospital_id' => Hospital::factory()->create()->id,
            'patient_name' => 'Juan Dela Cruz',
            'blood_type' => 'O+',
            'units' => 2,
            'urgency' => 'critical',
            'notes' => null,
        ];
    }

    public function test_submit_saves_an_open_request_with_a_reference_and_first_history_row(): void
    {
        $requester = User::factory()->create(['role' => 'requester']);

        $bloodRequest = BloodRequest::submit($requester, $this->details());

        $this->assertMatchesRegularExpression('/^LL-\d{6}-[A-HJ-NP-Z2-9]{6}$/', $bloodRequest->reference_number);
        $this->assertSame('open', $bloodRequest->fresh()->status);
        $this->assertSame($requester->id, $bloodRequest->requester_id);
        $this->assertDatabaseHas('request_history', [
            'blood_request_id' => $bloodRequest->id,
            'changed_by' => $requester->id,
            'old_status' => null,
            'new_status' => 'open',
            'remarks' => 'Request submitted',
        ]);
    }

    public function test_submit_skips_a_reference_that_already_exists(): void
    {
        BloodRequest::factory()->create(['reference_number' => 'LL-261114-AAAAAA']);
        $references = ['LL-261114-AAAAAA', 'LL-261114-BBBBBB'];

        $bloodRequest = BloodRequest::submit(User::factory()->create(), $this->details(), function () use (&$references) {
            return array_shift($references);
        });

        $this->assertSame('LL-261114-BBBBBB', $bloodRequest->reference_number);
    }

    public function test_submit_retries_when_the_database_rejects_a_duplicate_reference(): void
    {
        // Simulates another request saving the same reference between the check and the insert.
        // The first insert fails on the UNIQUE index, so submit() must try the next reference.
        $duplicated = false;
        BloodRequest::creating(function (BloodRequest $bloodRequest) use (&$duplicated) {
            if (! $duplicated) {
                $duplicated = true;
                BloodRequest::factory()->create(['reference_number' => $bloodRequest->reference_number]);
            }
        });
        $references = ['LL-261114-CCCCCC', 'LL-261114-DDDDDD'];

        $bloodRequest = BloodRequest::submit(User::factory()->create(), $this->details(), function () use (&$references) {
            return array_shift($references);
        });

        $this->assertSame('LL-261114-DDDDDD', $bloodRequest->reference_number);
        $this->assertSame(1, BloodRequest::where('reference_number', 'LL-261114-DDDDDD')->count());
    }

    public function test_submit_gives_up_after_five_taken_references(): void
    {
        BloodRequest::factory()->create(['reference_number' => 'LL-261114-AAAAAA']);

        $this->expectException(RuntimeException::class);
        BloodRequest::submit(User::factory()->create(), $this->details(), fn () => 'LL-261114-AAAAAA');
    }

    public function test_every_status_change_writes_request_history(): void
    {
        $requester = User::factory()->create();
        $bloodRequest = BloodRequest::submit($requester, $this->details());

        $bloodRequest->changeStatus('donor_found', null, 'Donor accepted');
        $bloodRequest->changeStatus('fulfilled', $requester, 'Marked fulfilled by requester');

        $this->assertSame('fulfilled', $bloodRequest->fresh()->status);
        $this->assertSame(
            [[null, 'open'], ['open', 'donor_found'], ['donor_found', 'fulfilled']],
            $bloodRequest->history()->orderBy('id')->get()
                ->map(fn ($entry) => [$entry->old_status, $entry->new_status])
                ->all()
        );
    }

    public static function forbiddenChanges(): array
    {
        return [
            'fulfilled is final' => ['fulfilled', 'open'],
            'cancelled is final' => ['cancelled', 'fulfilled'],
            'expired is final' => ['expired', 'cancelled'],
            'donor found cannot reopen' => ['donor_found', 'open'],
            'same status' => ['open', 'open'],
            'unknown status' => ['open', 'approved'],
        ];
    }

    #[DataProvider('forbiddenChanges')]
    public function test_forbidden_status_changes_are_refused_without_history(string $from, string $to): void
    {
        $bloodRequest = BloodRequest::factory()->create(['status' => $from]);

        try {
            $bloodRequest->changeStatus($to, null);
            $this->fail("Changing {$from} to {$to} should be refused.");
        } catch (InvalidArgumentException) {
            // Expected.
        }

        $this->assertSame($from, $bloodRequest->fresh()->status);
        $this->assertSame(0, $bloodRequest->history()->count());
    }

    public function test_a_stale_copy_cannot_change_a_request_that_was_already_closed(): void
    {
        $bloodRequest = BloodRequest::factory()->create();
        $staleCopy = BloodRequest::find($bloodRequest->id);
        $bloodRequest->changeStatus('cancelled', null);

        $this->expectException(InvalidArgumentException::class);
        $staleCopy->changeStatus('fulfilled', null);
    }

    public function test_status_reference_and_requester_are_not_mass_assignable(): void
    {
        $bloodRequest = new BloodRequest([
            'status' => 'fulfilled',
            'reference_number' => 'LL-261114-AAAAAA',
            'requester_id' => 1,
        ]);

        $this->assertSame([], $bloodRequest->getAttributes());
    }

    public function test_every_status_has_a_label_and_a_transition_rule(): void
    {
        $this->assertSame(BloodRequest::STATUSES, array_keys(BloodRequest::STATUS_LABELS));
        $this->assertSame(BloodRequest::STATUSES, array_keys(BloodRequest::TRANSITIONS));
    }
}
