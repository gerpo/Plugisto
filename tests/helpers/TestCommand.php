<?php


namespace Gerpo\plugisto\tests\helpers;


use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'testcommand:install';

    public function handle(): void
    {

    }
}