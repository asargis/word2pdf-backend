<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class UploadController extends BaseController
{
    use ValidatesRequests;

    public function upload(UploadRequest $request)
    {
        try {
            $file = $request->file('file');
            $file->storeAs('public', $file->getClientOriginalName());

            return new JsonResponse([
                'status' => 'success',
                'filename' => $file->getClientOriginalName(),
            ]);
        } catch (\Throwable $exception) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function edit(Request $request)
    {
        $fullName = $request->post('fullName');
        $fileName = $request->post('fileName');
        $pdfDir = storage_path('app') . "/public/";
        $outfile = storage_path('app') . "/public/" . pathinfo($fileName, PATHINFO_FILENAME) . ".docx";

        try {
            $templateProcessor = new TemplateProcessor(storage_path('app') . "/public/" . $fileName);
            $templateProcessor->setValue('fullname', $fullName);
            $templateProcessor->saveAs($outfile);

            exec("export HOME=/tmp/ && /usr/bin/libreoffice --headless --convert-to pdf --outdir $pdfDir $outfile");

            $file = $pdfDir . "bio.pdf";

            $headers = ['Content-Type', 'application/pdf'];

            return response()->download($file, 'test.pdf', $headers);

        } catch (\Throwable $exception) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
