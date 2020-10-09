<?php

namespace App\Http\Controllers;

use App\ServiceClasses\Texte\HermeneusTextCreator;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function convert(Request $request)
    {
        $validatedData = $request->validate([
            'text_submitted' => 'sometimes|string|required',
        ]);
        return (new HermeneusTextCreator($validatedData))->makePreviewXML();

    }
}
