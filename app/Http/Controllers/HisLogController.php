<?php

namespace App\Http\Controllers;

use App\Http\Requests\IT\ImportHisLogRequest;
use App\Http\Requests\IT\StoreHisLogRequest;
use App\Services\IT\HisLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HisLogController extends Controller
{
    public function __construct(private HisLogService $hisLogService) {}

    public function create(): View
    {
        return $this->hisLogService->createForm();
    }

    public function store(StoreHisLogRequest $request): RedirectResponse
    {
        return $this->hisLogService->store($request);
    }

    public function dashboard(Request $request): View
    {
        return $this->hisLogService->dashboard($request);
    }

    public function import(ImportHisLogRequest $request): RedirectResponse
    {
        return $this->hisLogService->import($request);
    }
}
