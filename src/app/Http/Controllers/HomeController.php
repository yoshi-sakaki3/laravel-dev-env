<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

/** 環境チェック画面（PHP・Laravel・DB がそろっているかを1画面で確かめる） */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        try {
            $dbVersion = DB::scalar('select version()');
            $db = ['ok' => true, 'detail' => 'MySQL '.$dbVersion];
        } catch (Throwable $e) {
            $db = ['ok' => false, 'detail' => $e->getMessage()];
        }

        return view('home', [
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => Application::VERSION,
            'db' => $db,
        ]);
    }
}
