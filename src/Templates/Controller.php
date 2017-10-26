<?php

namespace App\Http\Controllers;

use App\Dummy;
use Illuminate\Http\Request;

class DummyController extends Controller
{


    public function index()
    {

        $query = Dummy::query();

        //$query->orderByRaw('FIELD(status, "not_approved", "payment_pending", "payment_received", "discarded")');

        return response()->json(['items' => $query->paginate(50)]);
    }

    public function show($id){

        $item = Dummy::findOrFail($id);

        $this->authorize('view', $item);

        return response()->json(["item"=>$item]);
    }


    public function store(Request $request)
    {
        $this->authorize('create', Dummy::class);

        $validation = Dummy::validation($request->all());

        if ($validation->fails()) {
            return response()->json(["status" => "errors", "messages" => $validation->messages()],400);

        } else {
            $item = Dummy::create($request->all());
            return response()->json(["item"=>$item]);
        }
    }


    public function update(Request $request, $id)
    {
        $item = Dummy::findOrFail($id);

        $this->authorize('update', $item);

        $validation = Dummy::validation($request->all());

        if ($validation->fails()) {
            return response()->json(["status" => "errors", "messages" => $validation->messages()],400);

        } else {
            $item->update($request->all());
            return response()->json(["item"=>$item]);
        }
    }


    public function destroy($id)
    {
        $item = Dummy::findOrFail($id);

        $this->authorize('delete', $item);

        $item->delete();
        return response()->json([]);
    }

    public function find($term)
    {
        return response()->json(['items'=>(strlen($term)>2)?Dummy::where('title', 'LIKE', '%$term%')->limit(20)->get():[]]);
    }


}
