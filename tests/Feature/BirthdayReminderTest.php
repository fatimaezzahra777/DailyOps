<?php

namespace Tests\Feature;

use App\Mail\BirthdayTomorrowMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BirthdayReminderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_emails_other_users_when_a_birthday_is_tomorrow_without_duplicates(): void
    {
        Mail::fake();
        $this->travelTo('2026-06-10 08:00:00');

        $birthdayUser = User::factory()->create([
            'name' => 'Sara',
            'birth_date' => '1994-06-11',
        ]);
        $firstRecipient = User::factory()->create(['name' => 'Youssef']);
        $secondRecipient = User::factory()->create(['name' => 'Nadia']);

        $this->artisan('birthdays:send-reminders')->assertSuccessful();

        Mail::assertSent(BirthdayTomorrowMail::class, 2);
        Mail::assertSent(BirthdayTomorrowMail::class, fn (BirthdayTomorrowMail $mail) => $mail->birthdayUser->is($birthdayUser)
            && $mail->recipient->is($firstRecipient)
            && $mail->hasTo($firstRecipient->email)
        );
        Mail::assertSent(BirthdayTomorrowMail::class, fn (BirthdayTomorrowMail $mail) => $mail->recipient->is($secondRecipient)
            && $mail->hasTo($secondRecipient->email)
        );
        Mail::assertNotSent(BirthdayTomorrowMail::class, fn (BirthdayTomorrowMail $mail) => $mail->hasTo($birthdayUser->email)
        );

        $this->artisan('birthdays:send-reminders')->assertSuccessful();

        Mail::assertSent(BirthdayTomorrowMail::class, 2);
        $this->assertSame('2026-06-11', $birthdayUser->fresh()->birthday_reminder_sent_for->format('Y-m-d'));
    }

    public function test_february_29_birthday_is_reminded_on_february_28_in_a_non_leap_year(): void
    {
        Mail::fake();
        $this->travelTo('2027-02-27 08:00:00');

        $birthdayUser = User::factory()->create([
            'birth_date' => '1992-02-29',
        ]);
        User::factory()->create();

        $this->artisan('birthdays:send-reminders')->assertSuccessful();

        Mail::assertSent(BirthdayTomorrowMail::class, fn (BirthdayTomorrowMail $mail) => $mail->birthdayUser->is($birthdayUser)
        );
    }
}
