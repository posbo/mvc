<?php namespace Assets\OrnoTest;

class Controller
{
    public function before()
    {
        return true;
    }

    public function index()
    {
        return 'Hello World';
    }

    public function after()
    {
        return true;
    }
}
