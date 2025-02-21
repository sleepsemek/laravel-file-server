<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <h3 href="#">Файлопомойка</h3>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="{{ route('file.index') }}">Файлы</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('note.index') }}">Заметки</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('uml.index') }}">UML диаграммы</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('torrent.index') }}">Торренты</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
