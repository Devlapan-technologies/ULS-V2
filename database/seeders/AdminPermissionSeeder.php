<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\System;
use App\Models\Module;
use App\Models\SubModule;
use App\Models\UserPermission;

class AdminPermissionSeeder extends Seeder
{
    public function run()
    {
        // IMPORTANT: Use the SAME user ID that was created in ULS
        $adminUserId = 1; // Change this to match ULS user ID
        
        $system = System::where('code', 'eclipse9')->first();
        if (!$system) {
            $this->command->error('System not found! Run SystemSeeder first.');
            return;
        }
        
        // Grant module view permissions
        $modules = Module::where('system_id', $system->id)->get();
        foreach ($modules as $module) {
            UserPermission::updateOrCreate(
                [
                    'user_id' => $adminUserId,
                    'system_id' => $system->id,
                    'module_id' => $module->id,
                    'sub_module_id' => null,
                ],
                [
                    'can_view_card' => true,
                    'can_access' => false,
                    'actions' => [],
                ]
            );
        }
        
        // Grant all submodule permissions
        $subModules = SubModule::whereIn('module_id', $modules->pluck('id'))->get();
        foreach ($subModules as $subModule) {
            UserPermission::updateOrCreate(
                [
                    'user_id' => $adminUserId,
                    'system_id' => $system->id,
                    'module_id' => $subModule->module_id,
                    'sub_module_id' => $subModule->id,
                ],
                [
                    'can_view_card' => false,
                    'can_access' => true,
                    'actions' => ['read', 'create', 'update', 'delete'],
                ]
            );
        }
        
        $this->command->info('Admin permissions created in UAS for user ID: ' . $adminUserId);
    }
}