@extends('layouts.admin')

@section('title', 'Cài đặt trang web')
@section('header', 'Cài đặt')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex" aria-label="Tabs">
                <button type="button"
                        onclick="switchTab('general')"
                        class="tab-btn w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm cursor-pointer"
                        id="tab-general">
                    Cài đặt chung
                </button>
                <button type="button"
                        onclick="switchTab('meta')"
                        class="tab-btn w-1/2 py-4 px-1 text-center border-b-2 font-medium text-sm cursor-pointer"
                        id="tab-meta">
                    Cài đặt Meta URL
                </button>
            </nav>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            @include('admin.settings.tabs.general')
            @include('admin.settings.tabs.meta')

            <!-- Save Button - Outside tabs to be always visible -->
            <div class="flex justify-end sticky bottom-0 bg-gray-50 py-3 px-6 -mx-6">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md shadow-sm hover:bg-blue-600">
                    Lưu cài đặt
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-auto h-24 object-cover rounded-md">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }

        // Tab switching functionality
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected tab content
            document.getElementById(`tab-content-${tabName}`).classList.remove('hidden');

            // Update tab button styles
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('border-blue-500', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            });

            // Style active tab
            const activeTab = document.getElementById(`tab-${tabName}`);
            activeTab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            activeTab.classList.add('border-blue-500', 'text-blue-600');
        }

        // Path meta functions
        function addPathMeta() {
            const container = document.getElementById('path-meta-fields');
            const index = container.children.length;

            const template = `
                <div class="path-meta-group border rounded-lg p-4 relative">
                    <button type="button"
                            class="absolute right-2 top-2 text-red-500 hover:text-red-700"
                            onclick="removePathMeta(this)">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Path</label>
                            <input type="text"
                                   name="site_path_title[${index}][path]"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="/example-path/">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text"
                                   name="site_path_title[${index}][title]"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="Page Title">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="site_path_description[${index}][description]"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  rows="2"
                                  placeholder="Page Description"></textarea>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', template);
        }

        function removePathMeta(button) {
            button.closest('.path-meta-group').remove();
        }

        // Initialize first tab on page load
        document.addEventListener('DOMContentLoaded', function() {
            switchTab('general');
        });
    </script>
@endpush

@push('styles')
    <style>
        .toggle-checkbox:checked {
            border-color: rgb(37 99 235);
        }
        .toggle-checkbox:checked + .toggle-label {
            background-color: rgb(37 99 235);
        }
    </style>
@endpush
