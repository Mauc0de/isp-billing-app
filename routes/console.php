<?php

use App\Jobs\SyncRouterStatuses;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Jadwal Otomasi (Backend 2)
|--------------------------------------------------------------------------
|
| Satu baris `* * * * * php artisan schedule:run` di crontab server sudah
| cukup menjalankan semuanya. Job-nya sendiri yang mengiterasi seluruh tenant,
| jadi tidak perlu mendaftarkan satu jadwal per tenant.
|
| withoutOverlapping dipakai karena pemindaian melibatkan panggilan ke router
| yang bisa memakan waktu; job yang masih jalan akan dilewati pada tick berikutnya.
|
| Schedule::job() otomatis mendispatch ke queue karena job ini mengimplementasikan
| ShouldQueue, jadi schedule worker hanya perlu singkat untuk mengembalikan respons
| dan pekerjaan beratnya dikerjakan worker. Nama queue-nya ('automation') ditetapkan
| di dalam kelas job, sehingga berlaku juga saat job dipanggil manual.
|
| Jadwal pemindaian tagihan lewat jatuhempo (ScanOverdueInvoices) sengaja belum
| didaftarkan di sini karena pipeline suspend-nya masih menunggu penyesuaian ke
| skema penamaan Indonesia.
|
*/

// Pantau konektivitas router, untuk layer monitoring Frontend 2.
Schedule::job(new SyncRouterStatuses)
    ->everyFifteenMinutes()
    ->withoutOverlapping();
