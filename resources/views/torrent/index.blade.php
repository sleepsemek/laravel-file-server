@extends('layouts.app')

@section('content')
    <div id="notification-container" class="notification-container"></div>

    <h4>Новый торрент</h4>
    <form action="{{ route('torrent.store') }}" method="POST" enctype="multipart/form-data" class="form-inline" id="uploadForm">
        @csrf
        <div class="input-group mt-3">
            <input type="file" name="torrent" class="form-control" required>
            <button type="submit" class="btn btn-primary" id="submitButton"><i class="fa fa-upload"></i></button>
        </div>
    </form>

    <div class="d-flex justify-content-center" style="margin: 20px;">
        <div id="spinner" class="spinner-border text-primary" role="status" style="display: none;">
            <span class="visually-hidden">Отправка</span>
        </div>
    </div>

    <h4>Список торрентов</h4>

    <div class="row mt-4">
        @foreach ($torrents as $torrent)
            <div class="col-md-4 mb-3">
                <div class="card h-100" id="torrent-{{ $torrent->getHash() }}">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="card-title">{{ $torrent->getName() }}</h5>
                            <p class="card-text"><span class="torrent-finished fw-bold text-primary"></span></p>
                        </div>
                        <div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="card-text">Статус: <span class="torrent-status text-muted"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="card-text">Пиры: <span class="torrent-peers text-muted"></span></p>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <p class="card-text">Загрузка: <span class="torrent-download-speed text-muted"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="card-text">Раздача: <span class="torrent-upload-speed text-muted"></span></p>
                                </div>
                            </div>
                            <div class="progress mb-3">
                                <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p class="card-text">Размер: <span class="torrent-size"></span></p>
                            <div class="row">
                                <div class="input-group">
                                    @if(!$torrent->isFinished())
                                        @if($torrent->getStatus() == '0')
                                            <form action="{{ route('torrent.start', $torrent->getHash()) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-primary me-1 px-4"><i class="fa fa-play"></i></button>
                                            </form>
                                        @else
                                            <form action="{{ route('torrent.stop', $torrent->getHash()) }}" method="post">
                                                @csrf
                                                <button type="submit" class="btn btn-primary me-1 px-4"><i class="fa fa-stop"></i></button>
                                            </form>
                                        @endif
                                    @endif
                                    <form action="{{ route('torrent.destroy', $torrent->getHash()) }}" method="post">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger px-4"><i class="fa fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection

@section('script')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(event) {
            document.getElementById('spinner').style.display = 'block';
            document.getElementById('submitButton').disabled = true;
        });

        function updateTorrents() {
            @foreach ($torrents as $torrent)
            $.ajax({
                url: '{{ route('torrent.view', $torrent->getHash()) }}',
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: {
                    user_id: {{ auth()->id() }},
                },
                success: function(data) {
                    $('#torrent-{{ $torrent->getHash() }} .torrent-status').text(data.status);
                    $('#torrent-{{ $torrent->getHash() }} .torrent-peers').text(data.peers);
                    $('#torrent-{{ $torrent->getHash() }} .torrent-finished').text(data.finished);
                    $('#torrent-{{ $torrent->getHash() }} .torrent-download-speed').text(data.download + '/s');
                    $('#torrent-{{ $torrent->getHash() }} .torrent-upload-speed').text(data.upload + '/s');
                    $('#torrent-{{ $torrent->getHash() }} .progress-bar').css('width', data.progress + '%');
                    $('#torrent-{{ $torrent->getHash() }} .progress-bar').html(data.progress + '%');
                    $('#torrent-{{ $torrent->getHash() }} .torrent-size').html(data.size);
                },
                error: function(xhr, status, error) {
                    console.log('Server response:', xhr.responseText);
                }
            });
            @endforeach
        }

        updateTorrents();
        setInterval(updateTorrents, 5000);
    </script>
@endsection
