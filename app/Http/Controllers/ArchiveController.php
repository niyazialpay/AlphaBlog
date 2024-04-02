<?php

namespace App\Http\Controllers;

use App\Models\Post\Posts;

class ArchiveController extends Controller
{
    public function show($language, $archives, $year, $month = null, $day = null)
    {
        if ($day != null) {
            $start_date = $year.'-'.$month.'-'.$day.' 00:00:00';
            $end_date = $year.'-'.$month.'-'.$day.' 23:59:59';
            $date = $year.'-'.$month.'-'.$day;
        } elseif ($month != null) {
            $start_date = $year.'-'.$month.'-01 00:00:00';
            $end_date = $year.'-'.$month.'-31 23:59:59';
            $date = $year.'-'.$month;
        } else {
            $start_date = $year.'-01-01 00:00:00';
            $end_date = $year.'-12-31 23:59:59';
            $date = $year;
        }
        $posts = Posts::with(['user', 'categories'])
            ->where('post_type', 'post')
            ->where('language', $language)
            ->where('is_published', true)
            ->whereDate('created_at', '>=', dateformat($start_date, 'Y-m-d H:i:s', config('app.timezone')))
            ->whereDate('created_at', '<=', dateformat($end_date, 'Y-m-d H:i:s', config('app.timezone')))
            ->orderBy('created_at', 'desc')
            ->paginate(10)->withQueryString();

        return view('themes.'.app('theme')->name.'.archive', [
            'posts' => $posts,
            'date' => $date,
            'day' => $day,
            'month' => $month,
            'year' => $year,
        ]);
    }
}
