<?php

namespace App\Http\Controllers\Install;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class Language extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $locale = config('app.locale');

        $all_langs = language()->allowed();
        $lang_allowed = [];

        foreach (['es-ES', 'en-US'] as $code) {
            if (isset($all_langs[$code])) {
                $lang_allowed[$code] = $all_langs[$code];
            }
        }

        if (! $locale || ! array_key_exists($locale, $lang_allowed)) {
            $locale = 'es-ES';
        }

        return view('install.language.create', compact('locale', 'lang_allowed'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        // Set locale
        session(['locale' => $request->get('lang')]);
        app()->setLocale($request->get('lang'));

        $response['redirect'] = route('install.database');

        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function getLanguages()
    {
        $all_langs = language()->allowed();
        $languages = [];

        foreach (['es-ES', 'en-US'] as $code) {
            if (isset($all_langs[$code])) {
                $languages[$code] = $all_langs[$code];
            }
        }

        return response()->json([
            'languages' => $languages,
        ]);
    }
}
