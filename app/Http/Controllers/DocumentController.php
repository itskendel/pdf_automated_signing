<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Tcpdf\Fpdi;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $documents = Document::all();

        return view('welcome', compact('documents'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdf'   =>  'required'
        ]);

        if ($request->hasFile('pdf')) {
            $path = $request->pdf->store('pdfs');

            try {
                Document::create([
                    'path'      => $path
                ]);

                return redirect()
                    ->route('document.index');
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $path = Document::find(($id));

        if (!Storage::exists($path->path)) {
            abort(404);
        }

        $file = Storage::get($path->path);

        return new Response($file, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $path->path . '"',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $path = Document::find(($id));

        if (!Storage::exists($path->path)) {
            abort(404);
        }

        try {
            $request->validate([
                'signatures'  =>  'required'
            ]);

            $pdf = new Fpdi('P', 'pt');
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->setAutoPageBreak(false);

            $source_file = storage_path("app/private/" . $path->path);
            $sign_image = storage_path("app/private/signatures/sign.png");
            $page_count = $pdf->setSourceFile($source_file);
            $signature_pages = [];

            $signatures = json_decode(($request->signatures), true);
            foreach ($signatures as $sign) {
                $signature_pages[$sign['page']][] = $sign;
            }

            for ($page_no = 1; $page_no <= $page_count; $page_no++) {
                $tpl = $pdf->importPage($page_no);
                $size = $pdf->getTemplateSize($tpl);
                $orientation = ($size['width'] > $size['height'] ? 'L' : 'P');

                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);

                if (isset($signature_pages[$page_no])) {
                    foreach ($signature_pages[$page_no] as $sign) {
                        $w = 150;
                        $h = 60;
                        $x = $sign['x'] - 45;
                        $y = $sign['y'] - 55;

                        $pdf->Image($sign_image, $x, $y, $w, $h, 'PNG');
                    }
                }
            }

            $pdf_content = $pdf->Output('', 'S');
            $file_name = 'signed/signed_' . time() . '.pdf';

            Storage::put($file_name, $pdf_content);

            return redirect()
                ->back();
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return redirect()
                ->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $document = Document::find($id);

        if (Storage::exists($document->path)) {
            Storage::delete($document->path);
            $document->delete();
        }

        return redirect()->back();
    }
}
