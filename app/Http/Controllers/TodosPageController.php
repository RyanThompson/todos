<?php

namespace App\Http\Controllers;

class TodosPageController extends Controller
{
    public function __invoke()
    {
        return view('todos');
    }
}
