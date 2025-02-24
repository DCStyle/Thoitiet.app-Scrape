<div class="mt-8 p-6 bg-white rounded-lg shadow-sm">
    <h4 class="text-lg font-medium text-gray-800">
        @isset($menu)
            Chỉnh sửa mục menu
        @else
            Thêm mục menu mới
        @endisset
    </h4>
    <form action="{{ isset($menu) ? route('admin.menus.update', $menu) : route('admin.menus.store') }}"
          method="POST" class="mt-4 space-y-4"
    >
        @csrf
        @isset($menu)
            @method('PUT')
        @endisset

        <div>
            <label for="title" class="block text-sm font-medium text-gray-600">Tiêu đề</label>
            <input type="text" name="title" id="title" required
                   value="{{ old('title', $menu->title ?? '') }}"
                   class="w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="icon" class="block text-sm font-medium text-gray-600">Icon (HTML)</label>
            <input type="text" name="icon" id="icon"
                   value="{{ old('icon', $menu->icon ?? '') }}"
                   class="w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="url" class="block text-sm font-medium text-gray-600">URL</label>
            <input type="text" name="url" id="url"
                   value="{{ old('url', $menu->url ?? '') }}"
                   class="w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div>
            <label for="parent_id" class="block text-sm font-medium text-gray-600">Mục cha</label>
            <select name="parent_id" id="parent_id"
                    class="w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Không có</option>
                @foreach ($menus as $item)
                    <option value="{{ $item->id }}"
                            {{ old('parent_id', $menu->parent_id ?? '') == $item->id ? 'selected' : '' }}
                    >
                        {{ $item->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="order" class="block text-sm font-medium text-gray-600">Thứ tự</label>
            <input type="number" name="order" id="order"
                   value="{{ old('order', $menu->order ?? '') }}"
                   class="w-full rounded-md border-gray-300 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <button type="submit"
                class="w-full md:w-auto px-4 py-2 bg-blue-500 text-white font-medium rounded-md shadow-sm hover:bg-blue-600">
            {{ isset($menu) ? 'Cập nhật' : 'Thêm mục menu' }}
        </button>
    </form>
</div>
