<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Traits\FileControllerTrait;

class FileController extends Controller
{

    use FileControllerTrait;

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $search = $request->input('search');

        $directory = storage_path('app/uploads');
        $files = $this->readDirectory($directory);

        if ($search) {
            $filteredFiles = array_filter($files, function ($file) use ($search) {
                return str_contains($file->name, $search);
            });

            $files = array_values($filteredFiles);
        }

        foreach ($files as $file) {
            $file->thumbnail = route('api.file.thumbnail', $file->name);
        }

        return response()->json([
            'data' => $files,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:1048576',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $file->storeAs('uploads', $fileName);

        return response()->json(['message' => 'Файл сохранен']);
    }

    public function destroy($filename)
    {
        $filePath = 'uploads/' . $filename;
        $previewPath = 'previews/' . pathinfo($filename, PATHINFO_FILENAME) . '.png';

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }

        if (Storage::exists($previewPath)) {
            Storage::delete($previewPath);
        }

        return response()->json(['message' => 'Файл удален']);
    }

}
