@extends('layouts.admin')

@section('title', 'Quản lý Menu')
@section('header', 'Quản lý Menu')

@section('content')
	<div class="container mx-auto px-4 sm:px-6 lg:px-8">
		<div class="bg-white shadow-md rounded-lg p-6">
			<!-- Header -->
			<div class="flex justify-between items-center mb-6">
				<div>
					<p class="text-sm text-gray-500 mt-1">
						<i class="fas fa-info-circle mr-2"></i>
						Kéo và thả để sắp xếp menu.
					</p>
				</div>
				<button data-modal-target="add-menu-modal"
						data-modal-toggle="add-menu-modal"
						class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
					<i class="fas fa-plus mr-2"></i>
					Thêm mục
				</button>
			</div>

			<!-- Menu List -->
			<div class="space-y-4" id="main-menu-list">
				@forelse ($menus as $menu)
					<div class="menu-item group bg-white border border-gray-200 rounded-lg" data-id="{{ $menu->id }}">
						<!-- Main Menu Item -->
						<div class="p-4 hover:bg-gray-50 transition-all">
							<div class="flex items-center justify-between">
								<div class="flex items-center space-x-4">
									<button class="drag-handle p-2 text-gray-400 hover:text-gray-600">
										<i class="fas fa-grip-vertical"></i>
									</button>
									<div>
										<h3 class="font-medium text-gray-900">
                                            @if($menu->icon)
                                                <span class="me-2">
                                                    {!! $menu->icon !!}
                                                </span>
                                            @endif

                                            {{ $menu->title }}
                                        </h3>
										<p class="text-sm text-gray-500">{{ $menu->url }}</p>
									</div>
								</div>
								<div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
									<a href="{{ route('admin.menus.edit', $menu) }}"
									   class="p-2 text-blue-600 hover:text-blue-700 transition-colors">
										<i class="fas fa-edit"></i>
									</a>
									<form action="{{ route('admin.menus.destroy', $menu) }}"
										  method="POST"
										  class="inline-block"
										  onsubmit="return confirm('Are you sure you want to delete this menu item?')">
										@csrf
										@method('DELETE')
										<button type="submit"
												class="p-2 text-red-600 hover:text-red-700 transition-colors">
											<i class="fas fa-trash"></i>
										</button>
									</form>
								</div>
							</div>
						</div>

						<!-- Submenu Container -->
						<div class="submenu-container">
							@if($menu->children->isNotEmpty())
								<ul class="submenu-list pl-12 pr-4 pb-4 space-y-2" data-parent-id="{{ $menu->id }}">
									@foreach($menu->children as $child)
										<li class="menu-item group bg-white border border-gray-100 rounded-md shadow-sm"
											data-id="{{ $child->id }}">
											<div class="p-3 hover:bg-gray-50 transition-all">
												<div class="flex items-center justify-between">
													<div class="flex items-center space-x-3">
														<button class="drag-handle p-1.5 text-gray-400 hover:text-gray-600">
															<i class="fas fa-grip-vertical"></i>
														</button>
														<div>
															<h4 class="text-sm font-medium text-gray-800">
                                                                @if($child->icon)
                                                                    <span class="me-2">
                                                                        {!! $child->icon !!}
                                                                    </span>
                                                                @endif

                                                                {{ $child->title }}
                                                            </h4>
															<p class="text-xs text-gray-500">{{ $child->url }}</p>
														</div>
													</div>
													<div class="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
														<a href="{{ route('admin.menus.edit', $child) }}"
														   class="p-1.5 text-blue-600 hover:text-blue-700 transition-colors">
															<i class="fas fa-edit"></i>
														</a>
														<form action="{{ route('admin.menus.destroy', $child) }}"
															  method="POST"
															  class="inline-block"
															  onsubmit="return confirm('Are you sure you want to delete this submenu item?')">
															@csrf
															@method('DELETE')
															<button type="submit"
																	class="p-1.5 text-red-600 hover:text-red-700 transition-colors">
																<i class="fas fa-trash"></i>
															</button>
														</form>
													</div>
												</div>
											</div>
										</li>
									@endforeach
								</ul>
							@endif
						</div>
					</div>
				@empty
					<div class="text-center py-12 bg-gray-50 rounded-lg">
						<p class="text-gray-500">Hiện chưa có item nào.</p>
					</div>
				@endforelse
			</div>
		</div>
	</div>

	<!-- Add Menu Modal -->
	<div id="add-menu-modal"
		 tabindex="-1"
		 aria-hidden="true"
		 class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
		<div class="relative w-full max-w-md max-h-full p-4">
			<div class="relative bg-white rounded-lg shadow">
				<div class="flex items-center justify-between p-4 border-b">
					<h3 class="text-xl font-semibold text-gray-900">Thêm menu mới</h3>
					<button type="button"
							class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center"
							data-modal-hide="add-menu-modal">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<div class="p-4">
					@include('admin.menus.partials.form', ['menu' => null])
				</div>
			</div>
		</div>
	</div>
@endsection

@push('scripts')
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const mainList = document.getElementById('main-menu-list');
			let draggedItem = null;

			const sortableOptions = {
				group: 'nested',
				animation: 150,
				fallbackOnBody: true,
				handle: '.drag-handle',
				dragClass: 'menu-drag',
				ghostClass: 'menu-ghost',
				onStart(evt) {
					draggedItem = evt.item;
					document.body.style.cursor = 'grabbing';
				},
				onEnd(evt) {
					document.body.style.cursor = 'default';
					if (evt.to !== evt.from) {
						updateMenuStructure();
					}
				}
			};

			// Initialize main menu sortable
			new Sortable(mainList, {
				...sortableOptions,
				group: 'menu'
			});

			// Initialize submenu sortables
			document.querySelectorAll('.submenu-list').forEach(list => {
				new Sortable(list, {
					...sortableOptions,
					group: 'submenu'
				});
			});

			function updateMenuStructure() {
				const structure = [];

				document.querySelectorAll('#main-menu-list > .menu-item').forEach((item, index) => {
					const menuId = item.dataset.id;
					const submenuList = item.querySelector('.submenu-list');
					const menuItem = {
						id: menuId,
						order: index,
						parent_id: null
					};

					structure.push(menuItem);

					if (submenuList) {
						submenuList.querySelectorAll('.menu-item').forEach((subitem, subindex) => {
							structure.push({
								id: subitem.dataset.id,
								order: subindex,
								parent_id: menuId
							});
						});
					}
				});

				fetch('{{ route('admin.menus.reorder') }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
					},
					body: JSON.stringify({ items: structure })
				})
						.then(response => response.json())
						.then(data => {
							if (data.success) {
								showNotification('Menu structure updated successfully', 'success');
							}
						})
						.catch(error => {
							console.error('Error updating menu structure:', error);
							showNotification('Failed to update menu structure', 'error');
						});
			}

			function showNotification(message, type = 'success') {
				const notification = document.createElement('div');
				notification.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white transition-all transform translate-y-0 ${
						type === 'success' ? 'bg-green-500' : 'bg-red-500'
				}`;
				notification.textContent = message;

				document.body.appendChild(notification);

				setTimeout(() => {
					notification.style.transform = 'translateY(200%)';
					setTimeout(() => notification.remove(), 300);
				}, 3000);
			}
		});
	</script>
@endpush

@push('styles')
	<style>
		.menu-drag {
			opacity: 0.9;
			background: white !important;
			box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
		}

		.menu-ghost {
			opacity: 0.4;
			background: #f3f4f6 !important;
			border: 2px dashed #60a5fa !important;
		}

		.drag-handle {
			cursor: grab;
		}

		.drag-handle:active {
			cursor: grabbing;
		}

		.menu-item {
			transition: all 0.2s ease;
		}

		.submenu-list {
			transition: padding 0.3s ease;
		}

		.submenu-list:empty {
			padding: 0;
		}
	</style>
@endpush
