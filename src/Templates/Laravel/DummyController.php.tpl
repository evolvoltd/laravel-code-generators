<?php

namespace App\Http\Controllers;

use App\Models\Dummy;
use Illuminate\Http\Request;

use App\Http\Requests\Dummy\StoreOrUpdate;

class DummyController extends Controller
{


    public function index(Request $request)
    {
        $query = Dummy::query();
        //$query->orderByRaw('FIELD(status, "not_approved", "payment_pending", "payment_received", "discarded")');

        return $query->paginate(50);
    }

    public function show(Dummy $item){
        //$this->authorize('view', $item);
        return $item;
    }


    public function store(StoreOrUpdate $request)
    {
        $item = Dummy::create($request->all());
        return $item;
    }


    public function update(StoreOrUpdate $request, Dummy $item)
    {
        //$this->authorize('update', $item);

        $item->update($request->all());
        return $item;
    }


    public function destroy(Dummy $item)
    {
        //$this->authorize('delete', $item);
        $item->delete();
        return [];
    }

    public function find($search)
    {
        return (strlen($search)>2)?Dummy::where('title', 'LIKE', '%' . $search . '%')->limit(20)->get():[];
    }


}
