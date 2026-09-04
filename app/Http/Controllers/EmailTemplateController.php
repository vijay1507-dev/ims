<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class EmailTemplateController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('settings.manage');

        return Inertia::render('EmailTemplates/Index', [
            'templates' => \App\Models\EmailTemplate::latest()->get(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('settings.manage');

        return Inertia::render('EmailTemplates/Create');
    }

    public function store(Request $request)
    {
        Gate::authorize('settings.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        \App\Models\EmailTemplate::create($validated);

        return redirect()->route('email-templates.index')->with('success', 'Email template created successfully.');
    }

    public function edit(\App\Models\EmailTemplate $template): Response
    {
        Gate::authorize('settings.manage');

        return Inertia::render('EmailTemplates/Edit', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, \App\Models\EmailTemplate $template)
    {
        Gate::authorize('settings.manage');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $template->update($validated);

        return redirect()->route('email-templates.index')->with('success', 'Email template updated successfully.');
    }

    public function destroy(\App\Models\EmailTemplate $template)
    {
        Gate::authorize('settings.manage');

        $template->delete();

        return redirect()->back()->with('success', 'Email template deleted successfully.');
    }
}
