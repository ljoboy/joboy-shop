<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $orders = $user->orders()->with(['status', 'items'])->latest()->get();

        return view('customer.dashboard', compact('orders'));
    }
}
