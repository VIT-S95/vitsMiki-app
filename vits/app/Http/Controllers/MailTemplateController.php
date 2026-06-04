<?php

namespace App\Http\Controllers;

use App\Models\MailTemplate;
use Illuminate\Http\Request;

class MailTemplateController extends Controller
{
    public function index()
    {
        $templates = MailTemplate::orderBy('nom')->get();
        return view('mail-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('mail-templates.form', ['template' => new MailTemplate()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:255',
            'sujet'     => 'required|string|max:255',
            'corps'     => 'required|string',
            'variables' => 'nullable|string',
            'actif'     => 'boolean',
        ]);

        $data['variables'] = $this->parseVariables($request->variables);
        $data['actif']     = $request->boolean('actif', true);

        MailTemplate::create($data);
        return redirect()->route('mail-templates.index')->with('success', 'Template créé.');
    }

    public function edit(MailTemplate $mailTemplate)
    {
        return view('mail-templates.form', ['template' => $mailTemplate]);
    }

    public function update(Request $request, MailTemplate $mailTemplate)
    {
        $data = $request->validate([
            'nom'       => 'required|string|max:255',
            'sujet'     => 'required|string|max:255',
            'corps'     => 'required|string',
            'variables' => 'nullable|string',
            'actif'     => 'boolean',
        ]);

        $data['variables'] = $this->parseVariables($request->variables);
        $data['actif']     = $request->boolean('actif', true);

        $mailTemplate->update($data);
        return redirect()->route('mail-templates.index')->with('success', 'Template mis à jour.');
    }

    public function destroy(MailTemplate $mailTemplate)
    {
        $mailTemplate->delete();
        return redirect()->route('mail-templates.index')->with('success', 'Template supprimé.');
    }

    private function parseVariables(?string $raw): ?array
    {
        if (!$raw) return null;
        $lines = array_filter(array_map('trim', explode("\n", $raw)));
        return array_values($lines) ?: null;
    }
}
