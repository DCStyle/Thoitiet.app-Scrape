<div class="mt-6 bg-white rounded-lg shadow-sm p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Xem trước URL</h3>
    <div class="p-4 {{ $getSlugPreviewClass() }} rounded text-sm font-mono text-gray-600">
        @isset($nameId)
            {{ url('/') }}/{{ $nameId }}/<span id="{{ $slugId }}">{{ $getSlug() }}</span>
        @else
            {{ url('/') }}/<span id="{{ $slugId }}">{{ $getSlug() }}</span>
        @endisset
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const nameInput = document.getElementById('{{ $nameInput }}');
        const slugPreview = document.getElementById('slug-preview');

        function convertToSlug(str) {
            const vietnamese = 'àáạảãâầấậẩẫăằắặẳẵèéẹẻẽêềếệểễìíịỉĩòóọỏõôồốộổỗơờớợởỡùúụủũưừứựửữỳýỵỷỹđ';
            const english = 'aaaaaaaaaaaaaaaeeeeeeeeeeeiiiiiooooooooooooooouuuuuuuuuuyyydd';

            return str.toLowerCase()
                .replace(/[^\w\s-]/g, (char) => {
                    const index = vietnamese.indexOf(char);
                    return index !== -1 ? english[index] : char;
                })
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }

        function updateSlugPreview() {
            let slug = convertToSlug(nameInput.value);

            @isset($initialSlug)
            document.getElementById('slug').value = slug;
            @endisset

                slugPreview.textContent = slug || 'duong-dan';
        }

        nameInput.addEventListener('input', updateSlugPreview);
        updateSlugPreview(); // Initial update
    });
</script>
