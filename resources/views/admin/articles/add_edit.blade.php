@extends('layouts.admin')

@section('title', $article->exists ? 'Chỉnh sửa bài viết: ' . $article->title : 'Thêm bài viết mới')
@section('header', $article->exists ? 'Chỉnh sửa bài viết: ' . $article->title : 'Thêm bài viết mới')

@section('content')
    <div class="max-w-6xl mx-auto px-4">
        <form action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-8">
            @csrf
            @if($article->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title Input -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                        <input type="text"
                               name="title"
                               id="title"
                               placeholder="Tiêu đề bài viết..."
                               value="{{ old('title', $article->title) }}"
                               class="w-full px-6 py-4 text-xl border-0 focus:ring-0 placeholder-gray-400"
                               required>
                        @error('title')
                        <p class="px-6 py-2 text-sm text-red-600 bg-red-50">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Content Editor -->
                    <div>
                        <x-head.tinymce-config/>
                        <textarea name="content"
                                  id="tinymce-editor"
                                  rows="20"
                                  placeholder="Viết nội dung bài viết của bạn tại đây..."
                                  class="w-full border-0 focus:ring-0 resize-none"
                        >{{ old('content', $article->content) }}</textarea>
                        @error('content')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publishing Options -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-medium">Xuất bản</h3>
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                {{ $article->exists ? 'Cập nhật' : 'Xuất bản' }}
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">Trạng thái</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox"
                                           name="is_published"
                                           value="1"
                                           class="sr-only peer"
                                            {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>

                            @if($article->exists)
                                <div class="py-4 border-t border-gray-100">
                                    <span class="text-sm text-gray-600">Đã tạo: {{ $article->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-medium mb-4">Danh mục</h3>
                        <div class="space-y-4">
                            <select name="article_category_id"
                                    class="w-full rounded-lg border-gray-300 text-sm p-2">
                                <option value="">Chọn danh mục</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}"
                                            {{ old('article_category_id', $article->article_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('article_category_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Featured Image -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-medium mb-4">Ảnh đại diện</h3>
                        <div class="space-y-4">
                            @if($article->image)
                                <div class="aspect-video rounded-lg bg-gray-100 overflow-hidden">
                                    <img src="{{ Storage::url($article->image) }}"
                                         alt="{{ $article->title }}"
                                         class="w-full h-full object-cover">
                                </div>
                            @endif
                            <div class="relative">
                                <input type="file"
                                       name="image"
                                       id="image"
                                       accept="image/*"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                       onchange="updateImagePreview(this)">
                                <div class="p-4 border-2 border-dashed border-gray-300 rounded-lg text-center hover:border-blue-500 transition-colors">
                                    <div class="space-y-1">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="text-sm text-gray-600">
                                            <span class="font-medium text-blue-600 hover:text-blue-700">Tải ảnh lên</span>
                                            hoặc kéo thả vào đây
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @error('image')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-medium mb-4">SEO</h3>
                        <div class="space-y-4">
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       placeholder="Tiêu đề SEO"
                                       value="{{ old('meta_title', $article->meta_title) }}"
                                       class="w-full rounded-lg border-gray-300 text-sm">
                            </div>

                            <div>
                                <textarea name="meta_description"
                                      rows="3"
                                      placeholder="Mô tả SEO"
                                      class="p-2 w-full rounded-lg border-gray-300 text-sm">{{ old('meta_description', $article->meta_description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <x-slug-preview :nameId="'tin-tuc'" :slugId="'slug-preview'" :nameInput="'title'" :initial-slug="$article->exists ? $article->slug : null" />
        </form>
    </div>

    @push('scripts')
        <script>
			function updateImagePreview(input) {
				if (input.files && input.files[0]) {
					const reader = new FileReader();
					reader.onload = function(e) {
						const preview = document.createElement('div');
						preview.className = 'aspect-video rounded-lg bg-gray-100 overflow-hidden mb-4';
						preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;

						const container = input.closest('.space-y-4');
						const existingPreview = container.querySelector('.aspect-video');
						if (existingPreview) {
							container.removeChild(existingPreview);
						}
						container.insertBefore(preview, container.firstChild);
					}
					reader.readAsDataURL(input.files[0]);
				}
			}
        </script>
    @endpush
@endsection
