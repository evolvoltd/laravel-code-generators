<?php

namespace App\Http\Controllers;

use App\tpl;
use Illuminate\Http\Request;

use App\Http\Requests\Dummy\StoreOrUpdate;

class DummyController extends Controller
{


    public function index()
    {
        $query = tpl::query();
        //$query->orderByRaw('FIELD(status, "not_approved", "payment_pending", "payment_received", "discarded")');

        return $query->paginate(50);
    }

    public function show(tpl $item){
        //$this->authorize('view', $item);
        return $item;
    }


    public function store(StoreOrUpdate $request)
    {
        $item = tpl::create($request->all());
        return $item;
    }


    public function update(StoreOrUpdate $request, tpl $item)
    {
        //$this->authorize('update', $item);

        $item->update($request->all());
        return $item;
    }


    public function destroy(tpl $item)
    {
        //$this->authorize('delete', $item);
        $item->delete();
        return [];
    }

    public function find($search)
    {
        return (strlen($search)>2)?tpl::where('title', 'LIKE', '%' . $search . '%')->limit(20)->get():[];
    }


}
