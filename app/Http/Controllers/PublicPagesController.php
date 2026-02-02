<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PublicPagesController extends Controller
{
    /**
     * Page de documentation
     */
    public function documentation()
    {
        return view('public.documentation');
    }

    /**
     * Page FAQ
     */
    public function faq()
    {
        return view('public.faq');
    }

    /**
     * Page de support
     */
    public function support()
    {
        return view('public.support');
    }

    /**
     * Page de licence
     */
    public function license()
    {
        return view('public.license');
    }
}
