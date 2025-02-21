<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sabre\DAV;
use Sabre\DAV\Browser\Plugin as BrowserPlugin;

class WebDavController extends Controller
{
    public function serve()
    {
        $rootDirectory = new DAV\FS\Directory(storage_path('app/uploads'));
        $server = new DAV\Server($rootDirectory);

        $server->setBaseUri('/webdav/');
        $server->addPlugin(new BrowserPlugin());

        $server->exec();
    }
}
