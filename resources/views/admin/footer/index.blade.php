@extends('layouts.admin')

@section('title', 'Quản lý Footer')
@section('header', 'Quản lý Footer')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Footer Columns Management -->
        <div class="bg-white shadow rounded-lg p-4 sm:p-6 mb-8">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Quản lý các cột</h2>
            <p class="drag-info mb-4">
                <i class="fas fa-info-circle mr-1"></i>
                Kéo và thả các cột hoặc liên kết để sắp xếp thứ tự
            </p>

            <form action="{{ route('admin.footer.column.store') }}" method="POST" class="mb-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <input type="text"
                               name="title"
                               placeholder="Tên cột"
                               class="border-gray-300 rounded-lg p-2 sm:p-3 w-full focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                    <button type="submit"
                            class="bg-blue-600 text-white rounded-lg px-4 sm:px-6 py-2 sm:py-3 hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300 text-sm sm:text-base">
                        <i class="fas fa-plus mr-2"></i> Thêm cột
                    </button>
                </div>
            </form>

            <div id="column-list" class="space-y-4">
                @foreach ($columns as $column)
                    <div class="column-item border p-3 sm:p-4 rounded-lg draggable-item" data-id="{{ $column->id }}">
                        <div class="flex justify-between items-center mb-4 editable-field">
                            <div class="flex items-center flex-grow">
                                <span class="draggable-handle p-2 mr-2" title="Kéo để di chuyển">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                                <div>
                                    <h3 class="font-semibold text-base sm:text-lg text-gray-700 editable-title" data-id="{{ $column->id }}" data-type="column">
                                        {{ $column->title }}
                                    </h3>
                                    <input type="text"
                                           class="hidden border-gray-300 rounded-lg p-2 w-full focus:border-blue-500 focus:ring focus:ring-blue-200 editable-input"
                                           value="{{ $column->title }}">
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button type="button"
                                        class="edit-btn p-2 text-yellow-500 hover:text-yellow-600"
                                        data-id="{{ $column->id }}"
                                        data-type="column"
                                        data-tooltip-target="edit-tooltip-{{ $column->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <div id="edit-tooltip-{{ $column->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                    Chỉnh sửa
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>

                                <button type="button"
                                        class="delete-btn p-2 text-red-500 hover:text-red-600"
                                        data-id="{{ $column->id }}"
                                        data-type="column"
                                        data-tooltip-target="delete-tooltip-{{ $column->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <div id="delete-tooltip-{{ $column->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                    Xóa
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                        </div>

                        <div class="items-list bg-gray-50 p-3 sm:p-4 rounded-lg">
                            <!-- Add Link Form -->
                            <form action="{{ route('admin.footer.column.item.store', $column) }}" method="POST"
                                  class="flex flex-col sm:flex-row gap-3 sm:gap-4 mb-4">
                                @csrf
                                <input type="text"
                                       name="label"
                                       placeholder="Tên liên kết"
                                       class="border-gray-300 rounded-lg p-2 w-full focus:border-green-500 focus:ring focus:ring-green-200">
                                <input type="text"
                                       name="url"
                                       placeholder="URL"
                                       class="border-gray-300 rounded-lg p-2 w-full focus:border-green-500 focus:ring focus:ring-green-200">
                                <button type="submit"
                                        class="bg-green-600 text-white rounded-lg px-4 py-2 hover:bg-green-700 focus:outline-none focus:ring focus:ring-green-300"
                                >
                                    <i class="fas fa-plus"></i>
                                </button>
                            </form>

                            <div class="space-y-2">
                                @foreach ($column->items as $item)
                                    <div class="item flex justify-between items-center p-2 border rounded-lg bg-white shadow-sm draggable-item editable-field"
                                         data-id="{{ $item->id }}">
                                        <div class="flex items-center flex-grow">
                                            <span class="draggable-handle p-2 mr-2" title="Kéo để di chuyển">
                                                <i class="fas fa-grip-vertical"></i>
                                            </span>
                                            <div class="flex-grow">
                                                <span class="text-gray-600 editable-title" data-id="{{ $item->id }}" data-type="item">
                                                    {{ $item->label }}: {{ $item->url }}
                                                </span>
                                                <input type="text"
                                                       class="hidden border-gray-300 rounded-lg p-2 w-full focus:border-blue-500 focus:ring focus:ring-blue-200 editable-input"
                                                       value="{{ $item->label }}">
                                                <input type="text"
                                                       class="hidden border-gray-300 rounded-lg p-2 w-full focus:border-blue-500 focus:ring focus:ring-blue-200 editable-input"
                                                       value="{{ $item->url }}">
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button type="button"
                                                    class="edit-btn p-2 text-yellow-500 hover:text-yellow-600"
                                                    data-id="{{ $item->id }}"
                                                    data-type="item"
                                                    data-tooltip-target="edit-tooltip-item-{{ $item->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <div id="edit-tooltip-item-{{ $item->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                Chỉnh sửa
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>

                                            <button type="button"
                                                    class="delete-btn p-2 text-red-500 hover:text-red-600"
                                                    data-id="{{ $item->id }}"
                                                    data-type="item"
                                                    data-tooltip-target="delete-tooltip-item-{{ $item->id }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                            <div id="delete-tooltip-item-{{ $item->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                                Xóa
                                                <div class="tooltip-arrow" data-popper-arrow></div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Footer Settings Management -->
        <div class="bg-white shadow rounded-lg p-4 sm:p-6">
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4">Quản lý cài đặt footer</h2>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 sm:p-6">
                <ul class="divide-y divide-gray-200 space-y-2">
                    @foreach ($settings as $setting)
                        <li class="py-3 flex justify-between items-center editable-field">
                            <div class="flex-grow pr-4">
                                <span class="editable-title-preserve hidden">{{ $setting->key }}</span>
                                <span class="font-medium text-gray-800 editable-title" data-id="{{ $setting->id }}" data-type="setting">
                                    {{ $setting->key }}: {{ $setting->value }}
                                </span>
                                <input type="text"
                                       class="hidden border-gray-300 rounded-lg p-2 w-full focus:border-blue-500 focus:ring focus:ring-blue-200 editable-input"
                                       value="{{ $setting->value }}">
                            </div>
                            <div class="flex space-x-2">
                                <button type="button"
                                        class="edit-btn p-2 text-yellow-500 hover:text-yellow-600"
                                        data-id="{{ $setting->id }}"
                                        data-type="setting"
                                        data-tooltip-target="edit-tooltip-setting-{{ $setting->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <div id="edit-tooltip-setting-{{ $setting->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                    Chỉnh sửa
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>

                                <button type="button"
                                        class="delete-btn p-2 text-red-500 hover:text-red-600"
                                        data-id="{{ $setting->id }}"
                                        data-type="setting"
                                        data-tooltip-target="delete-tooltip-setting-{{ $setting->id }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <div id="delete-tooltip-setting-{{ $setting->id }}" role="tooltip" class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                    Xóa
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Drag-and-Drop Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
	    document.addEventListener('DOMContentLoaded', function () {
		    // Drag and drop for footer columns
		    let columnList = document.getElementById('column-list');
		    Sortable.create(columnList, {
			    animation: 150,
			    handle: '.draggable-handle',
			    ghostClass: 'sortable-ghost',
			    dragClass: 'sortable-drag',
			    forceFallback: true, // Forces consistent drag behavior across browsers
			    onStart: function(evt) {
				    document.body.style.cursor = 'grabbing';
			    },
			    onEnd: function(evt) {
				    document.body.style.cursor = 'default';
				    let order = [];
				    document.querySelectorAll('#column-list .column-item').forEach((el) => {
					    order.push(el.dataset.id);
				    });
				    $.post('/admin/footer/column/update-order', {
					    order: order,
					    _token: '{{ csrf_token() }}'
				    });
			    }
		    });

		    // Drag and drop for footer column items - target the items container specifically
		    document.querySelectorAll('.items-list').forEach(function (list) {
			    Sortable.create(list.querySelector('.space-y-2'), { // Target the container of items
				    group: 'shared',
				    animation: 150,
				    handle: '.draggable-handle',
				    ghostClass: 'sortable-ghost',
				    dragClass: 'sortable-drag',
				    forceFallback: true,
				    onStart: function(evt) {
					    document.body.style.cursor = 'grabbing';
					    // Add visual feedback for drop zones
					    document.querySelectorAll('.items-list').forEach(l => {
						    l.classList.add('sortable-drag-active');
					    });
				    },
				    onEnd: function(evt) {
					    document.body.style.cursor = 'default';
					    // Remove visual feedback
					    document.querySelectorAll('.items-list').forEach(l => {
						    l.classList.remove('sortable-drag-active');
					    });

					    if (!evt.to) return; // Safety check

					    let order = [];
					    let itemId = evt.item.dataset.id;
					    let newParentId = evt.to.closest('.column-item').dataset.id;

					    evt.to.querySelectorAll('.item').forEach((el) => {
						    order.push(el.dataset.id);
					    });

					    $.post('/admin/footer/item/update-order', {
						    order: order,
						    _token: '{{ csrf_token() }}'
					    });

					    if (evt.from !== evt.to) {
						    $.post('/admin/footer/item/update-parent', {
							    item_id: itemId,
							    new_parent_id: newParentId,
							    _token: '{{ csrf_token() }}'
						    });
					    }
				    }
			    });
		    });
	    });
    </script>

    <!-- JavaScript for Inline Editing -->
    <script>
	    document.addEventListener('DOMContentLoaded', function () {
		    // Inline editing
		    $('.edit-btn').on('click', function () {
			    const parent = $(this).closest('.editable-field');
			    const title = parent.find('.editable-title');
			    const inputs = parent.find('.editable-input');
			    const preservedTitle = parent.find('.editable-title-preserve');
			    const id = $(this).data('id');
			    const type = $(this).data('type');

			    if (inputs.first().hasClass('hidden')) {
				    // Show inputs for editing
				    title.addClass('hidden');
				    inputs.removeClass('hidden');
				    inputs.first().focus();

				    // Change the edit button text to "Save"
				    $(this).text('Lưu');
			    } else {
				    // Save changes
				    let data = {
					    _token: '{{ csrf_token() }}',
					    id: id,
					    type: type
				    };

				    // Handle different types of editable content
				    switch (type) {
					    case 'column':
						    data.title = inputs.first().val();
						    break;
					    case 'item':
						    data.label = inputs.first().val();
						    data.url = inputs.last().val();
						    break;
					    case 'setting':
						    // For settings, we need to preserve the key and only update the value
						    data.key = preservedTitle.text();
						    data.value = inputs.first().val();
						    break;
				    }

				    $.post('/admin/footer/update', data)
					    .done(() => {
						    // Update the displayed text based on type
						    switch (type) {
							    case 'column':
								    title.text(data.title);
								    break;
							    case 'item':
								    title.text(data.label + ': ' + data.url);
								    break;
							    case 'setting':
								    title.text(data.key + ': ' + data.value);
								    break;
						    }

						    // Hide inputs
						    title.removeClass('hidden');
						    inputs.addClass('hidden');

						    // Change the edit button text back to "Edit"
						    $(this).html(`<i class="fas fa-edit"></i>`);
					    })
					    .fail((error) => {
						    console.error('Error updating:', error);
						    alert('Có lỗi xảy ra khi cập nhật. Vui lòng thử lại.');
					    });
			    }
		    });

		    // Delete functionality
		    $('.delete-btn').on('click', function () {
			    if (confirm('Bạn có chắc chắn muốn xóa?')) {
				    const id = $(this).data('id');
				    const type = $(this).data('type');
				    const elementToRemove = $(this).closest('.editable-field');

				    $.ajax({
					    url: '/admin/footer/delete',
					    type: 'DELETE',
					    data: {
						    _token: '{{ csrf_token() }}',
						    id: id,
						    type: type
					    },
					    success: function () {
						    elementToRemove.fadeOut(300, function() {
							    $(this).remove();
						    });
					    },
					    error: function(error) {
						    console.error('Error deleting:', error);
						    alert('Có lỗi xảy ra khi xóa. Vui lòng thử lại.');
					    }
				    });
			    }
		    });
	    });
    </script>
@endpush

@push('styles')
    <style>
        .draggable-handle {
            cursor: grab;
            color: #9ca3af;
            transition: color 0.2s;
        }

        .draggable-handle:active {
            cursor: grabbing;
        }

        .draggable-item {
            transition: all 0.2s ease;
            transform: translate(0, 0); /* Ensures proper positioning during drag */
        }

        .draggable-item {
            transition: all 0.2s ease;
            transform: translate(0, 0);
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .draggable-handle {
            cursor: grab;
            color: #9ca3af;
            transition: color 0.2s;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Prevent text selection for the entire drag area during dragging */
        .sortable-drag,
        .sortable-ghost,
        .draggable-item * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
        }

        .draggable-item:hover {
            border-color: #60a5fa;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .draggable-item:hover .draggable-handle {
            color: #60a5fa;
        }

        .sortable-ghost {
            opacity: 0.5;
            background: #f3f4f6 !important;
            border: 2px dashed #60a5fa !important;
        }

        .sortable-drag {
            opacity: 0.9;
            background: white !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: absolute !important; /* Forces correct positioning */
            pointer-events: none; /* Prevents interference with drop targets */
        }

        .drag-info {
            font-size: 0.875rem;
            color: #6b7280;
            font-style: italic;
            margin-bottom: 0.5rem;
        }

        /* Highlight drop zones */
        .items-list.sortable-drag-active {
            background-color: #f0f9ff;
            border: 2px dashed #60a5fa;
        }
    </style>
@endpush