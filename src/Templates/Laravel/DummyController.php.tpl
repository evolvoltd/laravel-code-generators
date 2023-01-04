<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dummy\Filter;
use App\Http\Requests\Dummy\StoreOrUpdate;
use App\Http\Requests\Find;
use App\Models\Dummy;
use App\Services\ServiceName;
use Illuminate\Pagination\LengthAwarePaginator;

class DummyController extends Controller
{
    private $dummyService;

    public function __construct(ServiceName $dummyService)
    {
        $this->dummyService = $dummyService;
    }

    public function index(Filter $request) : LengthAwarePaginator
    {
        return $this->dummyService->listDummies($request);
    }

    public function show(Dummy dummyItem) : Dummy
    {
        //$this->authorize('view', dummyItem);
        return dummyItem;
    }

    public function store(StoreOrUpdate $request) : Dummy
    {
        return $this->dummyService->createDummy($request);
    }

    public function update(StoreOrUpdate $request, Dummy dummyItem) : Dummy
    {
        //$this->authorize('update', dummyItem);

        return $this->dummyService->updateDummy($request,dummyItem);
    }

    public function destroy(Dummy dummyItem) : void
    {
        //$this->authorize('delete', dummyItem);
        dummyItem->delete();

    }

    public function find(Find $request) : array
    {
        return $this->dummyService->find($request);
    }

}
