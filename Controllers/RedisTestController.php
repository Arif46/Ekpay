<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redis;

class RedisTestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function redisTest()
    {
        Redis::set('name', 'Redis is working');
        $values = Redis::get('name');

        dd($values);
    }
}
