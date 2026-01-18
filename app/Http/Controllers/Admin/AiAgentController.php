<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use Illuminate\Http\Request;

class AiAgentController extends Controller
{
    /**
     * Display AI Agents management page.
     */
    public function index()
    {
        $agents = AiAgent::orderBy('display_order')->get();

        return view('admin.ai-agents.index', [
            'agents' => $agents,
            'personalities' => [
                'professional' => 'Professional',
                'friendly' => 'Friendly',
                'concise' => 'Concise',
                'creative' => 'Creative',
            ],
        ]);
    }

    /**
     * Store a new AI agent.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:ai_agents,code',
            'description' => 'nullable|string',
            'system_prompt' => 'required|string',
            'personality' => 'required|in:professional,friendly,concise,creative',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'capabilities' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['capabilities'] = $validated['capabilities'] ?? [];
        $validated['display_order'] = AiAgent::max('display_order') + 1;

        $agent = AiAgent::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Agent created successfully.',
            'agent' => $agent,
        ]);
    }

    /**
     * Update an AI agent.
     */
    public function update(Request $request, AiAgent $agent)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'system_prompt' => 'required|string',
            'personality' => 'required|in:professional,friendly,concise,creative',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'capabilities' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['capabilities'] = $validated['capabilities'] ?? [];

        $agent->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Agent updated successfully.',
            'agent' => $agent,
        ]);
    }

    /**
     * Delete an AI agent.
     */
    public function destroy(AiAgent $agent)
    {
        if ($agent->is_default) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the default agent.',
            ], 400);
        }

        $agent->delete();

        return response()->json([
            'success' => true,
            'message' => 'Agent deleted successfully.',
        ]);
    }

    /**
     * Set an agent as default.
     */
    public function setDefault(AiAgent $agent)
    {
        $agent->setAsDefault();

        return response()->json([
            'success' => true,
            'message' => 'Agent set as default.',
        ]);
    }

    /**
     * Get all agents (API endpoint).
     */
    public function list()
    {
        return response()->json([
            'agents' => AiAgent::orderBy('display_order')->get(),
        ]);
    }

    /**
     * Test agent with a sample prompt.
     */
    public function test(Request $request, AiAgent $agent)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        // Get the full system prompt
        $systemPrompt = $agent->getFullSystemPrompt();

        return response()->json([
            'success' => true,
            'system_prompt' => $systemPrompt,
            'temperature' => $agent->temperature,
            'message' => "Test mode - This would send: '{$request->message}' with the agent's personality applied.",
        ]);
    }
}
