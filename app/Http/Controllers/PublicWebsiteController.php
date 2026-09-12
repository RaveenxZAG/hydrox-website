<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Services\SystemNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicWebsiteController extends Controller
{
    public function home(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.home', compact('business'));
    }

    public function commercial(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.commercial', compact('business'));
    }

    public function residential(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.residential', compact('business'));
    }

    public function ndis(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.ndis', compact('business'));
    }

    public function agedCare(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.aged-care', compact('business'));
    }

    public function industrial(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.industrial', compact('business'));
    }

    public function school(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.school', compact('business'));
    }

    public function lawnCare(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.lawn-care', compact('business'));
    }

    public function concreting(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.services.concreting', compact('business'));
    }

    public function about(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.about', compact('business'));
    }

    public function contact(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.contact', compact('business'));
    }

    public function sendContact(Request $request, SystemNotificationService $notificationService): RedirectResponse
    {
        // Honeypot spam trap
        if ($request->filled('contact_guard_field')) {
            return back()->with('success', 'Thank you for reaching out. We will get back to you shortly.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'company' => ['nullable', 'string', 'max:191'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $notificationService->notify(
            'contact_enquiry',
            "New Website Contact: {$validated['name']}",
            "Name: {$validated['name']}\n"
                ."Company: ".($validated['company'] ?: 'N/A')."\n"
                ."Phone: {$validated['phone']}\n"
                ."Email: {$validated['email']}\n"
                ."Message:\n{$validated['message']}",
            null,
            null,
            true
        );

        return back()->with('success', 'Thank you! Your message has been received. Our team will contact you shortly.');
    }

    public function faq(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.faq', compact('business'));
    }

    public function careers(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.careers', compact('business'));
    }

    public function legal(): View
    {
        $business = SystemSetting::businessInformation();
        return view('pages.legal', compact('business'));
    }
}
