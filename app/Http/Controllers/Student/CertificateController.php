<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificateService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function show(Request $request, Certificate $certificate): View
    {
        $this->authorize('view', $certificate);
        $certificate->load(['student', 'result.exam']);

        return view('student.certificates.show', compact('certificate'));
    }

    public function download(Request $request, Certificate $certificate)
    {
        $this->authorize('view', $certificate);
        $certificate->load(['student', 'result.exam']);

        return Pdf::loadView('certificates.pdf', compact('certificate'))->setPaper('a4', 'landscape')->download($certificate->certificate_number.'.pdf');
    }

    public function verify(string $number): View
    {
        $certificate = app(CertificateService::class)->verify($number);

        return view('certificates.verify', compact('certificate', 'number'));
    }
}
