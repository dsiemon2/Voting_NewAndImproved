<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

class ServeCommand extends BaseServeCommand
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Set default port from .env if not provided via command line
        if (!$this->input->getOption('port')) {
            $this->input->setOption('port', env('SERVER_PORT', 8000));
        }

        return parent::handle();
    }
}
