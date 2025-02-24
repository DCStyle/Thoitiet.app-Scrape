<!-- Meta Settings Tab -->
<div id="tab-content-meta" class="tab-content hidden">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="space-y-6">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Cài đặt Meta cho URL cụ thể</h3>

            <div id="path-meta-fields" class="space-y-4">
                @php
                    $pathTitles = setting('site_path_title') ? json_decode(setting('site_path_title'), true) : [];
                    $pathDescriptions = setting('site_path_description') ? json_decode(setting('site_path_description'), true) : [];
                @endphp

                @foreach($pathTitles as $index => $title)
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
                                       name="site_path_title[{{ $index }}][path]"
                                       value="{{ array_key_first($title) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="/example-path/">
                                <small class="text-gray-500">
                                    Nhập URL cụ thể mà bạn muốn cài đặt Meta.
                                    <br/>Bắt đầu và kết thúc bằng dấu /
                                </small>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text"
                                       name="site_path_title[{{ $index }}][title]"
                                       value="{{ $title[array_key_first($title)] }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Page Title">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea name="site_path_description[{{ $index }}][description]"
                                      class="mt-1 p-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                      rows="2"
                                      placeholder="Page Description">{{ $pathDescriptions[$index][array_key_first($title)] ?? '' }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="button"
                    onclick="addPathMeta()"
                    class="mt-4 px-4 py-2 text-sm font-medium text-blue-500 border border-blue-500 rounded-md hover:bg-blue-50">
                Thêm URL mới
            </button>
        </div>
    </div>
</div>
