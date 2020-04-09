<?php

namespace Tests\Feature;

use App\User;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\FacebookResponse;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\Mock;
use Session;

class FacebookCallbackTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUnAuthorizedUserRedirect()
    {
        $response = $this->get('/dashboard/fbcallback');

        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testHandleFacebookResponseException()
    {
        $user = factory(User::class)->create();

        $fbLoginHelperMock = Mockery::mock();

        $fbRespException = $this->getFacebookRepsonseException("Facebook response exception");
        $fbLoginHelperMock->shouldReceive('getAccessToken')->andThrow(
            $fbRespException
        );

        $fbMock = Mockery::mock('facebook');
        $fbMock->shouldReceive('getRedirectLoginHelper')->andReturn($fbLoginHelperMock);
        app()->instance('facebook', $fbMock);

        $response = $this->actingAs($user)->get('/dashboard/fbcallback');
        
        $response->assertStatus(302);
        $response->assertRedirect('dashboard');
        $response->assertSessionHas('error', "Facebook response exception");
    }

    public function testHandleFacebookSDKException()
    {
        $user = factory(User::class)->create();

        $fbLoginHelperMock = Mockery::mock();
        $fbLoginHelperMock->shouldReceive('getAccessToken')->andThrow(
            FacebookSDKException::class, 'test', -1
        );

        $fbMock = Mockery::mock('facebook');
        $fbMock->shouldReceive('getRedirectLoginHelper')->andReturn($fbLoginHelperMock);
        app()->instance('facebook', $fbMock);

        $response = $this->actingAs($user)->get('/dashboard/fbcallback');
        
        $response->assertStatus(302);
        $response->assertRedirect('dashboard');
        $response->assertSessionHas('error', "App configuration error. Please contact Admin.");
    }

    public function testHandleEmptyAccessTokenWithoutException()
    {
        $user = factory(User::class)->create();

        $fbLoginHelperMock = Mockery::mock();
        $fbLoginHelperMock->shouldReceive('getAccessToken')->andreturn(null);
        $fbLoginHelperMock->shouldReceive('getError')->andReturn(false);

        $fbMock = Mockery::mock('facebook');
        $fbMock->shouldReceive('getRedirectLoginHelper')->andReturn($fbLoginHelperMock);
        app()->instance('facebook', $fbMock);

        $response = $this->actingAs($user)->get('/dashboard/fbcallback');
        
        $response->assertStatus(302);
        $response->assertRedirect('dashboard');
        $response->assertSessionHas('error', "Unknown error. Please try again later.");
    }

    public function testHandleSuccessRetrieveAccessToken()
    {
        $user = factory(User::class)->create();

        $clientMock = Mockery::mock();
        $clientMock->shouldReceive('getLongLivedAccessToken')->andReturn('live_long_access_token');

        $fbLoginHelperMock = Mockery::mock();
        $fbLoginHelperMock->shouldReceive('getAccessToken')->andreturn("access_token_string");

        $fbMock = Mockery::mock('facebook');
        $fbMock->shouldReceive('getRedirectLoginHelper')->andReturn($fbLoginHelperMock);
        $fbMock->shouldReceive('getOAuth2Client')->andReturn($clientMock);
        app()->instance('facebook', $fbMock);

        $response = $this->actingAs($user)->get('/dashboard/fbcallback');

        $response->assertStatus(302);
        $response->assertRedirect('dashboard');

        $user->fresh();
        $this->assertEquals($user->fb_access_token, 'live_long_access_token');
    }

    /**
     * Mock FacebookResponseException with customized message and code
     * 
     * @param String $message
     * @param Integer $code
     * @return FacebookResponseException
     */
    public function getFacebookRepsonseException($message, $code=-1)
    {
        $fbResponseMock = Mockery::mock(FacebookResponse::class);
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([
            "error" => [
                "message" => $message,
                "code"  => $code
            ]
        ]);
        $fbRespException = new FacebookResponseException($fbResponseMock);

        return $fbRespException;
    }
}
