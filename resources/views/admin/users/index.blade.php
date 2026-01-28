@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-users"></i> User Management</h1>
        </div>
        <button type="button" class="btn btn-warning" onclick="openUserModal()">
            <i class="fas fa-plus"></i> New User
        </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="filter-form">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search by name or email..."
                   class="form-control search-input">
            <select name="role" class="form-control" onchange="this.form.submit()">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>

    <!-- Users Table -->
    <div class="card">
        <table class="table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="user-info-cell">
                                <div class="user-avatar">
                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $user->name }}</strong>
                                    @if($user->organization)
                                        <br><small style="color: #6b7280;">{{ $user->organization }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->role->name === 'Administrator' ? 'badge-danger' : ($user->role->name === 'Member' ? 'badge-info' : 'badge-warning') }}">
                                {{ $user->role->name }}
                            </span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('M j, Y') }}</td>
                        <td>
                            <div class="action-buttons">
                                <button type="button"
                                   class="action-btn action-btn-edit"
                                   title="Edit"
                                   onclick="openUserModal({{ $user->id }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user) }}"
                                          method="POST"
                                          style="display: inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="action-btn action-btn-delete"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 40px; color: #6b7280;">
                            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
                            <p>No users found</p>
                            <button type="button" class="btn btn-primary mt-2" onclick="openUserModal()">
                                <i class="fas fa-plus"></i> Add a user
                            </button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div style="padding: 15px; border-top: 1px solid #e5e7eb;">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- User Modal -->
<div class="modal-overlay" id="userModal">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="userModalTitle"><i class="fas fa-user-plus"></i> Create New User</h2>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm" onsubmit="submitUserForm(event)">
            <div class="modal-body">
                <div class="modal-error" id="userModalError"></div>
                <input type="hidden" id="userEditId" value="">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">First Name <span style="color:#dc2626">*</span></label>
                        <input type="text" id="userFirstName" name="first_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name <span style="color:#dc2626">*</span></label>
                        <input type="text" id="userLastName" name="last_name" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Email Address <span style="color:#dc2626">*</span></label>
                    <input type="email" id="userEmail" name="email" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Organization</label>
                        <input type="text" id="userOrganization" name="organization" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone</label>
                        <input type="tel" id="userPhone" name="phone" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" id="passwordLabel">Password <span style="color:#dc2626">*</span></label>
                        <input type="password" id="userPassword" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" id="passwordConfirmLabel">Confirm Password <span style="color:#dc2626">*</span></label>
                        <input type="password" id="userPasswordConfirm" name="password_confirmation" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role <span style="color:#dc2626">*</span></label>
                        <select id="userRole" name="role_id" class="form-control" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:4px;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" id="userActive" name="is_active" value="1" checked style="width:18px;height:18px;">
                            <span>Active</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="userSubmitBtn">
                    <i class="fas fa-save"></i> Create User
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openUserModal(userId = null) {
    const modal = document.getElementById('userModal');
    const form = document.getElementById('userForm');
    const title = document.getElementById('userModalTitle');
    const submitBtn = document.getElementById('userSubmitBtn');
    const editId = document.getElementById('userEditId');
    const passwordLabel = document.getElementById('passwordLabel');
    const passwordConfirmLabel = document.getElementById('passwordConfirmLabel');
    const errorDiv = document.getElementById('userModalError');

    form.reset();
    errorDiv.classList.remove('active');
    document.getElementById('userActive').checked = true;

    if (userId) {
        title.innerHTML = '<i class="fas fa-user-edit"></i> Edit User';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Update User';
        editId.value = userId;
        passwordLabel.innerHTML = 'New Password';
        passwordConfirmLabel.innerHTML = 'Confirm New Password';
        document.getElementById('userPassword').removeAttribute('required');
        document.getElementById('userPasswordConfirm').removeAttribute('required');

        fetch(`/admin/users/${userId}/edit`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(user => {
            document.getElementById('userFirstName').value = user.first_name || '';
            document.getElementById('userLastName').value = user.last_name || '';
            document.getElementById('userEmail').value = user.email || '';
            document.getElementById('userOrganization').value = user.organization || '';
            document.getElementById('userPhone').value = user.phone || '';
            document.getElementById('userRole').value = user.role_id || '';
            document.getElementById('userActive').checked = user.is_active;
        });
    } else {
        title.innerHTML = '<i class="fas fa-user-plus"></i> Create New User';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Create User';
        editId.value = '';
        passwordLabel.innerHTML = 'Password <span style="color:#dc2626">*</span>';
        passwordConfirmLabel.innerHTML = 'Confirm Password <span style="color:#dc2626">*</span>';
        document.getElementById('userPassword').setAttribute('required', '');
        document.getElementById('userPasswordConfirm').setAttribute('required', '');
    }

    modal.classList.add('active');
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
}

function submitUserForm(e) {
    e.preventDefault();
    const editId = document.getElementById('userEditId').value;
    const form = document.getElementById('userForm');
    const formData = new FormData(form);
    const errorDiv = document.getElementById('userModalError');

    if (!document.getElementById('userActive').checked) {
        formData.delete('is_active');
    }

    const url = editId ? `/admin/users/${editId}` : '/admin/users';
    const method = editId ? 'PUT' : 'POST';

    const data = {};
    formData.forEach((v, k) => data[k] = v);
    if (editId) data['_method'] = 'PUT';

    fetch(url, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(r => r.json().then(d => ({ok: r.ok, data: d})))
    .then(({ok, data}) => {
        if (ok && data.success) {
            closeUserModal();
            location.reload();
        } else {
            const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : (data.message || 'An error occurred.');
            errorDiv.innerHTML = errors;
            errorDiv.classList.add('active');
        }
    })
    .catch(() => {
        errorDiv.innerHTML = 'An error occurred. Please try again.';
        errorDiv.classList.add('active');
    });
}

document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) closeUserModal();
});
</script>
@endpush
@endsection
