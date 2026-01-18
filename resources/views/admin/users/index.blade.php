@extends('layouts.app')

@section('content')
<div>
    <!-- Header -->
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1 class="page-title"><i class="fas fa-users"></i> User Management</h1>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-warning">
            <i class="fas fa-plus"></i> New User
        </a>
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
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="action-btn action-btn-edit"
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
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
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Add a user
                            </a>
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
@endsection
