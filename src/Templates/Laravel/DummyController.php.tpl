<?php

namespace App\Http\Controllers;

use App\Models\Dummy;
use Illuminate\Http\Request;
use App\Services\DummyService;
use App\Http\Requests\Dummy\StoreOrUpdate;

class DummyController extends Controller
{
    private $dummyService;

    public function __construct(DummyService dummyItemService)
    {
        $this->dummyService = dummyItemService;
    }

    public function index(Request $request)
    {
        $dummiesQuery = $this->dummyService->dummiesQuery($request);
        return  $dummiesQuery;
    }

    public function show(Dummy dummyItem)
    {
        //$this->authorize('view', dummyItem);
        return dummyItem;
    }

    public function store(StoreOrUpdate $request)
    {
        dummyItem = $this->dummyService->createDummy($request);
        return dummyItem;
    }

    public function update(StoreOrUpdate $request, Dummy dummyItem)
    {
        //$this->authorize('update', dummyItem);

        dummyItem = $this->dummyService->updateDummy($request,dummyItem);
        return dummyItem;
    }

    public function destroy(Dummy dummyItem)
    {
        //$this->authorize('delete', dummyItem);
        dummyItem->delete();
        return [];
    }

    public function find($search)
    {
        $dummies = $this->dummyService->find($search);
        return  $dummies;
    }

}
