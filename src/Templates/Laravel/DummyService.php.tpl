<?php
namespace App\Services;

use App\Models\Dummy;
use Illuminate\Foundation\Http\FormRequest;


class DummiesService
{
    public function listDummies(FormRequest $request)
    {
        $query = Dummy::query();//with([]);

        /*if($request->id)
        $query->where('id',$request->id);

        if($request->search)
        $query->where('name', 'LIKE', '%'.$request->search.'%');

        if($request->statuses)
        $query->whereIn('status', $request->statuses);

        $query->orderByRaw('FIELD(status, "status_1", "status_2", "status_3")');*/

        return $query->paginate(50);
    }

    public function createDummy(FormRequest $request)
    {
        dummyItem = Dummy::create($request->all());
        return dummyItem->fresh();//->load([]);
    }

    public function updateDummy(FormRequest $request, Dummy dummyItem)
    {
        dummyItem->update($request->all());
        return dummyItem;//->load([]);
    }

    public function find(FormRequest $request)
    {
        return ["data" => Dummy::where(function($q) use ($request) {
            foreach (Dummy::SEARCHABLE_ATTRIBUTES as $searchableAttribute)
            $q->orWhere($searchableAttribute, 'LIKE', '%' . $request->search . '%');
        })->limit(20)->get()];
    }
}
