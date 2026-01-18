<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use App\Models\EventTemplate;
use App\Models\VotingType;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    public function index()
    {
        return Event::with(['template', 'state'])->paginate(15);
    }

    public function active()
    {
        return Event::where('is_active', true)
            ->with(['template', 'state'])
            ->get();
    }

    public function show(Event $event)
    {
        return $event->load(['template', 'votingConfig.votingType.placeConfigs', 'state']);
    }

    public function divisions(Event $event)
    {
        return $event->divisions()->active()->ordered()->get();
    }

    public function entries(Event $event)
    {
        return $event->entries()
            ->with(['division', 'participant'])
            ->get();
    }

    public function votingConfig(Event $event)
    {
        return $event->votingConfig?->load('votingType.placeConfigs');
    }

    public function modules(Event $event)
    {
        return $event->modules;
    }

    public function users()
    {
        return User::with('role')->paginate(15);
    }

    public function templates()
    {
        return EventTemplate::where('is_active', true)->get();
    }

    public function votingTypes()
    {
        return VotingType::where('is_active', true)->with('placeConfigs')->get();
    }
}
