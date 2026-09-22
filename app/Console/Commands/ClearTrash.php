<?php

namespace App\Console\Commands;

use App\Models\Post\Comments;
use App\Models\Post\Posts;
use App\Models\Search;
use Illuminate\Console\Command;

class ClearTrash extends Command
{
    /**
     * @var string
     */
    protected $signature = 'app:clear-trash';

    /**
     * @var string
     */
    protected $description = 'Command description';

    public function handle()
    {
        Comments::onlyTrashed()->forceDelete();
        Posts::onlyTrashed()->forceDelete();
        Search::where('think', false)->forceDelete();
        $this->info('Recycle bin emptied successfully');
    }
}
