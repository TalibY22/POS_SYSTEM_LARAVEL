<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ZohoAuthController extends Controller
{
    public function redirectToZoho()
    {
        // Generate and store state parameter to prevent CSRF attacks//THIS CODE IS TOO FOCUSED  ON SECURITY OF THE COMPUTER 
        $state = Str::random(40);
        session(['zoho_auth_state' => $state]);

        $queryParams = http_build_query([
            'scope' => 'ZohoBooks.invoices.READ,ZohoBooks.invoices.CREATE,ZohoBooks.contacts.CREATE,ZohoBooks.fullaccess.all,ZohoCRM.modules.ALL',
            'client_id' => env('ZOHO_CLIENT_ID'),
            'response_type' => 'code',
            'redirect_uri' => env('ZOHO_REDIRECT_URI'),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state
        ]);

        return redirect(env('ZOHO_AUTHORIZATION_URL') . '?' . $queryParams);
    }

    //
    public function handleZohoCallback(Request $request)
    {
        // Verify state parameter to prevent CSRF attacks
        

        $code = $request->query('code');
        if (!$code) {
            return redirect()->route('home')->withErrors('Authorization code not found');
        }

        try {
            $response = Http::timeout(30)->asForm()->post(env('ZOHO_TOKEN_URL'), [
                'code' => $code,
                'client_id' => env('ZOHO_CLIENT_ID'),
                'client_secret' => env('ZOHO_CLIENT_SECRET'),
                'redirect_uri' => env('ZOHO_REDIRECT_URI'),
                'grant_type' => 'authorization_code',
            ]);

            if (!$response->successful()) {
                Log::error('Zoho token exchange failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return redirect()->route('home')->withErrors('Failed to exchange authorization code');
            }

            $data = $response->json();
             //CHECKS IF DATA IS THERE IF IT IS THERE IT SAVES IN THE SESSION 
            if (isset($data['access_token'])) {
                // Store tokens securely
                session([
                    'zoho_access_token' => $data['access_token'],
                    'zoho_refresh_token' => $data['refresh_token'] ?? null,
                    'zoho_token_expires_at' => now()->addSeconds($data['expires_in']),
                    'zoho_api_domain' => $data['api_domain'] ?? null
                ]);

                return redirect()->route('home')->with('success', 'Successfully connected to Zoho');
            }

            return redirect()->route('home')->withErrors('Invalid response from Zoho');

        } catch (\Exception $e) {
            Log::error('Zoho authentication error', [
                'error' => $e->getMessage()
            ]);
            return redirect()->route('home')->withErrors('Authentication failed: ' . $e->getMessage());
        }
    }
 
    public function refreshZohoAccessToken()
    {
        $refreshToken = session('zoho_refresh_token');

        if (!$refreshToken) {
            return redirect()->route('zoho.redirect');
        }

        try {
            $response = Http::timeout(30)->asForm()->post(env('ZOHO_TOKEN_URL'), [
                'refresh_token' => $refreshToken,
                'client_id' => env('ZOHO_CLIENT_ID'),
                'client_secret' => env('ZOHO_CLIENT_SECRET'),
                'grant_type' => 'refresh_token',
            ]);

            if (!$response->successful()) {
                Log::error('Zoho token refresh failed', [
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return redirect()->route('zoho.redirect');
            }

            $data = $response->json();

            if (isset($data['access_token'])) {
                session([
                    'zoho_access_token' => $data['access_token'],
                    'zoho_token_expires_at' => now()->addSeconds($data['expires_in']),
                ]);
                return true;
            }

            return redirect()->route('zoho.redirect');

        } catch (\Exception $e) {
            Log::error('Zoho token refresh error', [
                'error' => $e->getMessage()
            ]);
            return redirect()->route('zoho.redirect');
        }
    }

    public function getZohoInvoices()
    {
        if (now()->greaterThan(session('zoho_token_expires_at'))) {
            $refreshResult = $this->refreshZohoAccessToken();
            if ($refreshResult !== true) {
                return $refreshResult;
            }
        }

        try {
            $accessToken = session('zoho_access_token');
            
            // Get your region's API domain from environment variable
            $apiDomain =  'https://www.zohoapis.com';
            
            // Construct the full API URL
            $apiUrl = "{$apiDomain}/books/v3/invoices";
            
            Log::info('Making Zoho API request', ['url' => $apiUrl]); // Debug log

            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
                ])
                ->get($apiUrl);

            if (!$response->successful()) {
                Log::error('Zoho API request failed', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'url' => $apiUrl
                ]);
                return response()->json(['error' => 'Failed to fetch invoices'], 500);
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Zoho API error', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch invoices'], 500);
        }
    }


 public function getZohoCustomers()
{
    if (now()->greaterThan(session('zoho_token_expires_at'))) {
        $refreshResult = $this->refreshZohoAccessToken();
        if ($refreshResult !== true) {
            return $refreshResult;
        }
    }

    try {
        $accessToken = session('zoho_access_token');
        
        
        $apiUrl = 'https://www.zohoapis.com/crm/v7/Contacts?fields=First_name,Last_Name,Email'; // Adjust based on your requirements

        Log::info('Making Zoho CRM API request', ['url' => $apiUrl]); // Debug log

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Zoho-oauthtoken ' . $accessToken,
            ])
            ->get($apiUrl);

        if (!$response->successful()) {
            Log::error('Zoho CRM API request failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'url' => $apiUrl
            ]);
            return response()->json(['error' => 'Failed to fetch customers'], 500);
        }

        return $response->json();

    } catch (\Exception $e) {
        Log::error('Zoho CRM API error', [
            'error' => $e->getMessage()
        ]);
        return response()->json(['error' => 'Failed to fetch customers'], 500);
    }
}
}
