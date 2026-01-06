<?php

namespace Modules\Sunat\Http\Controllers;

use App\Abstracts\Http\Controller;
use Modules\Sunat\Models\Emission;

class EmissionController extends Controller
{
    public function index()
    {
        $emissions = Emission::where('company_id', company_id())
            ->with('document')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('sunat::emissions.index', compact('emissions'));
    }

    public function show(Emission $emission)
    {
        return view('sunat::emissions.show', compact('emission'));
    }

    public function retry(Emission $emission)
    {
        if (!$emission->canRetry()) {
            flash('No se puede reintentar esta emisión')->warning();
            return back();
        }

        // TODO: Implementar reintento
        flash('Reintento programado')->info();
        return back();
    }
    public function downloadXml(Emission $emission)
    {
        if (empty($emission->xml_path) || !\Illuminate\Support\Facades\Storage::exists($emission->xml_path)) {
            flash('XML file not found')->error();
            return back();
        }

        return \Illuminate\Support\Facades\Storage::download($emission->xml_path);
    }

    public function downloadCdr(Emission $emission)
    {
        if (empty($emission->cdr_path) || !\Illuminate\Support\Facades\Storage::exists($emission->cdr_path)) {
            flash('CDR file not found')->error();
            return back();
        }

        return \Illuminate\Support\Facades\Storage::download($emission->cdr_path);
    }
}
