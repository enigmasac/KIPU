<?php

namespace Modules\Sunat\Http\Controllers;

use App\Abstracts\Http\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Modules\Sunat\Models\Certificate;
use Modules\Sunat\Services\CertificateService;

class Settings extends Controller
{
    public function index()
    {
        $certificate = Certificate::forCompany(company_id())->active()->first();

        return view('sunat::settings.index', [
            'certificate' => $certificate,
            'environment' => setting('sunat.environment', 'beta'),
            'autoEmit' => setting('sunat.auto_emit', false),
            'ruc' => setting('sunat.ruc'),
            'solUser' => setting('sunat.sol_user'),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'environment' => 'required|in:beta,production',
            'ruc' => 'nullable|string|size:11',
            'sol_user' => 'nullable|string|max:20',
            'sol_password' => 'nullable|string|max:50',
        ]);

        setting(['sunat.environment' => $request->environment]);
        setting(['sunat.auto_emit' => $request->boolean('auto_emit')]);

        if ($request->filled('ruc')) {
            setting(['sunat.ruc' => $request->ruc]);
        }
        if ($request->filled('sol_user')) {
            setting(['sunat.sol_user' => $request->sol_user]);
        }
        if ($request->filled('sol_password')) {
            setting(['sunat.sol_password' => Crypt::encryptString($request->sol_password)]);
        }

        setting()->save();
        flash(trans('messages.success.updated', ['type' => 'SUNAT']))->success();

        return redirect()->route('sunat.configuration.index');
    }

    public function certificate()
    {
        $certificates = Certificate::forCompany(company_id())->orderBy('created_at', 'desc')->get();
        return view('sunat::settings.certificate', compact('certificates'));
    }

    public function uploadCertificate(Request $request, CertificateService $service)
    {
        \Log::info('Llegó petición de upload', [
            'data' => $request->except(['_token', 'certificate_password']),
            'files' => array_keys($request->files->all()),
            'has_file' => $request->hasFile('certificate_file'),
        ]);

        $request->validate([
            'certificate_file' => [
                'required',
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $extension = strtolower($value->getClientOriginalExtension());
                    if (!in_array($extension, ['pfx', 'p12'])) {
                        $fail('El archivo debe ser un certificado .pfx o .p12');
                    }
                },
            ],
            'certificate_password' => 'required|string',
        ]);

        try {
            $service->uploadCertificate(
                $request->file('certificate_file'),
                $request->certificate_password,
                company_id()
            );
            flash('Certificado subido correctamente')->success();
        } catch (\Exception $e) {
            flash($e->getMessage())->error();
        }

        return redirect()->route('sunat.configuration.certificate');
    }

    public function deleteCertificate(Certificate $certificate)
    {
        // Verificar que el certificado pertenece a la compañía actual
        if ($certificate->company_id !== company_id()) {
            abort(403);
        }

        $certificate->delete();
        flash('Certificado eliminado')->success();
        return redirect()->route('sunat.configuration.certificate');
    }
}
