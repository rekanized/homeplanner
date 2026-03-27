<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::set('module_economy_enabled', 'true', 'modules');
        Setting::set('module_shopping_enabled', 'true', 'modules');
        Setting::set('module_todo_enabled', 'true', 'modules');
        Setting::set('module_kids_enabled', 'true', 'modules');
    }
}
