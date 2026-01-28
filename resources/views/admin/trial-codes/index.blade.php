@extends('layouts.app')

@section('content')
<div class="page-title d-flex justify-between align-center">
    <span><i class="fas fa-ticket-alt"></i> Trial Codes</span>
    <button class="btn btn-primary" onclick="document.getElementById('createModal').showModal()">
        <i class="fas fa-plus"></i> Create Trial Code
    </button>
</div>

<!-- Stats Cards -->
<div class="grid grid-3" style="margin-bottom: 20px;">
    <div class="card" style="background: linear-gradient(135deg, #2eaa5e 0%, #0a6632 100%); color: white;">
        <div class="d-flex justify-between align-center">
            <div>
                <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Total Codes</div>
                <div style="font-size: 28px; font-weight: bold;">{{ $stats['total'] }}</div>
            </div>
            <div style="font-size: 36px; opacity: 0.3;"><i class="fas fa-ticket-alt"></i></div>
        </div>
    </div>
    <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
        <div class="d-flex justify-between align-center">
            <div>
                <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Redeemed</div>
                <div style="font-size: 28px; font-weight: bold;">{{ $stats['redeemed'] }}</div>
            </div>
            <div style="font-size: 36px; opacity: 0.3;"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white;">
        <div class="d-flex justify-between align-center">
            <div>
                <div style="font-size: 12px; opacity: 0.8; text-transform: uppercase;">Pending</div>
                <div style="font-size: 28px; font-weight: bold;">{{ $stats['pending'] + $stats['sent'] }}</div>
            </div>
            <div style="font-size: 36px; opacity: 0.3;"><i class="fas fa-clock"></i></div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom: 20px;">
    <form method="GET" action="{{ route('admin.trial-codes.index') }}" class="d-flex gap-4 align-center">
        <div class="form-group" style="margin-bottom: 0; flex: 1;">
            <input type="text" name="search" class="form-control" placeholder="Search by code, email, name..."
                   value="{{ $filters['search'] ?? '' }}">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <select name="status" class="form-control">
                <option value="">All Statuses</option>
                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="sent" {{ ($filters['status'] ?? '') === 'sent' ? 'selected' : '' }}>Sent</option>
                <option value="redeemed" {{ ($filters['status'] ?? '') === 'redeemed' ? 'selected' : '' }}>Redeemed</option>
                <option value="expired" {{ ($filters['status'] ?? '') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="revoked" {{ ($filters['status'] ?? '') === 'revoked' ? 'selected' : '' }}>Revoked</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i> Filter
        </button>
        <a href="{{ route('admin.trial-codes.index') }}" class="btn btn-secondary">
            <i class="fas fa-times"></i> Clear
        </a>
    </form>
</div>

<!-- Trial Codes Table -->
<div class="card">
    <div class="card-header d-flex justify-between align-center">
        <span><i class="fas fa-list"></i> Trial Codes</span>
        <form method="POST" action="{{ route('admin.trial-codes.expire-old') }}" style="margin: 0;">
            @csrf
            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('Mark all expired codes?')">
                <i class="fas fa-calendar-times"></i> Expire Old Codes
            </button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Requester</th>
                    <th>Linked User</th>
                    <th>Status</th>
                    <th>Expires</th>
                    <th>Ext.</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($trialCodes as $code)
                <tr>
                    <td>
                        <code style="font-size: 14px; font-weight: bold; color: #0d6e38;">{{ $code->code }}</code>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-{{ $code->delivery_method === 'sms' ? 'sms' : 'envelope' }}"></i>
                            {{ ucfirst($code->delivery_method) }}
                        </small>
                    </td>
                    <td>
                        <strong>{{ $code->requester_full_name }}</strong>
                        <br>
                        <a href="mailto:{{ $code->requester_email }}" style="font-size: 12px;">{{ $code->requester_email }}</a>
                        @if($code->requester_phone)
                        <br><small class="text-muted">{{ $code->requester_phone }}</small>
                        @endif
                        @if($code->requester_organization)
                        <br><small class="text-muted"><i class="fas fa-building"></i> {{ $code->requester_organization }}</small>
                        @endif
                    </td>
                    <td>
                        @if($code->user)
                            <div class="user-info-cell">
                                <div class="user-avatar" style="width: 32px; height: 32px; font-size: 12px;">
                                    {{ strtoupper(substr($code->user->first_name, 0, 1) . substr($code->user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('admin.users.edit', $code->user) }}" style="font-weight: 500;">
                                        {{ $code->user->first_name }} {{ $code->user->last_name }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $code->user->email }}</small>
                                    <br>
                                    <small class="text-muted">User #{{ $code->user->id }}</small>
                                </div>
                            </div>
                        @else
                            <span class="text-muted"><i class="fas fa-user-slash"></i> Not registered</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $code->status_color }}">
                            {{ ucfirst($code->status) }}
                        </span>
                        @if($code->redeemed_at)
                        <br><small class="text-muted">Redeemed: {{ $code->redeemed_at->format('M d') }}</small>
                        @endif
                    </td>
                    <td>
                        @if($code->isExpired())
                        <span class="text-danger"><i class="fas fa-times-circle"></i> Expired</span>
                        <br><small class="text-muted">{{ $code->expires_at->format('M d, Y') }}</small>
                        @else
                        {{ $code->expires_at->format('M d, Y') }}
                        <br><small class="text-muted">{{ $code->days_remaining }} days left</small>
                        @endif
                    </td>
                    <td>
                        {{ $code->extension_count }}/{{ \App\Models\TrialCode::MAX_EXTENSIONS }}
                        @if($code->extendedByAdmin)
                        <br><small class="text-muted">by {{ $code->extendedByAdmin->first_name }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.trial-codes.show', $code) }}" class="btn btn-sm btn-secondary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($code->canBeExtended())
                            <form method="POST" action="{{ route('admin.trial-codes.extend', $code) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary" title="Extend Trial"
                                        onclick="return confirm('Extend this trial by 14 days?')">
                                    <i class="fas fa-calendar-plus"></i>
                                </button>
                            </form>
                            @endif
                            @if(in_array($code->status, ['pending', 'sent']))
                            <form method="POST" action="{{ route('admin.trial-codes.resend', $code) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info" title="Resend">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                            @endif
                            @if($code->canBeRevoked())
                            <form method="POST" action="{{ route('admin.trial-codes.revoke', $code) }}" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-danger" title="Revoke"
                                        onclick="return confirm('Revoke this trial code?')">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 40px;">
                        <i class="fas fa-ticket-alt" style="font-size: 48px; opacity: 0.3;"></i>
                        <p style="margin-top: 10px;">No trial codes found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($trialCodes->hasPages())
    <div style="padding: 15px;">
        {{ $trialCodes->withQueryString()->links() }}
    </div>
    @endif
</div>

<!-- Create Trial Code Modal -->
<dialog id="createModal" style="padding: 0; border: none; border-radius: 8px; max-width: 500px; width: 90%;">
    <div class="card" style="margin: 0;">
        <div class="card-header d-flex justify-between align-center">
            <span><i class="fas fa-plus"></i> Create Trial Code</span>
            <button type="button" onclick="document.getElementById('createModal').close()" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.trial-codes.store') }}">
            @csrf
            <div style="padding: 20px;">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+1234567890">
                </div>
                <div class="form-group">
                    <label class="form-label">Organization</label>
                    <input type="text" name="organization" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Delivery Method *</label>
                    <select name="delivery_method" class="form-control" required>
                        <option value="email">Email</option>
                        <option value="sms">SMS</option>
                    </select>
                </div>
            </div>
            <div style="padding: 15px 20px; background: #f9fafb; border-top: 1px solid #e5e7eb; display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('createModal').close()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Create & Send
                </button>
            </div>
        </form>
    </div>
</dialog>

<style>
.grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .grid-3 {
        grid-template-columns: 1fr;
    }
}

dialog::backdrop {
    background: rgba(0, 0, 0, 0.5);
}

.text-muted {
    color: #6b7280;
}

.text-danger {
    color: #ef4444;
}

.btn-info {
    background: #06b6d4;
    color: white;
}

.btn-info:hover {
    background: #0891b2;
}
</style>
@endsection
