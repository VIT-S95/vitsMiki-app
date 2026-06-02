<?php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule;

$freqMin = (int) Cache::get('vits.kizeo_frequence_min', config('vits.kizeo_frequence_min', 60));
Schedule::command('kizeo:import')
    ->cron("*/{$freqMin} * * * *")
    ->withoutOverlapping($freqMin);
