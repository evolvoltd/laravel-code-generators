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

        return ['items' => $query->paginate(50)];
    }

    public function show(tpl $item){
        $this->authorize('view', $item);
        return response()->json(["item"=>$item]);
    }


    public function store(StoreOrUpdate $request)
    {

        $item = tpl::create($request->all());
        return ["item"=>$item];

    }


    public function update(StoreOrUpdate $request, tpl $item)
    {
        $this->authorize('edit', $item);

        $item->update($request->all());
        return ["item"=>$item];

    }


    public function destroy(tpl $item)
    {
        $this->authorize('edit', $item);
        $item->delete();
        return [];
    }

    public function find($term)
    {
        return ['items'=>(strlen($term)>2)?tpl::where('title', 'LIKE', '%$term%')->limit(20)->get():[]];
    }


}
