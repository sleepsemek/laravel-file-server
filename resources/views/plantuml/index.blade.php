@extends('layouts.app')

@section('content')
    <div id="notification-container" class="notification-container"></div>

    <h4>Генератор диаграмм PlantUML</h4>

    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-12">
                <form action="{{ route('uml.index') }}" method="POST">
                    @csrf
                    <div class="input-group mb-3">
                        <textarea class="form-control" id="plantuml_code" name="plantuml_code" rows="6">{{ $plantumlCode ?? '' }}</textarea>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-sitemap"></i></button>
                    </div>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="m-0">Вывод</h4>
                    <div class="d-flex">
                        <form action="{{ route('uml.svg') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plantuml_code" value="{{ $plantumlCode }}"/>
                            <button type="submit" class="btn btn-primary me-2"><i class="fa fa-download"></i> SVG</button>
                        </form>
                        <a href="{{ $imagePath }}" class="btn btn-primary" download><i class="fa fa-download"></i> PNG</a>
                        @if(\Illuminate\Support\Facades\Auth::check())
                        <form action="{{ route('note.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="text" value="{{ $plantumlCode }}"/>
                            <button type="submit" class="btn btn-primary ms-2"><i class="fa fa-save"></i> Заметки</button>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="d-flex justify-content-center">
                    <img src="{{ $imagePath ?? '' }}?t={{ time() }}" class="img-fluid" alt="PlantUML Diagram">
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            adjustTextareaHeightByClass('form-control');
        });

        function adjustTextareaHeightByClass(className) {
            var elements = document.getElementsByClassName(className);
            for (var i = 0; i < elements.length; i++) {
                elements[i].style.height = 'auto';
                elements[i].style.height = (elements[i].scrollHeight + 5) + 'px';
            }
        }
    </script>
@endsection
