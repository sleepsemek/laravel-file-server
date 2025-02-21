@extends('layouts.app')

@section('content')

<h4>Список заметок</h4>
<table class="table">
    <thead>
        <tr>
            <th>Текст</th>
            <th style="width: 1px">Действия</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <form action="{{ route('note.store') }}" method="POST">
                @csrf
                <td class="align-start">
                    <textarea class="form-control" name="text" rows="5" required>{{ old('text') }}</textarea>
                </td>
                <td class="align-middle">
                    <div class="input-group d-flex flex-nowrap justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i></button>
                    </div>
                </td>
            </form>
        </tr>
        @foreach($notes as $note)
        <tr>
            <td class="align-start">
                <form action="{{ route('note.update', ['id' => $note->id]) }}" method="POST">
                    @csrf
                    @method('PATCH')
                <textarea class="form-control" name="text" rows="5" required>{{ $note->text }}</textarea>
            </td>
            <td class="align-middle">
               <div class="input-group d-flex flex-nowrap justify-content-end">
                    <button type="button" class="btn btn-success submit-btn" data-form-id="plantumlForm-{{ $note->id }}"><i class="fa fa-sitemap"></i></button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-edit"></i></button>
                    <button type="button" class="btn btn-danger rounded-end submit-btn" data-form-id="deleteForm-{{ $note->id }}"><i class="fa fa-trash"></i></button>
                </div>
                </form>
                <form id="deleteForm-{{ $note->id }}" action="{{ route('note.destroy', ['id' => $note->id]) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
                <form id="plantumlForm-{{ $note->id }}" action="{{ route('uml.index') }}" method="POST" style="display: none">
                    @csrf
                    <input type="hidden" name="plantuml_code" value="{{ $note->text }}"/>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="d-flex justify-content-center">
    {{ $notes->links('partials.pagination') }}
</div>

@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        adjustTextareaHeightByClass('form-control');
    });

    document.querySelectorAll('.submit-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const formId = this.dataset.formId;
            const form = document.getElementById(formId);
            if (form) {
                form.submit();
            }
        });
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
