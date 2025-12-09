<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Console\Command;

class TestBranchQuery extends Command
{
    protected $signature = 'test:branch-query';
    protected $description = 'Test the branch query for super admin';

    public function handle()
    {
        $superAdmin = User::where('type', 'super admin')->first();
        
        if (!$superAdmin) {
            $this->error('No super admin found!');
            return 1;
        }

        $this->info("Super Admin ID: {$superAdmin->id}");
        $this->info("Super Admin creatorId(): {$superAdmin->creatorId()}");
        
        // Test the query
        $branches = Branch::where(function($query) use ($superAdmin) {
            $query->where('created_by', '=', $superAdmin->creatorId())
                  ->orWhere('created_by', '=', 0);
        })->get();

        $this->info("Total branches found: " . $branches->count());
        
        if ($branches->count() > 0) {
            $this->info("Branches:");
            foreach ($branches as $branch) {
                $this->line("  - {$branch->name} (created_by: {$branch->created_by})");
            }
        } else {
            $this->warn("No branches found!");
            $this->info("All branches in DB:");
            $allBranches = Branch::all();
            foreach ($allBranches as $branch) {
                $this->line("  - {$branch->name} (created_by: {$branch->created_by})");
            }
        }

        return 0;
    }
}

