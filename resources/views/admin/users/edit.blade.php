@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-800 mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to Users
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Edit User</h1>
        <p class="text-gray-600">{{ $user->name }}</p>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- User Information -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">User Information</h2>
            </div>
            <div class="card-body space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="form-label">First Name <span class="text-red-500">*</span></label>
                        <input type="text"
                               id="first_name"
                               name="first_name"
                               value="{{ old('first_name', $user->first_name) }}"
                               class="form-input @error('first_name') border-red-500 @enderror"
                               required>
                        @error('first_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="form-label">Last Name <span class="text-red-500">*</span></label>
                        <input type="text"
                               id="last_name"
                               name="last_name"
                               value="{{ old('last_name', $user->last_name) }}"
                               class="form-input @error('last_name') border-red-500 @enderror"
                               required>
                        @error('last_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="email" class="form-label">Email Address <span class="text-red-500">*</span></label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', $user->email) }}"
                           class="form-input @error('email') border-red-500 @enderror"
                           required>
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="organization" class="form-label">Organization</label>
                    <input type="text"
                           id="organization"
                           name="organization"
                           value="{{ old('organization', $user->organization) }}"
                           class="form-input @error('organization') border-red-500 @enderror">
                    @error('organization')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           value="{{ old('phone', $user->phone) }}"
                           class="form-input @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Password -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Change Password</h2>
                <p class="text-sm text-gray-500">Leave blank to keep current password</p>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label for="password" class="form-label">New Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-input">
                </div>
            </div>
        </div>

        <!-- Role & Status -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Role & Status</h2>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label for="role_id" class="form-label">Role <span class="text-red-500">*</span></label>
                    <select id="role_id"
                            name="role_id"
                            class="form-select @error('role_id') border-red-500 @enderror"
                            required
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="role_id" value="{{ $user->role_id }}">
                        <p class="text-sm text-gray-500 mt-1">You cannot change your own role</p>
                    @endif
                    @error('role_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                               class="form-checkbox"
                               {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <span class="ml-2">Active</span>
                    </label>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="is_active" value="{{ $user->is_active ? '1' : '0' }}">
                        <span class="text-sm text-gray-500 ml-2">(You cannot deactivate yourself)</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Account Info -->
        <div class="card">
            <div class="card-header">
                <h2 class="text-lg font-semibold">Account Information</h2>
            </div>
            <div class="card-body">
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Created</dt>
                        <dd class="font-medium">{{ $user->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Last Updated</dt>
                        <dd class="font-medium">{{ $user->updated_at->format('M j, Y g:i A') }}</dd>
                    </div>
                    @if($user->email_verified_at)
                        <div>
                            <dt class="text-gray-500">Email Verified</dt>
                            <dd class="font-medium">{{ $user->email_verified_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-between">
            @if($user->id !== auth()->id())
                <button type="button"
                        onclick="if(confirm('Are you sure you want to delete this user?')) { document.getElementById('delete-form').submit(); }"
                        class="btn-danger">
                    <i class="fas fa-trash mr-2"></i> Delete User
                </button>
            @else
                <div></div>
            @endif

            <div class="flex space-x-4">
                <a href="{{ route('admin.users.index') }}" class="btn-outline">Cancel</a>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i> Update User
                </button>
            </div>
        </div>
    </form>

    @if($user->id !== auth()->id())
        <form id="delete-form"
              action="{{ route('admin.users.destroy', $user) }}"
              method="POST"
              class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
</div>
@endsection
