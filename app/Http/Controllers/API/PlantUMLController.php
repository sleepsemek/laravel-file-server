<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlantUMLController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'code' => 'string'
            ]);

            $plantUMLCode = $request->input('code');
        } else {
            $plantUMLCode = "@startuml\n\tТолян -> Алиса\n@enduml";
        }

        $inputFilePath = storage_path('app/diagram.txt');
        $outputFileName = 'diagram.png';

        file_put_contents($inputFilePath, $plantUMLCode);

        $command = "java -jar " . storage_path('app/plantuml.jar') . " -charset UTF-8 -config " . storage_path('app/config.txt') . " -tpng " . $inputFilePath . " -o " . public_path('diagram/') . " -filename " . $outputFileName;
        exec($command, $output, $returnVar);

        $imageUrl = asset('diagram/' . $outputFileName);

        return response()->json([
            'imageURL' => $imageUrl,
            'code' => $plantUMLCode,
        ]);
    }
}
