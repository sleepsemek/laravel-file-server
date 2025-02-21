@extends('layouts.app')

@section('content')
    <div id="notification-container" class="notification-container"></div>

    <h4>Новый файл</h4>
    <form action="{{ route('file.store') }}" method="POST" enctype="multipart/form-data" class="form-inline" id="uploadForm">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <div class="input-group my-3">
            <input type="file" name="files[]" class="form-control" id="file" multiple required>
            <button type="submit" class="btn btn-primary" id="submitButton"><i class="fa fa-upload"></i></button>
        </div>
    </form>
    <div id="progressContainer" class="progress mb-3" style="height: 25px; display: none;">
        <div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%; height: 100%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
    </div>

    <h4>Список файлов</h4>
    <div class="mt-3 mb-3">
        <form action="{{ route('file.index') }}" method="GET">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Поиск по названию" value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                @if(request('search'))
                    <a href="{{ route('file.index') }}" class="btn btn-danger"><i class="fa fa-times"></i></a>
                @endif
            </div>

            <div class="form-group mt-3">
                <div class="row align-items-center">
                    <div class="col">
                        <select name="sort" class="form-control" onchange="this.form.submit()">
                            <option value="name" {{ !request('sort') ? 'selected' : '' }}>По умолчанию</option>
                            <option value="filesize_DESC" {{ request('sort') == 'filesize_DESC' ? 'selected' : '' }}>Сначала большие</option>
                            <option value="filesize_ASC" {{ request('sort') == 'filesize_ASC' ? 'selected' : '' }}>Сначала маленькие</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <div class="form-check">
                            <input type="hidden" name="show_videos" value="0">
                            <input type="checkbox" name="show_videos" class="form-check-input" id="showVideosCheckbox" value="1"  
                                            {{ request('show_videos') ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label" for="showVideosCheckbox">Видео</label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-3">
        @foreach($files as $file)
        <div class="col">
            <div class="card h-100">
                <div class="h-100" onclick="showModal('{{ $file->type }}', '{{ route('file.view', $file->name) }}', '{{ $file->extension }}');">
                    <img src="{{ $file->thumbnail }}" alt="Превью" class="card-img-top">
                    <div class="card-body">
                        <h6 class="card-title m-0"> {{ $file->name }} </h6>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <form action="{{ route('file.destroy', ['filename' => $file->name]) }}" method="POST" id="deleteForm-{{$file->name}}">
                        @csrf
                        @method('DELETE')
                        <div class="btn-group" role="group" aria-label="File actions">
                            <button type="button" class="btn btn-primary download-button" onclick="location.href='{{ route('file.download', ['filename' => $file->name]) }}'">
                                <i class="fa fa-download"></i>
                            </button>
                            <button type="button" class="btn btn-success share-button" data-link="{{ url('/file/shared/' . $file->share_link) }}">
                                <i class="fa fa-share"></i>
                            </button>
                            <button type="button" class="btn btn-danger delete-button" data-form-id="deleteForm-{{ $file->name }}" data-toggle="modal" data-target="#confirmDeleteModal">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer d-flex py-3">
                    <small class="badge bg-primary me-2">{{ $file->updated_at }}</small>
                    <small class="badge bg-success me-2">{{ $file->size }}</small>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <nav class="d-flex justify-content-center">
        {{ $files->onEachSide(1)->links('partials.pagination') }}
    </nav>

    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileModalLabel">Просмотр</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeleteModalLabel">Подтверждение удаления</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="deleteModalBody">
                    Вы уверены, что хотите удалить этот файл?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteButton">Удалить</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        let confirmDeleteModal = document.getElementById('confirmDeleteModal');
        let confirmDeleteButton = document.getElementById('confirmDeleteButton');
        let currentFormId = null;

        document.querySelectorAll('.delete-button').forEach(function(button) {
            button.addEventListener('click', function() {
                currentFormId = button.getAttribute('data-form-id');
                let fileName = currentFormId.replace('deleteForm-', '');
                const deleteModalBody = document.getElementById('deleteModalBody');
                deleteModalBody.innerHTML = `Вы уверены, что хотите удалить файл ${fileName}?`;
                let modal = new bootstrap.Modal(confirmDeleteModal);
                modal.show();
            });
        });

        confirmDeleteButton.addEventListener('click', function() {
            if (currentFormId) {
                document.getElementById(currentFormId).submit();
            }
        });

        function showModal(fileType, filePath, fileExtension) {
            const viewModalBody = document.getElementById('viewModalBody');
            viewModalBody.innerHTML = '';

            let content;

            switch (fileType) {
                case 'video':
                    content = `<video controls autoplay class="w-100"><source src="${filePath}" type="video/${fileExtension}">Your browser does not support the video tag.</video>`;
                    break;
                case 'image':
                    content = `<img src="${filePath}" class="img-fluid" alt="Image">`;
                    break;
                case 'audio':
                    content = `<audio controls class="w-100"><source src="${filePath}" type="audio/${fileExtension}">Your browser does not support the audio tag.</audio>`;
                    break;
                case 'text':
                    content = `<iframe src="${filePath}" class="w-100" style="height: 80vh;" frameborder="0"></iframe>`;
                    break;
                case 'pdf':
                    content = `<embed src="${filePath}" type="application/pdf" class="w-100" style="height: 80vh;"></embed>`;
                    break;
                default:
                    content = 'Неподдерживаемый тип файла для предпросмотра';
            }

            viewModalBody.innerHTML = content;
            const fileModal = new bootstrap.Modal(document.getElementById('fileModal'));
            fileModal.show();

            document.getElementById('fileModal').addEventListener('hidden.bs.modal', stopMedia, { once: true });

        }

        function stopMedia() {
            const viewModalBody = document.getElementById('viewModalBody');
            viewModalBody.innerHTML = '';

            const video = document.getElementById('modalVideo');
            const audio = document.getElementById('modalAudio');

            if (video) {
                video.pause();
                video.currentTime = 0;
            }

            if (audio) {
                audio.pause();
                audio.currentTime = 0;
            }
        }

        document.getElementById('uploadForm').addEventListener('submit', function(event) {
    event.preventDefault();
    document.getElementById('submitButton').disabled = true;
    const fileInput = document.getElementById('file');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressContainer = document.getElementById('progressContainer');

    const formData = new FormData();
    Array.from(fileInput.files).forEach(file => {
        formData.append('files[]', file);
    });

    progressContainer.style.display = 'block';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    axios.post('/file', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-CSRF-TOKEN': csrfToken
        },
        onUploadProgress: function(progressEvent) {
            let percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            progressBar.style.width = percentCompleted + '%';
            progressBar.setAttribute('aria-valuenow', percentCompleted);
            progressBar.textContent = percentCompleted + '%';
        }
    }).then(function(response) {
        if (response.data.redirect) {
            window.location.href = response.data.redirect;
        } else {
            console.log('Files uploaded successfully');
        }
    }).catch(function(error) {
        console.error('Error uploading files');
    });
});

        document.querySelectorAll('.share-button').forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault();
                const link = this.getAttribute('data-link');
                copyToClipboard(link);
                showCopySuccessNotification();
            });
        });

        function copyToClipboard(text) {
            const input = document.createElement('textarea');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
        }

        function showCopySuccessNotification() {
            const notificationContainer = document.getElementById('notification-container');

            const notification = document.createElement('div');
            notification.classList.add('alert', 'alert-success', 'alert-dismissible', 'fade', 'show');
            notification.setAttribute('role', 'alert');
            notification.innerHTML = 'Ссылка скопирована';

            const closeButton = document.createElement('button');
            closeButton.classList.add('btn-close');
            closeButton.setAttribute('type', 'button');
            closeButton.setAttribute('data-bs-dismiss', 'alert');
            closeButton.setAttribute('aria-label', 'Close');

            notification.appendChild(closeButton);

            notificationContainer.appendChild(notification);
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@endsection
