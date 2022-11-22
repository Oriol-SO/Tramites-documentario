<?php

namespace App\Http\Controllers;

use App\Models\oficina;
use App\Models\role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    
    public function getusers(){
        return User::all()->map(function($u){
            $rol=role::where('id',$u->rol_id)->first();
            $oficina=oficina::where('id',$u->oficina_id)->first();
            return[
                'id'=>$u->id,
                'nombre'=>$u->name,
                'email'=>$u->email,
                'rol_id'=>$u->rol,
                'dni'=>$u->dni,
                'rol'=>$rol?$rol->nombre:null,
                'oficina'=>$oficina?$oficina->nombre:null,
            ];
        });
    }

    public function roles(){
        return role::all()->map(function($r){
            return[
                'id'=>$r->id,
                'nombre'=>$r->nombre,
            ];
        });
    }

    public function oficinas(){
        return oficina::all()->map(function($o){
            return[
                'id'=>$o->id,
                'nombre'=>$o->nombre,
            ];
        });
    }

    protected function add_user(Request $request){
        $request->validate([
            'nombre'=>'required',
            'correo'=>'required|email',
            'dni'=>'required|numeric',
            'rol'=>'required',
            'oficina'=>'required'
        ]);
        try{
            $oficina=$request->oficina['id'];
            if($request->rol['id']==1){
                $oficina=9;
            }
            if($request->rol['id']==2){
                $oficina=1;
            }
            User::create([
                'name'=>$request->nombre,
                'email'=>$request->correo,
                'dni'=>$request->dni,
                'email_verified_at' =>now(),
                'password' => Hash::make($request->dni),
                'rol_id'=>$request->rol['id'],
                'oficina_id'=>$oficina
            ]);
        }catch(Exception $e){
            //return $e;
            
            return response()->json(['message'=>'Error al agregar usuario'],405);
        }
    }

    protected function editar_user(Request $request ,$id){
        $request->validate([
            'nombre'=>'required',
            'correo'=>'required|email',
            'dni'=>'required|numeric',
            
        ]);
       $user= User::findOrFail($id);
        try{
            $correos=User::where('email',$request->correo)->count();
            if($request->correo!=$user->email){
                $correos=User::where('email',$request->correo)->first();
                if($correos){
                    return response()->json(['message'=>'El correo ya fue tomado'],405);
                }
                User::where('id',$id)->update([
                    'name'=>$request->nombre,
                    'email'=>$request->correo,
                    'password' => Hash::make($request->dni),
                    'dni'=>$request->dni,
                    'email_verified_at' =>now(),
                ]);
            }else{
                User::where('id',$id)->update([
                    'name'=>$request->nombre,
                    'dni'=>$request->dni,
                    'email_verified_at' =>now(),
                    'password' => Hash::make($request->dni),
                ]);
            }
            
        }catch(Exception $e){
            //return $e;
            return response()->json(['message'=>'Error al editar usuario'],405);
        }
        
        return $request;
    }
}
