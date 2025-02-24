@extends('layouts.admin')

@section('title', 'Bảng điều khiển')
@section('header', 'Bảng điều khiển')

@section('content')
    <div class="p-6">
        {{-- Main Features --}}
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Tính Năng Chính</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Settings Card --}}
            <div class="block p-6 rounded-lg shadow-lg bg-indigo-600">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-cogs fa-2xl text-white"></i>
                    <button id="settingsDropdownButton" data-dropdown-toggle="settingsDropdown" class="text-white focus:ring-4 focus:outline-none focus:ring-indigo-300 rounded-lg p-1.5 text-center inline-flex items-center" type="button">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01"/>
                        </svg>
                    </button>
                    <div id="settingsDropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="settingsDropdownButton">
                            <li>
                                <a href="{{ route('admin.settings') }}" class="block px-4 py-2 hover:bg-gray-100">Xem Cài Đặt</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings') }}#cache" class="block px-4 py-2 hover:bg-gray-100">Cấu Hình Cache</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings') }}#security" class="block px-4 py-2 hover:bg-gray-100">Bảo Mật</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Cài Đặt</h3>
                <p class="text-white/80 text-sm">Quản lý cấu hình và thiết lập hệ thống</p>
            </div>

            {{-- Users Card --}}
            <div class="block p-6 rounded-lg shadow-lg bg-blue-600">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-users fa-2xl text-white"></i>
                    <button id="usersDropdownButton" data-dropdown-toggle="usersDropdown" class="text-white focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg p-1.5 text-center inline-flex items-center" type="button">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01"/>
                        </svg>
                    </button>
                    <div id="usersDropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="usersDropdownButton">
                            <li>
                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 hover:bg-gray-100">Danh Sách</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.create') }}" class="block px-4 py-2 hover:bg-gray-100">Thêm Mới</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.index') }}?filter=inactive" class="block px-4 py-2 hover:bg-gray-100">Tài Khoản Khóa</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Người Dùng</h3>
                <p class="text-white/80 text-sm">Quản lý tài khoản và phân quyền</p>
            </div>

            {{-- Menu Card --}}
            <div class="block p-6 rounded-lg shadow-lg bg-emerald-600">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-bars fa-2xl text-white"></i>
                    <button id="menuDropdownButton" data-dropdown-toggle="menuDropdown" class="text-white focus:ring-4 focus:outline-none focus:ring-emerald-300 rounded-lg p-1.5 text-center inline-flex items-center" type="button">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01"/>
                        </svg>
                    </button>
                    <div id="menuDropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="menuDropdownButton">
                            <li>
                                <a href="{{ route('admin.menus.index') }}" class="block px-4 py-2 hover:bg-gray-100">Xem Menu</a>
                            </li>
                            <li>
                                <a href="{{ route('admin.menus.create') }}" class="block px-4 py-2 hover:bg-gray-100">Thêm Menu</a>
                            </li>
                            <li>
                                <form id="reorder-form" action="{{ route('admin.menus.reorder') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Sắp Xếp Lại</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Menu</h3>
                <p class="text-white/80 text-sm">Tùy chỉnh menu và cấu trúc điều hướng</p>
            </div>

            {{-- Footer Card --}}
            <div class="block p-6 rounded-lg shadow-lg bg-purple-600">
                <div class="flex items-center justify-between mb-4">
                    <i class="fas fa-th-large fa-2xl text-white"></i>
                    <button id="footerDropdownButton" data-dropdown-toggle="footerDropdown" class="text-white focus:ring-4 focus:outline-none focus:ring-purple-300 rounded-lg p-1.5 text-center inline-flex items-center" type="button">
                        <svg class="w-6 h-6" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M12 6h.01M12 12h.01M12 18h.01"/>
                        </svg>
                    </button>
                    <div id="footerDropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="footerDropdownButton">
                            <li>
                                <a href="{{ route('admin.footer.index') }}" class="block px-4 py-2 hover:bg-gray-100">Xem Footer</a>
                            </li>
                            <li>
                                <form id="footer-column-form" action="{{ route('admin.footer.column.store') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Thêm Cột Mới</button>
                                </form>
                            </li>
                            <li>
                                <form id="footer-settings-form" action="{{ route('admin.footer.setting.update') }}" method="POST" class="block">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 hover:bg-gray-100">Cài Đặt Footer</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">Footer</h3>
                <p class="text-white/80 text-sm">Quản lý cột và liên kết chân trang</p>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Initialize all dropdowns
            const dropdownButtons = document.querySelectorAll('[data-dropdown-toggle]');
            dropdownButtons.forEach(button => {
                const targetId = button.getAttribute('data-dropdown-toggle');
                const targetDropdown = document.getElementById(targetId);

                if (button && targetDropdown) {
                    const dropdown = new Dropdown(targetDropdown, button);
                }
            });
        </script>
    @endpush
@endsection
