<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    private $users;

    public function __construct(User $users)
    {
        $this->middleware('auth');
        $this->users = $users;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home',['users' => $this->users->all()]);
    }

    public function videochat()
    {
        return 'Video Chat';
    }

}
