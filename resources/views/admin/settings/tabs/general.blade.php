<!-- General Settings Tab -->
<div id="tab-content-general" class="tab-content">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Thông tin trang web</h3>

            <!-- Site Name -->
            <div>
                <label for="site_name" class="block text-sm font-medium text-gray-700">Tên trang web</label>
                <input type="text"
                       name="site_name"
                       id="site_name"
                       value="{{ old('site_name', setting('site_name')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       required>
                @error('site_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Site Description -->
            <div>
                <label for="site_description" class="block text-sm font-medium text-gray-700">Mô tả trang web</label>
                <textarea name="site_description"
                          id="site_description"
                          rows="4"
                          class="p-2 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('site_description', setting('site_description')) }}</textarea>
                @error('site_description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Site Meta Keywords -->
            <div>
                <label for="site_meta_keywords" class="block text-sm font-medium text-gray-700">Từ khóa meta</label>
                <input type="text"
                       name="site_meta_keywords"
                       id="site_meta_keywords"
                       value="{{ old('site_meta_keywords', setting('site_meta_keywords')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('site_meta_keywords')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Site Creator -->
            <div>
                <label for="site_creator" class="block text-sm font-medium text-gray-700">Người tạo trang web</label>
                <input type="text"
                       name="site_creator"
                       id="site_creator"
                       value="{{ old('site_creator', setting('site_creator')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('site_creator')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Site Logo -->
            <div>
                <label for="site_logo" class="block text-sm font-medium text-gray-700">Logo trang web</label>
                <input type="file"
                       name="site_logo"
                       id="site_logo"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       onchange="previewImage(this, 'site_logo_preview')">
                <div id="site_logo_preview" class="mt-2">
                    @if(setting('site_logo'))
                        <img src="{{ Storage::url(setting('site_logo')) }}" alt="Site Logo" class="w-auto h-24 object-cover rounded-md">
                    @endif
                </div>
                @error('site_logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Site Favicon -->
            <div>
                <label for="site_favicon" class="block text-sm font-medium text-gray-700">Favicon</label>
                <input type="file"
                       name="site_favicon"
                       id="site_favicon"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       onchange="previewImage(this, 'site_favicon_preview')">
                <div id="site_favicon_preview" class="mt-2">
                    @if(setting('site_favicon'))
                        <img src="{{ Storage::url(setting('site_favicon')) }}" alt="Site Favicon" class="w-8 h-8 object-cover rounded-md">
                    @endif
                </div>
                @error('site_favicon')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Site OG Image -->
            <div>
                <label for="site_og_image" class="block text-sm font-medium text-gray-700">Ảnh OG</label>
                <input type="file"
                       name="site_og_image"
                       id="site_og_image"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                       onchange="previewImage(this, 'site_og_image_preview')">
                <div id="site_og_image_preview" class="mt-2">
                    @if(setting('site_og_image'))
                        <img src="{{ Storage::url(setting('site_og_image')) }}" alt="Site OG Image" class="w-48 h-48 object-cover rounded-md">
                    @endif
                </div>
                @error('site_og_image')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cache Lifetime -->
            <div class="relative">
                <label for="site_cache_lifetime" class="text-sm font-medium text-gray-700 mb-1 block">
                    Thời gian lưu cache
                </label>
                <div class="relative mt-1">
                    <input type="number"
                           name="cache_lifetime"
                           id="cache_lifetime"
                           value="{{ old('cache_lifetime', setting('cache_lifetime')) }}"
                           class="block w-full pl-4 pr-12 py-2.5 text-gray-700 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200"
                           required
                    >
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <span class="text-gray-500 text-sm">phút</span>
                    </div>
                </div>
                @error('cache_lifetime')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Cache Toggle -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="relative inline-block w-12 align-middle select-none">
                            <input type="checkbox"
                                   name="cache_enabled"
                                   id="cache_enabled"
                                   class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out translate-x-0 checked:translate-x-6"
                                {{ old('cache_enabled', setting('cache_enabled')) ? 'checked' : '' }}
                            >
                            <label for="cache_enabled"
                                   class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer">
                            </label>
                        </div>
                        <label for="site_cache_enabled" class="text-sm font-medium text-gray-700 cursor-pointer">
                            Kích hoạt cache
                        </label>
                    </div>
                    <span class="text-xs text-gray-500">
                            Khuyến nghị bật để cải thiện hiệu suất
                        </span>
                </div>
                @error('cache_enabled')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</div>
