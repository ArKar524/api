<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function authorize($ability, $model = null)
    {
        if (!auth()->user()->can($ability, $model)) {
            abort(403);
        }
    }
}
