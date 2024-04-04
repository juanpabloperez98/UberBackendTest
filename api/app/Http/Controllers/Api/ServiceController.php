<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


// Clases propias
use App\Enums\StatusServiceEnum;
use App\Events\ServiceCancelled;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    // Metodo para solicitar servicio
    public function requestService(Request $request)
    {
        try {
            // Validar datos
            $validator = Validator::make($request->all(), [
                'latitude_origin' => 'required|numeric',
                'longitude_origin' => 'required|numeric',
                'latitude_destination' => 'required|numeric',
                'longitude_destination' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                // Manejar los errores de validación
                return response()->json([
                    'success' => false,
                    'data' => $validator->errors()
                ], 400);
            }

            $userId = auth()->user()->id;

            // Validar que el usuario no tenga un servicio en estos momentos en estado PENDING
            $existingService = Service::where('user_id', $userId)
                    ->where('status', StatusServiceEnum::PENDING)
                    ->first();

            if($existingService){
                return response()->json([
                    'success' => false,
                    'data' => 'Usted ya tiene un servicio en estado pendiente'
                ], 400);
            }

            // Empezar transaction
            DB::beginTransaction();
            // Crear un nuevo servicio
            $service = new Service([
                'user_id' => $userId, // Obtener el ID del usuario autenticado
                'latitude_origin' => $request->latitude_origin,
                'longitude_origin' => $request->longitude_origin,
                'latitude_destination' => $request->latitude_destination,
                'longitude_destination' => $request->longitude_destination,
                'status' =>  StatusServiceEnum::PENDING // Estado inicial del servicio
            ]);

            // Guardar el servicio en la base de datos
            $service->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => 'Servicio solicitado con exito!!'
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            // Manejo de error
            return response()->json([
                'success' => false,
                'data' => 'error ' . $th->getMessage() . ' | Line: ' . $th->getLine()
            ], 500);
        }
    }

    // Metodo para cancelar servicio
    public function rejectService(Request $request)
    {
        try {
            // Validar datos
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                // Manejar los errores de validación
                return response()->json([
                    'success' => false,
                    'data' => $validator->errors()
                ], 400);
            }

            $service = Service::find($request->service_id);

            if(!isset($service)){
                return response()->json([
                    'success' => false,
                    'data' => 'Servicio no encontrado'
                ], 400);
            }

            $userId = auth()->user()->id;

            if($service->user_id != $userId){
                return response()->json([
                    'success' => false,
                    'data' => 'Este usuario no puede cancelar el servicio'
                ], 400);
            }

            // Empezar transaction
            DB::beginTransaction();

            // Actualizo el estado
            $service->status = StatusServiceEnum::CANCELLED;
            $service->save();
            DB::commit();

            broadcast(new ServiceCancelled($service->id))->toOthers();

            return response()->json([
                'success' => true,
                'data' => 'Servicio cancelado con exito!!'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            // Manejo de error
            return response()->json([
                'success' => false,
                'data' => 'error ' . $th->getMessage() . ' | Line: ' . $th->getLine()
            ], 500);
        }

    }

    /* public function getServicesDrivers(Request $request)
    {
        try {




        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'data' => 'error ' . $th->getMessage() . ' | Line: ' . $th->getLine()
            ], 500);
        }
    } */

}
