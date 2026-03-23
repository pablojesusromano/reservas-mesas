<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMesaRequest;
use App\Http\Requests\UpdateMesaRequest;
use App\Models\Mesa;

class MesaController extends Controller {
    
    public function index()
    {
        $mesas = Mesa::orderBy('ubicacion')->orderBy('numero')->get();
        return view('mesas.index', [
            'mesas' => $mesas,
        ]);
    }

    public function create() 
    {
        return view('mesas.create', [
            'ubicaciones' => Mesa::UBICACIONES
        ]);
    }

    public function store(StoreMesaRequest $request)
    {
        try {
            $data = $request->validated();
            $data['numero'] = Mesa::getNextNumero($data['ubicacion']);

            Mesa::create($data);
            return redirect()->route('mesas.index')->with('success', 'Mesa creada exitosamente.');
        } catch (\RuntimeException  $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function edit(Mesa $mesa)
    {
        return view('mesas.edit', [
            'mesa' => $mesa,
            'ubicaciones' => Mesa::UBICACIONES
        ]);
    }

    public function update(UpdateMesaRequest $request, Mesa $mesa)
    {
        try {
            $data = $request->validated();
            if($mesa->ubicacion !== $data['ubicacion']) {
                $data['numero'] = Mesa::getNextNumero($data['ubicacion']);
            }

            $mesa->update($data);
            return redirect()->route('mesas.index')->with('success', 'Mesa actualizada exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Mesa $mesa)
    {
        try {
            $mesa->delete();
            return redirect()->route('mesas.index')->with('success', 'Mesa eliminada exitosamente.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}