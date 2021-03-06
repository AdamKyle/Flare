<?php

namespace App\Admin\Controllers;

use Storage;
use App\Http\Controllers\Controller;
use App\Flare\Models\GameMap;
use App\Admin\Requests\MapUploadValidation;

class MapsController extends Controller {

    public function index() {
        return view('admin.maps.maps', [
            'maps' => GameMap::all()
        ]);
    }

    public function uploadMap() {
        return view('admin.maps.upload');
    }

    public function upload(MapUploadValidation $request) {
        $path = Storage::disk('maps')->putFile($request->name, $request->map);

        GameMap::create([
            'name'          => $request->name,
            'path'          => $path,
            'default'       => $request->default === 'yes' ? true : false,
            'kingdom_color' => $request->kingdom_color,
        ]);

        return redirect()->route('maps')->with('success', $request->name . ' uploaded successsfully.');
    }
}
