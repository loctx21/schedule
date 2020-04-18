<?php

namespace App\Http\Controllers;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Illuminate\Http\Request;
use JavaScript;

class DashboardController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Retrieve dashboard index page
     * 
     * @return \App\View
     */
    public function index(Request $request)
    {
        $fb = app()->make('facebook');
        $helper = $fb->getRedirectLoginHelper();
        $loginUrl = $helper->getLoginUrl(url('/dashboard/fbcallback'), config('facebook.permission'));

        $user = $request->user();

        JavaScript::put([
            'fb_logined' => !empty($user->fb_access_token),
            'fb_login_url' => $loginUrl,
            'pages' => $user->pages,
            'app_id' => config('facebook.app_id')
        ]);

        return view('dashboard.index');
    }
    
    /**
     * Handle get user's facebook long lived access token
     * Redirect to home page (with error message)
     */
    public function callback(Request $request)
    {
        $fb = app()->make('facebook');
        $helper = $fb->getRedirectLoginHelper();
        $accessToken = $error = null;
        
        try {
            $accessToken = $helper->getAccessToken();
        } catch (FacebookResponseException $e) {
            $error = $e->getMessage();
        } catch (FacebookSDKException $e) {
            $error = "App configuration error. Please contact Admin.";
        } 

        if (!$error && !isset($accessToken)) {
            if ($helper->getError()) {
                $error = $helper->getErrorReason() . ' - ' . $helper->getErrorDescription();
            } else {
                $error = "Unknown error. Please try again later.";
            }
        }

        if (!empty($error))
            return redirect()->route('dashboard')->with('error', $error);

        $user = $request->user();
        $user->fb_access_token = $fb->getOAuth2Client()->getLongLivedAccessToken($accessToken);
        $user->save();
        
        return redirect()->route('dashboard');
    }
}
