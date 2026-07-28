<?php

namespace App\Http\Controllers;

use App\Services\InvoiceTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceTemplateController extends Controller
{
    public function edit(InvoiceTemplateService $invoiceTemplate): View
    {
        return view('invoice-template.edit', [
            'invoiceTemplate' => $invoiceTemplate->current(),
        ]);
    }

    public function update(Request $request, InvoiceTemplateService $invoiceTemplate): RedirectResponse
    {
        $data = $request->validate([
            'template' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,csv', 'max:10240'],
        ]);

        $invoiceTemplate->store($data['template']);

        return back()->with('status', 'Work log template updated.');
    }

    public function destroy(InvoiceTemplateService $invoiceTemplate): RedirectResponse
    {
        $invoiceTemplate->delete();

        return back()->with('status', 'Work log template removed.');
    }

    public function download(InvoiceTemplateService $invoiceTemplate): BinaryFileResponse|RedirectResponse
    {
        $current = $invoiceTemplate->current();

        if (! $current) {
            return redirect()->route('login')->with('error', 'No work log template is available yet.');
        }

        return response()->download(
            storage_path('app/public/'.$current['path']),
            $current['original_name']
        );
    }
}
