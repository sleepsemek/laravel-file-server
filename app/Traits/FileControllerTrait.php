<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Intervention\Image\ImageManager;
use Pawlox\VideoThumbnail\VideoThumbnail;
use Intervention\Image as Image;

trait FileControllerTrait
{

    private array $fileTypes = [
        'pdf' => 'pdf',
        'txt' => 'text',
        'doc' => 'word',
        'docx' => 'word',
        'odt' => 'word',
        'xls' => 'excel',
        'xlsx' => 'excel',
        'ods' => 'excel',
        'pptx' => 'powerpoint',
        'jpg' => 'image',
        'jpeg' => 'image',
        'png' => 'image',
        'gif' => 'image',
        'bmp' => 'image',
        'webp' => 'image',
        'mp4' => 'video',
        'avi' => 'video',
        'mov' => 'video',
        'mp3' => 'audio',
        'wav' => 'audio',
        'ogg' => 'audio',
        'flac' => 'audio',
        'zip' => 'archive',
        'rar' => 'archive',
    ];

    private function readDirectory($directory)
    {
        $result = [];

        if (is_dir($directory)) {
            $items = scandir($directory);

            foreach ($items as $item) {
                $path = $directory . DIRECTORY_SEPARATOR . $item;

                if ($item == '.' || $item == '..' || is_dir($path)) {
                    continue;
                }

                $shareLink = Crypt::encryptString($item);

                $fileInfo = new \stdClass();
                $fileInfo->name = $item;
                $fileInfo->unformatted_size = filesize($path);
                $fileInfo->size = $this->formatFileSize($fileInfo->unformatted_size);
                $fileInfo->updated_at = date("d.m.Y", filemtime($path));
                $fileInfo->share_link = $shareLink;
                $fileInfo->path = $path;
                $fileInfo->filetime = filemtime($path);
                $result[] = $fileInfo;
            }

            usort($result, function($a, $b) {
                return $b->filetime - $a->filetime;
            });

        }

        return $result;
    }

    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function download($filename)
    {
        $filePath = storage_path('app/uploads/' . $filename);

        return response()->download($filePath, $filename);
    }

    public function thumbnail($filename)
    {
        $filePath = storage_path('app/uploads/' . $filename);
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $fileType = $this->fileTypes[$fileExtension] ?? 'unknown';

        $previewDir = storage_path('app/previews/');
        if ($fileType === 'video') {
            if (!file_exists($previewDir)) {
                mkdir($previewDir, 0777, true);
            }

            $previewPath = $previewDir . pathinfo($filename, PATHINFO_FILENAME) . '.png';

            if (!file_exists($previewPath)) {
                $videoThumbnail = new VideoThumbnail();
                $videoThumbnail->createThumbnail(
                    $filePath,
                    $previewDir,
                    pathinfo($filename, PATHINFO_FILENAME) . '.png',
                    10,
                    620,
                    480
                );
            }

            $fileContent = file_get_contents($previewPath);
            $type = mime_content_type($previewPath);
        } else {
            $filePath = $fileType === 'image' ? $filePath : $previewDir . $fileType . '.png';

            $manager = new ImageManager(new Image\Drivers\Gd\Driver());
            $img = $manager->read($filePath);
            $img->coverDown(620, 480);

            $fileContent = $img->toPng();
            $type = 'image/png';
        }

        return response($fileContent, 200)->header('Content-Type', $type);
    }

}
