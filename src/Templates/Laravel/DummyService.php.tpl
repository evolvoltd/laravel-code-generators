<?php
namespace App\Services;

use App\Models\Dummy;
use Illuminate\Http\Request;


class DummiesService
{
    public function listDummies(Request $request)
    {
        //$query->orderByRaw('FIELD(status, "not_approved", "payment_pending", "payment_received", "discarded")');

        $query = Dummy::with([]);

        /*if($request->filled('id'))
            $query->where('id',$request->input('id'));

        if(strlen($request->input('search'))>2)
            $query->where('name', 'LIKE', '%'.$request->input('search').'%');*/

        return $query->paginate(50);
    }

    public function createDummy(Request $request)
    {
        dummyItem = Dummy::create($request->all());
        return dummyItem->fresh()->load([]);
    }

    public function updateDummy(Request $request, Dummy dummyItem)
    {
        dummyItem->update($request->all());
        return dummyItem->load([]);
    }

    public function find($search)
    {
        return (strlen($search) > 2) ? ["data" => Dummy::with([])->where('name', 'LIKE', '%' . $search . '%')->limit(20)->get()] : [];
    }
}
