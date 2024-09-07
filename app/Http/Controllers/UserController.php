<?php

namespace App\Http\Controllers;



use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class UserController extends Controller
{
    public function index(){
            return view('index');
    

}
}
