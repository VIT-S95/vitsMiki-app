<?php
use Illuminate\Support\Facades\Schedule;

$freqMin = config('vits.kizeo_frequence_min', 60);
Schedule::command('kizeo:import')
    ->cron("*/{$freqMin} * * * *")
    ->withoutOverlapping();
