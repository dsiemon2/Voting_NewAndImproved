<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class DashboardController extends Controller
{
    public function __construct(
        private EventRepositoryInterface $eventRepository,
        private UserRepositoryInterface $userRepository,
    ) {}

    public function index()
    {
        $activeEvents = $this->eventRepository->getActiveEvents();
        $upcomingEvents = $this->eventRepository->getUpcomingEvents(5);
        $totalUsers = $this->userRepository->count();

        return view('admin.dashboard', [
            'activeEvents' => $activeEvents,
            'upcomingEvents' => $upcomingEvents,
            'totalUsers' => $totalUsers,
            'user' => auth()->user(),
        ]);
    }
}
