<?php


namespace Plugisto\Tests\helpers;


use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'testcommand:install';

    public function handle(): void
    {

    }
}