<?php

namespace App\Http\Controllers;

use Auth;

class ChatController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {

        $user = Auth::user();
        $user->token = csrf_token();
        $user->save();

        return view('index');
    }
 }
