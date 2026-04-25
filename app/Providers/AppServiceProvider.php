<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuHarian;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['partials.navbar', 'partials.sidebar'], function ($view) {
            if (!Auth::check()) return;
        
            $user  = Auth::user();
            $today = today();
        
            $query = MenuHarian::where('status', 'final')
                ->whereYear('tanggal',  $today->year)
                ->whereMonth('tanggal', $today->month);
        
            if ($user->role === 'pengelola') {
                $query->where('unit_sppg', $user->unit_sppg);
            }
        
            $totalAlert = 0;
            $navAlerts  = [];
        
            foreach ($query->get() as $menu) {
                $s = $menu->statusAnggaran();
                if ($s === 'over') {
                    $totalAlert++;
                    $navAlerts[] = [
                        'type'    => 'danger',
                        'msg'     => 'Menu ' . ($menu->nama_menu ?? $menu->tanggal->format('d/m/Y'))
                                     . ' melebihi anggaran',
                        'time'    => $menu->tanggal->format('d/m/Y'),
                        'menu_id' => $menu->id,
                    ];
                } elseif ($s === 'warning') {
                    $totalAlert++;
                    $navAlerts[] = [
                        'type'    => 'warning',
                        'msg'     => 'Menu ' . ($menu->nama_menu ?? $menu->tanggal->format('d/m/Y'))
                                     . ' mendekati batas anggaran',
                        'time'    => $menu->tanggal->format('d/m/Y'),
                        'menu_id' => $menu->id,
                    ];
                }
            }
        
            $view->with('navAlertCount', $totalAlert);
            $view->with('navAlerts', array_slice($navAlerts, 0, 5));
        });
    }
}
