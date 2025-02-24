@extends('layouts.admin')

@section('title', $category->exists ? 'Chỉnh sửa danh mục: ' . $category->name : 'Thêm danh mục mới')
@section('header', $category->exists ? 'Chỉnh sửa danh mục: ' . $category->name : 'Thêm danh mục mới')

@section('content')
    <div class="max-w-6xl mx-auto px-4">
        <form action="{{ $category->exists ? route('admin.article-categories.update', $category) : route('admin.article-categories.store') }}"
              method="POST"
              enctype="multipart/form-data"
              class="space-y-8">
            @csrf
            @if($category->exists) @method('PUT') @endif

            <div class="space-y-6">
                <!-- Name Input -->
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <input type="text"
                           name="name"
                           id="name"
                           placeholder="Tiêu đề danh mục..."
                           value="{{ old('name', $category->name) }}"
                           class="w-full px-6 py-4 text-xl border-0 focus:ring-0 placeholder-gray-400"
                           required>
                    @error('name')
                    <p class="px-6 py-2 text-sm text-red-600 bg-red-50">{{ $message }}</p>
                    @enderror
                </div>

                <!-- SEO Settings -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-medium mb-4">SEO</h3>
                    <div class="space-y-4">
                        <div>
                            <input type="text"
                                   name="meta_title"
                                   placeholder="Tiêu đề SEO"
                                   value="{{ old('meta_title', $category->meta_title) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm">
                        </div>

                        <div>
                                <textarea name="meta_description"
                                          rows="3"
                                          placeholder="Mô tả SEO"
                                          class="p-2 w-full rounded-lg border-gray-300 text-sm">{{ old('meta_description', $category->meta_description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Save section -->
                <div class="bg-white rounded-xl shadow-sm p-6 flex space-x-4">
                    <button type="submit"
                            class="px-6 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                        {{ $category->exists ? 'Cập nhật' : 'Thêm mới' }}
                    </button>
                </div>
            </div>

            <x-slug-preview :nameId="'danh-muc'" :slugId="'slug-preview'" :nameInput="'name'" :initial-slug="$category->exists ? $category->slug : null" />
        </form>
    </div>
@endsection
