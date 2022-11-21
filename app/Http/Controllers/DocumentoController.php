<?php

namespace App\Http\Controllers;

use App\Models\documento;
use App\Models\oficina;
use App\Models\Proceso;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoController extends Controller
{


    public function documentos(){
        return documento::all();
    }

    public function add_documento(Request $request){
        $request->validate([
            'nombre'=>'required',
            'remitente'=>'required',
            'dni'=>'required|numeric',
            'archivo'=>'required',
            'destino'=>'required',
            'tipo'=>'required',
        ]);
        try{
            $direccion='documentos';
            $url=Storage::url($request->file('archivo')->store($direccion,'public_file'));
            
            $doc=documento::create([
                'documento'=>$request->nombre,
                'fecha'=>Carbon::now(),
                'remitente'=>$request->remitente,
                'dni'=>$request->dni,
                'destino'=>$request->destino,
                'path'=>$url,
                'tipo'=>$request->tipo,
               // 'oficina_id'=>1,
            ]);
            Proceso::create([
                'recepcion'=>Carbon::now(),
                'documento_id'=>$doc->id,
                'oficina_input'=>1,         
            ]);

            return true;
        }catch(Exception $e){
            //return $e;
            return response()->json(['message'=>'Error al subir documento'],405);
        }
   
    }

    public function dato_doc($id){
        $d=documento::findOrFail($id);
        try{
            return [
                'id'=>$d->id,
                'documento'=>$d->documento,
                'fecha'=>$d->fecha,
                'path'=>$d->path,
                'remitente'=>$d->remitente,
                'dni'=>$d->dni,
                'destino'=>$d->destino,
                'tipo'=>$d->tipo,
                'proceso'=>Proceso::where('documento_id',$d->id)->get()->map(function($p){
                    $oficina_i=oficina::where('id',$p->oficina_input)->first();
                    $oficina_o=oficina::where('id',$p->oficina_ouput)->first();
                    return[
                        'id'=>$p->id,
                        'recepcion'=>$p->recepcion,
                        'derivar'=>$p->derivar,
                        'oficina_input'=>$p->oficina_input,
                        'oficina_ouput'=>$p->oficina_ouput,
                        'nom_input'=>$oficina_i?$oficina_i->nombre:null,
                        'nom_ouput'=>$oficina_o?$oficina_o->nombre:null,
                    ];
                }),
            ];
        }catch(Exception $e){
            return $e;
            return response()->json(['message'=>'Error al obtener datos'],405);
        }
    }

    public function derrivar_doc(Request $request){
        $request->validate([
            'oficina'=>'required',
            'documento'=>'required',
        ]);

        return $request;
    }
}


/*
//codigo para borrar
$path = public_path().$file->path;
unlink($path);
*/