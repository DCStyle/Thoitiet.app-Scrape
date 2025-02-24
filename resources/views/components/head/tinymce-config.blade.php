<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.6.0/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        license_key: 'gpl',
        selector: '#tinymce-editor',
        plugins: 'advlist anchor autolink charmap code codesample directionality emoticons fullscreen help image importcss insertdatetime link lists media nonbreaking pagebreak preview quickbars searchreplace table template visualblocks visualchars wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | align lineheight | checklist numlist bullist indent outdent | forecolor backcolor removeformat | link image media table codesample | charmap emoticons | fullscreen code preview',
        toolbar_mode: 'sliding',
        quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote quickimage quicktable',
        contextmenu: 'link image table',
        height: 600,
        skin: 'oxide',
        menubar: false,
        branding: false,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: false,
        document_base_url: '{{ config('app.url') }}',
        images_upload_handler: function(blobInfo, progress) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('image', blobInfo.blob(), blobInfo.filename());

                fetch('/images/upload', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                    .then(res => res.json())
                    .then(json => resolve(json.location))
                    .catch(err => reject('Upload failed'));
            });
        }
    });
</script>
