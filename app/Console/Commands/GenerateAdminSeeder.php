<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAdminSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:iseed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generate admin table seeder file.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $table = ['admin_menu', 'admin_permissions', 'admin_permission_menu', 'admin_roles', 'admin_role_menu', 'admin_role_permissions'];
        return $this->call("iseed", [
            'tables' => implode(',', $table),
            '--force' => true,
        ]);
    }
}
