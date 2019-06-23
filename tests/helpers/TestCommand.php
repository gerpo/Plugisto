<?php


namespace Plugisto\Tests\Helpers;


use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'testcommand:install';

    public function handle(): void
    {

    }
}