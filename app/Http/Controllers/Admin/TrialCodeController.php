<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrialCode;
use App\Services\TrialCodeService;
use Illuminate\Http\Request;

class TrialCodeController extends Controller
{
    protected TrialCodeService $trialCodeService;

    public function __construct(TrialCodeService $trialCodeService)
    {
        $this->trialCodeService = $trialCodeService;
    }

    /**
     * Display trial codes listing
     */
    public function index(Request $request)
    {
        $query = TrialCode::with(['user', 'extendedByAdmin', 'parentCode']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by search (email, name, code)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('requester_email', 'like', "%{$search}%")
                    ->orWhere('requester_first_name', 'like', "%{$search}%")
                    ->orWhere('requester_last_name', 'like', "%{$search}%")
                    ->orWhere('requester_organization', 'like', "%{$search}%");
            });
        }

        $trialCodes = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get stats
        $stats = [
            'total' => TrialCode::count(),
            'pending' => TrialCode::where('status', TrialCode::STATUS_PENDING)->count(),
            'sent' => TrialCode::where('status', TrialCode::STATUS_SENT)->count(),
            'redeemed' => TrialCode::where('status', TrialCode::STATUS_REDEEMED)->count(),
            'expired' => TrialCode::where('status', TrialCode::STATUS_EXPIRED)->count(),
            'revoked' => TrialCode::where('status', TrialCode::STATUS_REVOKED)->count(),
        ];

        return view('admin.trial-codes.index', [
            'trialCodes' => $trialCodes,
            'stats' => $stats,
            'filters' => [
                'status' => $request->status,
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Show single trial code details
     */
    public function show(TrialCode $trialCode)
    {
        $trialCode->load(['user', 'extendedByAdmin', 'parentCode', 'extensionCodes']);

        return view('admin.trial-codes.show', [
            'trialCode' => $trialCode,
        ]);
    }

    /**
     * Extend a trial code
     */
    public function extend(TrialCode $trialCode)
    {
        $result = $this->trialCodeService->extendTrial($trialCode, auth()->user());

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Trial has been extended. New expiration: ' . $result['new_expires_at']->format('M d, Y'));
    }

    /**
     * Revoke a trial code
     */
    public function revoke(Request $request, TrialCode $trialCode)
    {
        $result = $this->trialCodeService->revokeCode(
            $trialCode,
            auth()->user(),
            $request->reason
        );

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Trial code has been revoked.');
    }

    /**
     * Resend a trial code
     */
    public function resend(TrialCode $trialCode)
    {
        $result = $this->trialCodeService->resendCode($trialCode);

        if (!$result['success']) {
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Trial code has been resent to ' . $trialCode->requester_email);
    }

    /**
     * Manually create a trial code (admin)
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'delivery_method' => 'required|in:email,sms',
        ]);

        $result = $this->trialCodeService->requestTrialCode([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'organization' => $request->organization,
            'delivery_method' => $request->delivery_method,
        ]);

        if (!$result['success']) {
            return back()->with('error', $result['error'])->withInput();
        }

        return redirect()->route('admin.trial-codes.index')
            ->with('success', 'Trial code created and sent to ' . $request->email);
    }

    /**
     * Bulk expire old codes
     */
    public function expireOld()
    {
        $count = $this->trialCodeService->expireOldCodes();

        return back()->with('success', "{$count} expired trial codes have been updated.");
    }
}
