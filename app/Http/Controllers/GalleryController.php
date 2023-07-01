<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\User;

class GalleryController extends Controller
{
    public function index(){
        echo "For Gallery";
    }

}