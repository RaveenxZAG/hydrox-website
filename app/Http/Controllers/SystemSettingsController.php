<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SystemSettingsController extends Controller
{
    public function editBusiness(): View
    {
        return view('settings.business', [
            'business' => SystemSetting::businessInformation(),
        ]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'abn' => ['required', 'regex:/^\d{11}$/'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'billing_email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'mobile_primary' => ['nullable', 'string', 'max:40'],
            'mobile_secondary' => ['nullable', 'string', 'max:40'],
            'address_line_1' => ['required', 'string', 'max:120'],
            'address_line_2' => ['nullable', 'string', 'max:120'],
            'address_line_3' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:60'],
            'postcode' => ['required', 'regex:/^\d{4}$/'],
            'country' => ['required', 'string', 'max:120'],
        ], [
            'abn.regex' => 'The ABN must contain exactly 11 numbers.',
            'postcode.regex' => 'The postcode must contain exactly 4 numbers.',
        ]);

        SystemSetting::setValue('business_information', json_encode($data, JSON_UNESCAPED_SLASHES));

        return back()->with('status', 'Business information updated.');
    }

    public function edit(): View
    {
        return view('settings.maintenance');
    }

    public function clearCache(): RedirectResponse
    {
        foreach (['optimize:clear', 'cache:clear', 'view:clear', 'route:clear', 'config:clear'] as $command) {
            Artisan::call($command);
        }

        return back()->with('status', 'System cache cleared.');
    }

    public function clearTemporaryData(): RedirectResponse
    {
        Storage::disk('public')->deleteDirectory('report-temp');
        Storage::disk('public')->makeDirectory('report-temp');

        File::deleteDirectory(storage_path('app/pdf-cache'));
        File::ensureDirectoryExists(storage_path('app/pdf-cache'));

        Artisan::call('cache:clear');

        return back()->with('status', 'Temporary uploads and cache data cleared.');
    }
}
