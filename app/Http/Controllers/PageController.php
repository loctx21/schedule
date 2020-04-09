<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePage;
use App\Page;

class PageController extends Controller
{
    public function create(CreatePage $request)
    {
        $data = $request->validated();
        $page = new Page;
        
        $page->fill($data);
        $page->save();

        return response()->json($page);
    }
}
