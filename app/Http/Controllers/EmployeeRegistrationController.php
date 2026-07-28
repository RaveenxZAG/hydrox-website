<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class EmployeeRegistrationController extends Controller
{
    public function create(): RedirectResponse
    {
        return redirect()->route('subcontractor-onboardings.create');
    }

    public function index(): RedirectResponse
    {
        return redirect()->route('subcontractor-onboardings.index');
    }

    public function show($employeeRegistration): RedirectResponse
    {
        return redirect()->route('subcontractor-onboardings.index');
    }
}
