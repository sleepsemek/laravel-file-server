<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlantUMLController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'plantuml_code' => 'string'
            ]);

            $plantUMLCode = $request->input('plantuml_code');
        } else {
            $plantUMLCode = "@startuml\n\tТолян -> Алиса\n@enduml";
        }

        $inputFilePath = storage_path('app/diagram.txt');
        $outputFileName = 'diagram.png';

        file_put_contents($inputFilePath, $plantUMLCode);

        $command = "java -jar " . storage_path('app/plantuml.jar') . " -charset UTF-8 -config " . storage_path('app/config.txt') . " -tpng " . $inputFilePath . " -o " . public_path('diagram/') . " -filename " . $outputFileName;
        exec($command, $output, $returnVar);

        return view('plantuml.index', ['imagePath' => asset('diagram/' . $outputFileName), 'plantumlCode' => $plantUMLCode]);
    }

    public function getSvg(Request $request)
    {
        $request->validate([
            'plantuml_code' => 'string'
        ]);

        $plantUMLCode = $request->input('plantuml_code');

        $inputFilePath = storage_path('app/diagram.txt');
        $outputFileName = 'diagram.svg';

        file_put_contents($inputFilePath, $plantUMLCode);

        $command = "java -jar " . storage_path('app/plantuml.jar') . " -charset UTF-8 -config " . storage_path('app/config.txt') . " -tsvg " . $inputFilePath . " -o " . public_path('diagram/') . " -filename " . $outputFileName;

        exec($command, $output, $returnVar);

        return response()->download(public_path('diagram/' . $outputFileName), $outputFileName);
    }

}
