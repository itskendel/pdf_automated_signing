<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
</head>

<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center justify-center min-h-screen flex-col">
    <div class="max-w-3xl w-full space-y-6">
        <div class="flex justify-between w-fll">
            <div>
                <h1>PDF AUTOMATED SIGNING</h1>
            </div>

            <a href="{{ route('document.create') }}"
                class="px-4 py-2 bg-blue-600 text-white text-xs uppercase tracking-wider">
                upload
            </a>
        </div>

        <div class="w-full grid grid-cols-2 gap-2">
            @foreach ($documents as $document)
                <div class="space-y-3 aspect-square">
                    <embed src="{{ route('document.show', $document->id) }}" type="application/pdf"
                        class="w-full h-full border border-gray-200 rounded"></embed>
                    <div class="inline-flex gap-2">
                        <form id="sign_form" action="{{ route('document.update', $document->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="pdf_file" id="pdf_file"
                                value="{{ route('document.show', $document->id) }}">
                            <input type="hidden" name="signatures" id="signatures">

                            <button id="btn_submit_sign" type="submit" form="sign_form"
                                class="px-4 py-2 bg-green-600 text-white text-xs uppercase tracking-wider">Sign</button>
                        </form>

                        <button class="px-4 py-2 bg-blue-600 text-white text-xs uppercase tracking-wider">View</button>

                        <form id="delete_form" action="{{ route('document.destroy', $document->id) }}" method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="submit" form="delete_form"
                                class="px-4 py-2 bg-red-600 text-white text-xs uppercase tracking-wider">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <script>
        async function findSignatureCoordinates(file) {
            const array_buffer = await file.arrayBuffer();
            const pdf_document = await pdfjsLib.getDocument({
                data: array_buffer
            }).promise;
            const all_signatures = [];

            for (let page_num = 1; page_num <= pdf_document.numPages; page_num++) {
                const page = await pdf_document.getPage(page_num);
                const text_content = await page.getTextContent();
                const viewport = page.getViewport({
                    scale: 1
                });
                const page_height = viewport.height;

                for (const item of text_content.items) {
                    const text = String(item.str).toLowerCase();

                    if (text.includes("signature")) {
                        all_signatures.push({
                            page: page_num,
                            x: Number(item.transform[4].toFixed(3)),
                            y: Number((page_height - item.transform[5]).toFixed(3)),
                            width: Number((item.width || 0).toFixed(3))
                        });
                    }
                }
            }

            return all_signatures;
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const pdf_inputs = document.querySelectorAll('input[name="pdf_file"]');
            for (const input of pdf_inputs) {
                const url = input.value;

                const form = input.parentElement;
                const signatures_input = form.querySelector('input[name="signatures"]');
                const sign_button = form.querySelector('button[type="submit"]');

                sign_button.disabled = true;

                try {
                    const response = await fetch(url);
                    const file = await response.blob();

                    const coordinates = await findSignatureCoordinates(file);
                    signatures_input.value = JSON.stringify(coordinates);

                    if (coordinates.length > 0) {
                        sign_button.disabled = false;
                    }

                } catch (error) {
                    sign_button.disabled = true;
                }
            }
        });
    </script>
</body>

</html>
