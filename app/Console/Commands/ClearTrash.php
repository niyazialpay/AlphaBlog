<?php

namespace App\Console\Commands;

use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\Search;
use Illuminate\Console\Command;

class ClearTrash extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-trash';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Comments::onlyTrashed()->forceDelete();
        Posts::onlyTrashed()->forceDelete();
        Search::where('think', false)->forceDelete();
        $this->info('Recycle bin emptied successfully');
    }
}
