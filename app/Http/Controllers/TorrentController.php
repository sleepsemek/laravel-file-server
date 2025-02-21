<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Transmission\Client;
use Transmission\Transmission;

class TorrentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'torrent' => 'required|file|mimes:torrent',
        ]);

        $torrentFile = $request->file('torrent');
        $base64Content = base64_encode(file_get_contents($torrentFile->getPathname()));

        $transmission = $this->getTransmission();

        $transmission->add($base64Content, true);

        return redirect()->route('torrent.index')->with('success', 'Торрент добавлен');
    }

    public function index()
    {
        $transmission = $this->getTransmission();

        $torrents = $transmission->all();

        return view('torrent.index', compact('torrents'));
    }

    public function view($hash)
    {
        $transmission = $this->getTransmission();

        $torrent = $transmission->get($hash);

        $statuses = ['Остановлен', 'Ожидание проверки', 'Проверка', 'Ожидание загрузки', 'Загрузка', 'Ожидание раздачи', 'Раздача'];

        $data = [
            'status' => $statuses[$torrent->getStatus()],
            'finished' => $torrent->isFinished() ? 'Завершен' : 'Активен',
            'peers' => count($torrent->getPeers()),
            'progress' => round($torrent->getPercentDone(), 2),
            'download' => $this->formatFileSize($torrent->getDownloadRate()),
            'upload' => $this->formatFileSize($torrent->getUploadRate()),
            'size' => $this->formatFileSize($torrent->getSize()),
        ];

        return response()->json($data);
    }

    public function stop($hash)
    {
        $transmission = $this->getTransmission();
        $torrent = $transmission->get($hash);
        $torrent->stop();

        return redirect()->route('torrent.index')->with('success', 'Загрузка остановлена');
    }

    public function start($hash)
    {
        $transmission = $this->getTransmission();
        $torrent = $transmission->get($hash);
        $torrent->start();

        return redirect()->route('torrent.index')->with('success', 'Загрузка возобновлена');
    }

    public function destroy($hash)
    {
        $transmission = $this->getTransmission();
        $torrent = $transmission->get($hash);
        $torrent->remove();

        return redirect()->route('torrent.index')->with('success', 'Торрент удален без файла');
    }

    private function getTransmission()
    {
        $client = new Client();
        $client->authenticate('transmission', 'transmission');

        $transmission = new Transmission();
        $transmission->setClient($client);
        $transmission->setHost('localhost');
        $transmission->setPort(9091);

        return $transmission;
    }

    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
