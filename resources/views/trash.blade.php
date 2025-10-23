<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center justify-center min-h-screen flex-col">
    <div class="max-w-lg w-full">
        <form id="pdfSignerForm" action="{{ route('pdf_signer') }}" method="POST" enctype="multipart/form-data"
            class="space-y-5">
            @csrf

            <div>
                <label for="pdf_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Select PDF Document:
                </label>

                <input type="file" name="pdf" id="pdf_file" accept=".pdf"
                    class="w-full p-2 border border-gray-300 dark:border-gray-700 rounded-md
                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm
                        file:font-semibold file:bg-blue-50 dark:file:bg-blue-900
                        file:text-blue-700 dark:file:text-blue-300
                        hover:file:bg-blue-100 dark:hover:file:bg-blue-800 cursor-pointer">

                <input type="hidden" name="signatures" id="signatures">
            </div>

            <button type="submit" class="bg-blue-600 w-full text-white p-3 rounded-lg font-medium hover:bg-blue-700">
                Submit
            </button>
        </form>
    </div>

    <script>
        async function findSignatureCoordinates(file) {
            const arrayBuffer = await file.arrayBuffer();
            const pdf = await pdfjsLib.getDocument({
                data: arrayBuffer
            }).promise;
            const allSignatures = [];

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const textContent = await page.getTextContent();
                const viewport = page.getViewport({
                    scale: 1
                });
                const pageHeight = viewport.height;

                for (const item of textContent.items) {
                    const text = String(item.str).toLowerCase();

                    if (text.includes("signature")) {
                        allSignatures.push({
                            page: pageNum,
                            x: Number(item.transform[4].toFixed(3)),
                            y: Number((pageHeight - item.transform[5]).toFixed(3)),
                            width: Number((item.width || 0).toFixed(3))
                        });
                    }
                }
            }

            return allSignatures;
        }

        document.getElementById("pdf_file").addEventListener("change", async (e) => {
            const file = e.target.files?.[0];
            if (!file || file.type !== "application/pdf") return;

            try {
                const coordinates = await findSignatureCoordinates(file);
                document.getElementById("signatures").value = JSON.stringify(coordinates);
                console.log("Detected signature coordinates:", coordinates);
            } catch (err) {
                console.error("Error extracting coordinates:", err);
            }
        });
    </script>

</body>

</html>
