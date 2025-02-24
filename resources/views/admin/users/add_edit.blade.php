@extends('layouts.admin')

@section('title', $user->exists ? 'Chỉnh sửa người dùng: ' . $user->name : 'Thêm người dùng mới')
@section('header', $user->exists ? 'Chỉnh sửa người dùng: ' . $user->name : 'Thêm người dùng mới')

@section('content')
    <div class="max-w-4xl mx-auto">
        <form
                action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
                method="POST"
                class="space-y-6"
        >
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="space-y-4">
                    <h3 class="text-lg font-medium text-gray-900">Thông tin người dùng</h3>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 required">Họ và tên</label>
                        <input type="text"
                               name="name"
                               id="name"
                               value="{{ old('name', $user->name) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 required">Email</label>
                        <input type="email"
                               name="email"
                               id="email"
                               value="{{ old('email', $user->email) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               required>
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 {{ $user->exists ? '' : 'required' }}">
                            {{ $user->exists ? 'Mật khẩu mới (để trống nếu giữ nguyên)' : 'Mật khẩu' }}
                        </label>
                        <input type="password"
                               name="password"
                               id="password"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                {{ $user->exists ? '' : 'required' }}>
                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 {{ $user->exists ? '' : 'required' }}">
                            {{ $user->exists ? 'Xác nhận mật khẩu mới' : 'Xác nhận mật khẩu' }}
                        </label>
                        <input type="password"
                               name="password_confirmation"
                               id="password_confirmation"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                {{ $user->exists ? '' : 'required' }}>
                    </div>

                    @if($user->exists)
                        <!-- User Info -->
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-500">
                                Tạo ngày: {{ $user->created_at->format('M d, Y H:i') }}
                            </div>
                            <div class="text-sm text-gray-500">
                                Cập nhật lần cuối: {{ $user->updated_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Hủy
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600">
                    {{ $user->exists ? 'Cập nhật người dùng' : 'Tạo người dùng' }}
                </button>
            </div>
        </form>
    </div>
@endsection
