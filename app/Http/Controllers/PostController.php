<?php

namespace App\Http\Controllers;

use App\Http\Requests\Publish\CreatePublish;
use App\Http\Requests\Publish\UpdatePublish;
use App\Page;
use App\Post;
use App\Service\Page\PagePublishtDataService;
use App\Service\Publish\PublishCreateService;
use App\Service\Publish\PublishDeleteService;
use App\Service\Publish\PublishUpdateService;
use Illuminate\Http\Request;
use JavaScript;

class PostController extends Controller
{
    public function create(Page $page, CreatePublish $request)
    {
        $createPublishService = new PublishCreateService($request->all(), $page);
        return response()->json($createPublishService->process());
    }

    public function update(Post $post, UpdatePublish $request)
    {
        $createPublishService = new PublishUpdateService($request->all(), $post);
        return response()->json($createPublishService->process());
    }

    public function get(Page $page, Request $request)
    {
        $service = new PagePublishtDataService($page, $request);
        return response()->json($service->getPagePublish($page, $request));
    }

    public function delete(Post $post, Request $request, PublishDeleteService $service)
    {
        $this->authorize('forceDelete', $post);  
        
        $service->delete($post);
    }

    public function index(Request $request, Page $page)
    {
        JavaScript::put([
            'page' => $page
        ]);
        return view('post.index');
    }
}
