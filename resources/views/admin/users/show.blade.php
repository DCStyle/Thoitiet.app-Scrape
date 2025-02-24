@extends('layouts.admin')

@section('title', 'Chi tiết người dùng: ' . $user->name)
@section('header', 'Chi tiết người dùng: ' . $user->name)

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- User Information Card -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-gray-900">Thông tin người dùng</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Basic Info -->
                    <div>
                        <div class="text-sm font-medium text-gray-500">Tên</div>
                        <div class="mt-1">{{ $user->name }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-500">Email</div>
                        <div class="mt-1">{{ $user->email }}</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium text-gray-500">Vai trò</div>
                        <div class="mt-1">
                            @foreach($user->roles as $role)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($role->name) }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Timestamps -->
                <div class="pt-4 border-t border-gray-200">
                    <div class="text-sm text-gray-500">
                        Tạo ngày: {{ $user->created_at->format('M d, Y H:i') }}
                    </div>
                    <div class="text-sm text-gray-500">
                        Cập nhật lần cuối: {{ $user->updated_at->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">Hoạt động gần đây</h3>
                    <a href="{{ route('admin.users.activity', ['user' => $user->id]) }}"
                       class="text-sm text-blue-600 hover:text-blue-900">
                        Xem tất cả hoạt động
                    </a>
                </div>

                @if($user->activities->count())
                    <div class="space-y-4">
                        @foreach($user->activities as $activity)
                            <div class="flex items-center justify-between py-2">
                                <div class="flex items-center space-x-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ str_replace('_', ' ', ucfirst($activity->type)) }}
                                </span>
                                    <span class="text-sm text-gray-600">
                                    {{ $activity->description }}
                                </span>
                                </div>
                                <span class="text-sm text-gray-500">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500">Không có hoạt động gần đây</p>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between">
            <div>
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    Quay lại danh sách
                </a>
            </div>

            <div class="flex space-x-4">
                <a href="{{ route('admin.users.edit', $user) }}"
                   class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600">
                    Chỉnh sửa người dùng
                </a>

                @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}"
                          method="POST"
                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md hover:bg-red-600">
                            Xóa người dùng
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
