<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EventTemplate;
use App\Models\Module;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = EventTemplate::with('modules')
            ->withCount('events')
            ->orderBy('name')
            ->get();

        $modules = Module::orderBy('is_core', 'desc')->orderBy('name')->get();

        return view('admin.templates.index', compact('templates', 'modules'));
    }

    public function create()
    {
        $modules = Module::orderBy('is_core', 'desc')->orderBy('name')->get();
        return view('admin.templates.create', compact('modules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_templates,name',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'participant_label' => 'nullable|string|max:100',
            'entry_label' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template = EventTemplate::create($validated);

        // Attach modules (including core modules)
        $coreModules = Module::where('is_core', true)->pluck('id')->toArray();
        $selectedModules = $request->input('modules', []);
        $allModules = array_unique(array_merge($coreModules, $selectedModules));

        $template->modules()->sync($allModules);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Template created successfully.']);
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template created successfully.');
    }

    public function edit(EventTemplate $template)
    {
        $template->load('modules');

        if (request()->ajax()) {
            return response()->json($template);
        }

        $modules = Module::orderBy('is_core', 'desc')->orderBy('name')->get();
        return view('admin.templates.edit', compact('template', 'modules'));
    }

    public function update(Request $request, EventTemplate $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:event_templates,name,' . $template->id,
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:100',
            'participant_label' => 'nullable|string|max:100',
            'entry_label' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'modules' => 'array',
            'modules.*' => 'exists:modules,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $template->update($validated);

        // Attach modules (including core modules)
        $coreModules = Module::where('is_core', true)->pluck('id')->toArray();
        $selectedModules = $request->input('modules', []);
        $allModules = array_unique(array_merge($coreModules, $selectedModules));

        $template->modules()->sync($allModules);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Template updated successfully.']);
        }

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(EventTemplate $template)
    {
        if ($template->events()->count() > 0) {
            return redirect()->route('admin.templates.index')
                ->with('error', 'Cannot delete template with existing events.');
        }

        $template->modules()->detach();
        $template->delete();

        return redirect()->route('admin.templates.index')
            ->with('success', 'Template deleted successfully.');
    }
}
