<?php

namespace App\Http\Controllers;

use App\Http\Requests\IT\StoreHisLogRequest;
use App\Models\HisLog;
use App\Services\IT\HisLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HisLogController extends Controller
{
    public function __construct(private HisLogService $hisLogService) {}

    public function index(Request $request): View
    {
        return $this->hisLogService->index($request);
    }

    public function create(): View
    {
        return $this->hisLogService->createForm();
    }

    public function store(StoreHisLogRequest $request): RedirectResponse
    {
        return $this->hisLogService->store($request);
    }

    public function edit(HisLog $hisLog): View
    {
        return $this->hisLogService->editForm($hisLog);
    }

    public function update(StoreHisLogRequest $request, HisLog $hisLog): RedirectResponse
    {
        return $this->hisLogService->update($request, $hisLog);
    }

    public function dashboard(Request $request): View
    {
        return $this->hisLogService->dashboard($request);
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->hisLogService->export($request);
    }
}
