<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Iman\Streamer\VideoStreamer;
use App\Traits\FileControllerTrait;

class FileController extends Controller
{

    use FileControllerTrait;

    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'sort' => 'nullable|string|max:255',
            'show_videos' => 'nullable|boolean',
        ]);

        $search = $request->input('search');
        $sort = $request->input('sort');
        $showVideos = $request->input('show_videos', false);

        $directory = storage_path('app/uploads');
        $files = $this->readDirectory($directory);

        $perPage = 12;
        $currentPage = $request->input('page', 1);

        $filesCollection = collect($files);

        if ($search) {
            $filesCollection = $filesCollection->filter(function ($file) use ($search) {
                return str_contains($file->name, $search);
            });
        }

        if ($sort) {
            switch ($sort) {
                case 'filesize_ASC':
                    $filesCollection = $filesCollection->sortBy(function ($file) use ($sort) {
                        return $file->unformatted_size;
                    });
                    break;
                case 'filesize_DESC':
                    $filesCollection = $filesCollection->sortByDesc(function ($file) use ($sort) {
                        return $file->unformatted_size;
                    });
            }
        }

        if (!$showVideos) {
            $filesCollection = $filesCollection->filter(function ($file) {
                return strtolower(pathinfo($file->name, PATHINFO_EXTENSION)) !== 'mp4';
            });
        }

        foreach ($files as $file) {
            $file->extension = strtolower(pathinfo($file->name, PATHINFO_EXTENSION));
            $file->type = $this->fileTypes[$file->extension] ?? 'unknown';
            $file->thumbnail = route('file.thumbnail', $file->name);
        }

        $currentPageFiles = $filesCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $files = new LengthAwarePaginator($currentPageFiles, $filesCollection->count(), $perPage, $currentPage, [
            'path' => '/files',
            'query' => $request->query(),
        ]);

        return view('file.index', compact('files'));
    }

    public function view($filename)
    {
        $filePath = storage_path('app/uploads/' . $filename);
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileType = $this->fileTypes[$fileExtension] ?? 'unknown';

        if ($fileType === 'video') {
            VideoStreamer::streamFile($filePath);

        } elseif ($fileType === 'image') {
            $fileContent = file_get_contents($filePath);
            $type = mime_content_type($filePath);

            return response($fileContent, 200)->header('Content-Type', $type);

        } elseif ($fileType === 'audio') {
            $response = new StreamedResponse(function () use ($filePath) {
                $file = fopen($filePath, 'rb');

                fpassthru($file);
                fclose($file);
            });

            $response->headers->set('Content-Type', mime_content_type($filePath));
            $response->headers->set('Content-Length', filesize($filePath));
            $response->headers->set('Accept-Ranges', 'bytes');
            $response->headers->set('Cache-Control', 'no-cache');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Connection', 'keep-alive');

            return $response;

        } elseif ($fileType === 'text' || $fileType === 'pdf') {
            $fileContent = file_get_contents($filePath);
            $type = mime_content_type($filePath);

            return response($fileContent, 200)->header('Content-Type', $type);
        } else {
            abort(415, 'Неподдерживаемый тип файла');
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:1048576',
        ]);

        $files = $request->file('files');

        foreach ($files as $file) {
            $fileName = $file->getClientOriginalName();
            $file->storeAs('uploads', $fileName);
        }

        $request->session()->flash('success', 'Файлы успешно загружены');

        return response()->json([
            'redirect' => route('file.index')
        ]);
    }


    public function destroy($filename)
    {
        $filePath = 'uploads/' . $filename;

        if (Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
        return redirect()->route('file.index')->with('success', 'Файл успешно удален');
    }

    public function shared($token)
    {
        $fileName = Crypt::decryptString($token);
        $filePath = storage_path('app/uploads/' . $fileName);

        return response()->download($filePath, $fileName);
    }

}
