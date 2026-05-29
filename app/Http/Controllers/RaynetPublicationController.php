<?php
namespace App\Http\Controllers;

use App\Models\RaynetPublication;
use Illuminate\Http\Request;

class RaynetPublicationController extends Controller
{
    public function news()
    {
        $current = RaynetPublication::published()->ofType('news')->current()->latest('published_date')->first();
        $archive = RaynetPublication::published()->ofType('news')->where('is_current', false)->orderByDesc('published_date')->get();
        return view('pages.publications.news', compact('current','archive'));
    }

    public function checkpoint()
    {
        $current = RaynetPublication::published()->ofType('checkpoint')->current()->latest('published_date')->first();
        $archive = RaynetPublication::published()->ofType('checkpoint')->where('is_current', false)->orderByDesc('published_date')->get();
        return view('pages.publications.checkpoint', compact('current','archive'));
    }
}
