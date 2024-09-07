<?php

namespace App\Http\Controllers;



use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class UserController extends Controller
{
    public function index(){
            return view('index');
    

}

   

    public function store(Request $request){
                
        $request->validate([
            'admin_f_name' => 'required|string|max:255',
            'admin_l_name' => 'required|string|max:255',
            'admin_email' => 'required|string|email|max:255|unique:admins,email',
            'phone_code' => 'required|string|max:5',
            'admin_phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
        ]);

        DB::table('admins')->insertOrIgnore([
            'f_name' => $request->input('admin_f_name'),
            'l_name' => $request->input('admin_l_name'),
            'email' => $request->input('admin_email'),
            'password' => bcrypt($request->input('password')),
            'phone' => $request->input('phone_code') . $request->input('admin_phone'),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Admin added successfully!');


    }


}
