<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function signDocument(Request $request)
    {
        $validated = $request->validate([
            'pdf' => 'required|mimes:pdf|max:10000',
            'signatures' => 'required|json',
        ]);

        $signatures = json_decode($validated['signatures'], true);

        if (empty($signatures)) {
            return back()->with('error', 'No signature coordinates found.');
        }

        $path = $request->file('pdf')->store('incoming', 'public');
        $sourceFile = storage_path("app/public/{$path}");

        $signatureImg = storage_path("app/private/signatures/sign.png");
        $outputFile = storage_path('app/signed/signed_' . time() . '.pdf');

        if (!file_exists($signatureImg)) {
            throw new \Exception('Signature image file not found on server at: ' . $signatureImg);
        }

        $pdf = new Fpdi('P', 'pt');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setAutoPageBreak(false);

        $pageCount = $pdf->setSourceFile($sourceFile);

        // Group signatures by page for efficiency
        $signaturesByPage = [];
        foreach ($signatures as $sig) {
            $signaturesByPage[$sig['page']][] = $sig;
        }

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tpl = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tpl);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            // Apply all signatures for this page
            if (isset($signaturesByPage[$pageNo])) {
                foreach ($signaturesByPage[$pageNo] as $sig) {
                    $w = 150;
                    $h = 60;
                    $x = $sig['x'] - 45;
                    $y = $sig['y'] - 55;

                    $pdf->Image($signatureImg, $x, $y, $w, $h, 'PNG');
                }
            }
        }

        $pdf->Output($outputFile, 'F');
        return response()->download($outputFile)->deleteFileAfterSend(true);
    }
}
