<?php

namespace App\Http\Controllers\Buyer;

use App\Buyer;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class BuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $compradores = Buyer::has('transactions')->get();

        //return response()->json(['data' => $compradores], 200);
        return $this->showAll($compradores);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //public function show($id)
    public function show(Buyer $buyer)
    {
        //$compradores = Buyer::has('transactions')->findOrFail($id);

        //return response()->json(['data' => $compradores], 200);
        //return $this->showOne($compradores);
        return $this->showOne($buyer);

    }

}
