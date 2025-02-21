<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $notes = Note::orderByDesc('updated_at')->get();

        return response()->json([
            'data' => $notes,
        ]);
    }

    public function store(Request $request) {
        $note = $request->validate([
            'name' => 'string|max:100',
            'text' => 'string|required',
        ]);

        $note['name'] = isset($note['name']) ? $note['name'] : "Без названия";

        Note::create($note);

        return response()->json(['message' => 'Заметка создана']);
    }

    public function update(Request $request, $id) {
        $validatedData = $request->validate([
            'name' => 'string|max:100',
            'text' => 'string|required',
        ]);

        $note = Note::findOrFail($id);
        $note->update($validatedData);

        return response()->json(['message' => 'Заметка обновлена']);
    }

    public function destroy($id) {
        $note = Note::findOrFail($id);
        $note->delete();

        return response()->json(['message' => 'Заметка удалена']);
    }

}
