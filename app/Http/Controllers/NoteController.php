<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function index() {
        $notes = Note::orderByDesc('updated_at')->paginate(5);

        return view('note.index', compact('notes'));
    }

    public function store(Request $request) {
        $note = $request->validate([
            'name' => 'string|max:100',
            'text' => 'string|required',
        ]);

        $note['name'] = isset($note['name']) ? $note['name'] : "Без названия";

        Note::create($note);

        return redirect()->route('note.index')->with('success', 'Заметка создана');
    }

    public function update(Request $request, $id) {
        $validatedData = $request->validate([
            'name' => 'string|max:100',
            'text' => 'string|required',
        ]);

        $note = Note::findOrFail($id);
        $note->update($validatedData);

        return redirect()->route('note.index')->with('success', 'Заметка обновлена');
    }

    public function destroy($id) {
        $note = Note::findOrFail($id);
        $note->delete();

        return redirect()->route('note.index')->with('success', 'Заметка удалена');
    }

}
