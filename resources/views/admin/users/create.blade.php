@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="text-gray-600 hover:text-gray-800 mb-2 inline-block">
            <i class="fas fa-arrow-left mr-1"></i> Back to Users
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Create New User</h1>
        <p class="text-gray-600">Add a new user to the system</p>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf

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
                               value="{{ old('first_name') }}"
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
                               value="{{ old('last_name') }}"
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
                           value="{{ old('email') }}"
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
                           value="{{ old('organization') }}"
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
                           value="{{ old('phone') }}"
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
                <h2 class="text-lg font-semibold">Password</h2>
            </div>
            <div class="card-body space-y-4">
                <div>
                    <label for="password" class="form-label">Password <span class="text-red-500">*</span></label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input @error('password') border-red-500 @enderror"
                           required>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="form-label">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-input"
                           required>
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
                            required>
                        <option value="">Select Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <label class="flex items-center">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', true) ? 'checked' : '' }}
                               class="form-checkbox">
                        <span class="ml-2">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('admin.users.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save mr-2"></i> Create User
            </button>
        </div>
    </form>
</div>
@endsection
