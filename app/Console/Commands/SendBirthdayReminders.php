<?php

namespace App\Console\Commands;

use App\Mail\BirthdayTomorrowMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBirthdayReminders extends Command
{
    protected $signature = 'birthdays:send-reminders';

    protected $description = 'Notify users about birthdays scheduled for tomorrow';

    public function handle(): int
    {
        $tomorrow = now(config('app.timezone'))->addDay()->startOfDay();
        $birthdayUsers = User::query()
            ->whereNotNull('birth_date')
            ->get()
            ->filter(fn (User $user) => $user->hasBirthdayOn($tomorrow));

        $sent = 0;

        foreach ($birthdayUsers as $birthdayUser) {
            if ($birthdayUser->birthday_reminder_sent_for?->isSameDay($tomorrow)) {
                continue;
            }

            User::query()
                ->whereKeyNot($birthdayUser->id)
                ->whereNotNull('email')
                ->each(function (User $recipient) use ($birthdayUser, &$sent) {
                    Mail::to($recipient)->send(new BirthdayTomorrowMail($birthdayUser, $recipient));
                    $sent++;
                });

            $birthdayUser->forceFill([
                'birthday_reminder_sent_for' => $tomorrow,
            ])->saveQuietly();
        }

        $this->info("{$sent} birthday reminder(s) sent.");

        return self::SUCCESS;
    }
}
