<?php

namespace App\Http\Controllers;

use RapidWeb\GoogleOAuth2Handler\GoogleOAuth2Handler;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

use RapidWeb\GooglePeopleAPI\GooglePeople;
use RapidWeb\GooglePeopleAPI\Contact;
use stdClass;
use RapidWeb\GoogleOAuth2Handler\Google_Client;

use Illuminate\Http\Request;

/**
 * 
 */
class GoogleOAuth2HandlerCustom extends GoogleOAuth2Handler
{
	private $clientId;
    private $clientSecret;
    private $scopes;
    private $refreshToken;
    private $client;

	function __construct($clientId, $clientSecret, $scopes, $refreshToken)
	{
		$this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->scopes = $scopes;
        $this->refreshToken = $refreshToken;

        $this->setupClient();
        // dd($this->client);
	}

	public function setupClient()
    {
        $this->client = new \Google_Client();

        $this->client->setClientId($this->clientId);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri('http://localhost:8000/callback');
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account');

        foreach($this->scopes as $scope)  {
            $this->client->addScope($scope);
        }

        if ($this->refreshToken) {
            $this->client->refreshToken($this->refreshToken);
        } else {
            $this->authUrl = $this->client->createAuthUrl();
        }
    }

    public function setRefreshToken($authCode)
    {
    	$this->client->authenticate($authCode);
        $accessToken = $this->client->getAccessToken();
        return $this->refreshToken = $accessToken['refresh_token'];
    }

    public function performRequest($method, $url, $body = null)
    {
        $httpClient = $this->client->authorize();
        $request = new GuzzleRequest($method, $url, [], $body);
        $response = $httpClient->send($request);
        return $response;
    }
}

class TestController extends Controller
{
	public function createGoogleOAuth2Handler($refreshToken = null)
	{
		$clientId     = '468050198908-se8k9p2ob7if1n224vvnqvsj4bafauf8.apps.googleusercontent.com';
		$clientSecret = 'toWtKvd1mlom-qycLsAd4tIB';
		$scopes       = ['https://www.googleapis.com/auth/userinfo.profile', 'https://www.googleapis.com/auth/contacts', 'https://www.googleapis.com/auth/contacts.readonly'];

		$googleOAuth2Handler = new GoogleOAuth2HandlerCustom($clientId, $clientSecret, $scopes, $refreshToken);

		return $googleOAuth2Handler;
	}

    public function index()
    {

    	$googleOAuth2Handler = $this->createGoogleOAuth2Handler();
    	return redirect($googleOAuth2Handler->authUrl);		
    }

    public function callback(Request $request)
    {
    	$googleOAuth2Handler = $this->createGoogleOAuth2Handler();
    	$googleOAuth2Handler->setRefreshToken($request->code);

		$people = new GooglePeople($googleOAuth2Handler);

		$contact = new Contact($people);
		$contact->names[0] = new stdClass;
		$contact->names[0]->givenName = 'Testy';
		$contact->names[0]->familyName = 'McTest Test';
		$contact->save();

		return redirect('/');
    }
}
