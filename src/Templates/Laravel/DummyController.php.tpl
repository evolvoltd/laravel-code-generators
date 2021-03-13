<?php

namespace App\Http\Controllers;

use App\Models\Dummy;
use Illuminate\Http\Request;
use App\Services\ServiceName;
use App\Http\Requests\Dummy\StoreOrUpdate;
use App\Http\Requests\Dummy\Filter;

class DummyController extends Controller
{
    private $dummyService;

    public function __construct(ServiceName dummyService)
    {
        $this->dummyService = dummyService;
    }

    public function index(Filter $request)
    {
        return $this->dummyService->listDummies($request);
    }

    public function show(Dummy dummyItem)
    {
        //$this->authorize('view', dummyItem);
        return dummyItem;
    }

    public function store(StoreOrUpdate $request)
    {
        return $this->dummyService->createDummy($request);
    }

    public function update(StoreOrUpdate $request, Dummy dummyItem)
    {
        //$this->authorize('update', dummyItem);

        return $this->dummyService->updateDummy($request,dummyItem);
    }

    public function destroy(Dummy dummyItem)
    {
        //$this->authorize('delete', dummyItem);
        dummyItem->delete();
        return [];
    }

    public function find($search)
    {
        return $this->dummyService->find($search);
    }

}
