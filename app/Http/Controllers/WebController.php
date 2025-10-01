<?php
namespace App\Http\Controllers;

use App\Http\Controllers\HelperController;

class WebController extends Controller
{
    private $helper;

    public function __construct()
    {
        $this->helper = new HelperController();
    }

    public function Index()
    {

        return view('index');
    }

}
