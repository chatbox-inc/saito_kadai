<?php

namespace App\Http\Controllers\Shukkin;

use App\Repository\ShukkinRepositoryInterface;
use Illuminate\Http\Request;

class ShukkinController extends Controller
{
    protected $notification;

    public function __construct(ShukkinRepositoryInterface $notification) {
        $this->notification = $notification;
    }

    public function handle() {

    }

}
