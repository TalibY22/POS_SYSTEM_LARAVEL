<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\ZohoAuthController;

class BooksController extends Controller
{
    public function getZohoInvoices()
    {
        if (now()->greaterThan(session('zoho_token_expires_at'))) {
            
            $authController = new ZohoAuthController();
            $refreshResult = $authController->refreshZohoAccessToken();
            if ($refreshResult !== true) {
                return $refreshResult;
            }
        }

        try {
            $accessToken = session('zoho_access_token');
            
          
            $apiDomain =  'https://www.zohoapis.com';
            
         
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



   public function post_customers($requestData) {
    //check if the token has expired or not 
    if (now()->greaterThan(session('zoho_token_expires_at'))) {
            
        $authController = new ZohoAuthController();
        $refreshResult = $authController->refreshZohoAccessToken();
        if ($refreshResult !== true) {
            return $refreshResult;
        }
    }

    try { 
        $accessToken = session('zoho_access_token');
        $apidomain = env('ZOHO_API_DOMAIN');
        $url = "{$apidomain}/books/v3/contacts";

        $itemData = [
            'contact_name' => $requestData['name'] ?? null,
           

        ];

        $itemData = array_filter($itemData, function ($value) {
            return !is_null($value);
        });

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://www.zohoapis.com/books/v3/contacts",$itemData);

        if ($response->successful()) {
            return response()->json([
                'success' => true,
                'message' => 'Customers  created successfully',
                'data' => $response->json()
            ], 200);
        }
        
        return  response()->json([
                
            'success' => false,
            'message' => 'Failed to create item in Zoho Books',
            'error' => $response->json()

        ], $response->status());
            
            
            
           





      
    }

    catch(\Exception $e){
        Log::error('Zoho Books Item Creation Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the item',
                'error' => $e->getMessage()
            ], 500);


    }

    
        

   }
   


    public function getCustomers()
    {
        if (now()->greaterThan(session('zoho_token_expires_at'))) {
            
            $authController = new ZohoAuthController();
            $refreshResult = $authController->refreshZohoAccessToken();
            if ($refreshResult !== true) {
                return $refreshResult;
            }
        }

        try {
            $accessToken = session('zoho_access_token');
            
          
            $apiDomain =  'https://www.zohoapis.com';
            
         
            $apiUrl = "{$apiDomain}/books/v3/contacts";
            
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
                return response()->json(['error' => 'Failed to fetch contacts'], 500);
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Zoho API error', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch contacts'], 500);
        }
    }








    public function PostItem(Request $request){

        if (now()->greaterThan(session('zoho_token_expires_at'))) {
            
            $authController = new ZohoAuthController();
            $refreshResult = $authController->refreshZohoAccessToken();
            if ($refreshResult !== true) {
                return $refreshResult;
            }
        }

        try {
             $accessToken = session('zoho_access_token');
           
            // Prepare the item data
            $itemData = [
                'name' => $request->name,
                'rate' => $request->rate,
                'description' => $request->description,
                'sku' => $request->sku,
                'unit' => $request->unit ?? 'piece',
                'item_type' => $request->item_type ?? 'sales',
                'product_type' => $request->product_type ?? 'goods',
                'purchase_rate' => $request->purchase_rate,
                'tax_percentage' => $request->tax_percentage,
                'initial_stock' => $request->initial_stock,
                'reorder_level' => $request->reorder_level,
                'initial_stock_rate' => $request->initial_stock_rate,
                'account_id' => $request->account_id,
                'purchase_account_id' => $request->purchase_account_id,
                'inventory_account_id' => $request->inventory_account_id,
                'is_taxable' => $request->has('is_taxable'),
            ];

            
            $itemData = array_filter($itemData, function ($value) {
                return !is_null($value);
            });

           
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post("https://www.zohoapis.com/books/v3/items", $itemData);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item created successfully',
                    'data' => $response->json()
                ], 200);
            }

            return 
            
            
            
            response()->json([
                
                'success' => false,
                'message' => 'Failed to create item in Zoho Books',
                'error' => $response->json()
            ], $response->status());

        } catch (\Exception $e) {
            Log::error('Zoho Books Item Creation Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the item',
                'error' => $e->getMessage()
            ], 500);
        }
    
            
    }

    

}
