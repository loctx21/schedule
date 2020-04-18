<?php

namespace App\Http\Controllers;

use App\Helper\Utils;
use App\Http\Requests\CreatePage;
use App\Http\Requests\Page\UpdatePage;
use App\Page;
use App\Service\Page\PageCreateService;
use App\Service\Page\PageUpdateService;
use Illuminate\Http\Request;
use JavaScript;

class PageController extends Controller
{
    public function create(CreatePage $request)
    {
        $service = new PageCreateService($request);
        return response()->json($service->create($request->validated()));
    }

    public function update(Page $page, UpdatePage $request, PageUpdateService $service)
    {
        return response()->json($service->update($page, $request->all()));
    }

    public function editView(Page $page, Request $request) 
    {
        JavaScript::put([
            'page' => $page,
            'timezones' => Utils::getTimezoneArr()
        ]);
        return view('page.edit');
    }
}
