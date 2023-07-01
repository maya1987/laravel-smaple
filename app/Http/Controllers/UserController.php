<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\User;

class PostController extends Controller
{
    public function get(){
        $users =User::get();
    }

    public function Store(Request $request){

    }
}