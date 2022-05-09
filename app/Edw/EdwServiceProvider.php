<?php

namespace App\Edw;

use Illuminate\Support\ServiceProvider;

class EdwServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(EdwConnection::class, function()
        {
            return new EdwConnection(config('database.connections.edw'));
        });
    }
}
