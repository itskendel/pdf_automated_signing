import "./bootstrap";
import * as pdfjsLib from "pdfjs-dist/build/pdf.mjs";

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL(
    "pdfjs-dist/build/pdf.worker.mjs",
    import.meta.url
).toString();

// async function findSignatureCoordinates(file) {
//     const arrayBuffer = await file.arrayBuffer();
//     const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
//     const signatures = [];

//     for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
//         const page = await pdf.getPage(pageNum);
//         const textContent = await page.getTextContent();
//         const viewport = page.getViewport({ scale: 1 });

//         for (const item of textContent.items) {
//             if (item.str.toLowerCase().includes("signature")) {
//                 signatures.push({
//                     page: pageNum,
//                     x: item.transform[4],
//                     y: viewport.height - item.transform[5],
//                     width: item.width,
//                 });
//             }
//         }
//     }

//     return signatures;
// }

// document.addEventListener("DOMContentLoaded", () => {
//     const form = document.querySelector("#pdfSignerForm");
//     const pdfInput = document.getElementById("pdf_file");

//     if (!form || !pdfInput) return;

//     const status = document.createElement("span");
//     status.className = "text-sm text-gray-600 dark:text-gray-300";
//     pdfInput.after(status);

//     pdfInput.addEventListener("change", async (e) => {
//         const file = e.target.files[0];
//         if (!file) return;

//         status.textContent = "Analyzing PDF...";
//         form.querySelectorAll('input[name^="sig_"]').forEach(input => input.remove());

//         try {
//             const signatures = await findSignatureCoordinates(file);

//             if (signatures.length > 0) {
//                 status.textContent = `✓ Found ${signatures.length} signature line${signatures.length > 1 ? 's' : ''}`;

//                 const input = document.createElement("input");
//                 input.type = "hidden";
//                 input.name = "signatures";
//                 input.value = JSON.stringify(signatures);
//                 form.appendChild(input);
//             } else {
//                 status.textContent = "No signature lines detected.";
//             }
//         } catch (error) {
//             console.error(error);
//             status.textContent = "Error analyzing PDF.";
//         }
//     });
// });
