<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Update status booking otomatis (NONAKTIF sejak fitur check-in/check-out
        // diterapkan — status ongoing/completed sekarang wajib melalui verifikasi
        // kondisi barang oleh admin, bukan otomatis berdasarkan waktu)
        // $schedule->call(function () {
        //     (new \App\Http\Controllers\BookingController)->autoUpdateStatus();
        // })->everyMinute();

        // Contoh bawaan Laravel (tidak dipakai)
        // $schedule->command('inspire')->hourly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}

// namespace App\Console;

// use Illuminate\Console\Scheduling\Schedule;
// use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// class Kernel extends ConsoleKernel
// {
//     /**
//      * Define the application's command schedule.
//      */
//     protected function schedule(Schedule $schedule): void
//     {
//         <?php

// namespace App\Console;

// use Illuminate\Console\Scheduling\Schedule;
// use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

// class Kernel extends ConsoleKernel
// {
//     protected function schedule(Schedule $schedule): void
//     {
        // Update status booking otomatis (NONAKTIF sejak fitur check-in/check-out
        // diterapkan — status ongoing/completed sekarang wajib melalui verifikasi
        // kondisi barang oleh admin, bukan otomatis berdasarkan waktu)
        // $schedule->call(function () {
        //     (new \App\Http\Controllers\BookingController)->autoUpdateStatus();
        // })->everyMinute();

        // Contoh bawaan Laravel (tidak dipakai)
        // $schedule->command('inspire')->hourly();
//     }

//     protected function commands(): void
//     {
//         $this->load(__DIR__.'/Commands');
//         require base_path('routes/console.php');
//     }
// }
//     }

//     /**
//      * Register the commands for the application.
//      */
//     protected function commands(): void
//     {
//         $this->load(__DIR__.'/Commands');

//         require base_path('routes/console.php');
//     }
// }