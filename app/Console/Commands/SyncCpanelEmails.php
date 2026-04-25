<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Console\Command;

class SyncCpanelEmails extends Command
{
    protected $signature = 'cpanel:sync-emails';
    protected $description = 'Sync all existing users with cPanel email accounts';

    protected $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();
        $this->userService = $userService;
    }

    public function handle()
    {
        $users = User::all();
        $this->info("Syncing " . $users->count() . " users...");

        foreach ($users as $user) {
            $result = $this->userService->syncCpanelPassword($user);
            if ($result['success']) {
                $this->info("Synced: {$user->email}");
            } else {
                $this->error("Failed: {$user->email} - {$result['message']}");
            }
        }

        $this->info("Sync completed.");
    }
}