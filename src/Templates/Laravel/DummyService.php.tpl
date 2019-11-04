<?php
namespace App\Services;

use App\Models\Dummy;
use Illuminate\Http\Request;


class DummyService
{
    public function dummiesQuery(Request $request)
    {
        //$query->orderByRaw('FIELD(status, "not_approved", "payment_pending", "payment_received", "discarded")');

        $query = Dummy::query();
        return $query->paginate(50);
    }

    public function createDummy(Request $request)
    {
        dummyItem = Dummy::create($request->all());
        return dummyItem;
    }

    public function updateDummy(Request $request, Dummy dummyItem)
    {
        dummyItem->update($request->all());
        return dummyItem;
    }


}
