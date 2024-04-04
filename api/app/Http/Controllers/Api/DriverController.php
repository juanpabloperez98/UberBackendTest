<?php

namespace App\Http\Controllers\Api;

// Eventos

use App\Enums\StatusServiceEnum;
use App\Events\DriverAcceptedService;
use App\Events\DriverLocationUpdated;
use App\Events\ServiceAcceptedByDriver;
// Modelos
use App\Models\Driver;

//Controladores
use App\Http\Controllers\Controller;
use App\Models\Service;
// Librerias
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;


class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    // Metodo para actualizar la ubicación del driver (se debe implementar websockets por el lado del client)
    public function updateLocationDriver(Request $request)
    {
        try {
            // Validar la solicitud del conductor
            $validator = Validator::make($request->all(), [
                'driver_id' => 'required|exists:drivers,id',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                // Manejar los errores de validación
                return response()->json([
                    'success' => false,
                    'data' => $validator->errors()
                ], 400);
            }

            $driver = Driver::find($request->driver_id);

            if ($driver === null) {
                return response()->json([
                    'success' => false,
                    'data' => 'No se encontró el conductor'
                ], 400);
            }

            // Empezar transaction
            DB::beginTransaction();

            // Actualizar las coordenadas del conductor en la base de datos
            $driver->latitude = $request->latitude;
            $driver->longitude = $request->longitude;
            $driver->save();

            // Emitir el evento de actualización de ubicación del conductor
            broadcast(new DriverLocationUpdated($driver->id, $driver->latitude, $driver->longitude))->toOthers();

            Log::channel('eventos')->info('Evento emitido: DriverLocationUpdated', [
                'driver_id' => $driver->id,
                'latitude' => $driver->latitude,
                'longitude' => $driver->longitude
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'latitude' => $driver->latitude,
                    'longitude' => $driver->longitude
                ]
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'data' => 'error ' . $th->getMessage() . ' | Line: ' . $th->getLine()
            ], 500);
        }
    }

    public function acceptService(Request $request)
    {
        try {
            // Validar datos
            $validator = Validator::make($request->all(), [
                'service_id' => 'required|numeric|exists:services,id',
                'driver_id' => 'required|numeric|exists:drivers,id',
            ]);

            if ($validator->fails()) {
                // Manejar los errores de validación
                return response()->json([
                    'success' => false,
                    'data' => $validator->errors()
                ], 400);
            }

            // Obtener los IDs de servicio y conductor directamente de la solicitud
            $serviceId = $request->service_id;
            $driverId = $request->driver_id;

            // Validar que el conductor no tenga un servicio en pendiente
            $service = Service::where('driver_id', $driverId)
                ->latest('created_at') // Ordena por fecha de creación en orden descendente
                ->first(); // Obtén el último registro que cumpla con las condiciones de la consulta

            // Validar que si hay un servicio relacionado a ese driver y que este en un estado diferente a completado o cancelado
            if (
                $service && (
                    $service->status != StatusServiceEnum::COMPLETED ||
                    $service->status != StatusServiceEnum::CANCELLED
                )
            ) {
                return response()->json([
                    'success' => false,
                    'data' => 'Este conductor tiene un servicio activo y aun no se ha completado'
                ], 400);
            }

            // Empezar transaction
            DB::beginTransaction();

            $service->driver_id = $driverId;
            $service->status = StatusServiceEnum::ACCEPTED;
            $service->save();

            // Emitir el evento de servicio aceptado por el conductor
            broadcast(new ServiceAcceptedByDriver($serviceId, $driverId))->toOthers();
            DB::commit();
            return response()->json([
                'success' => true,
                'data' => 'Servicio aceptado exitosamente'
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

    public function endService(Request $request, $serviceId)
    {
        try {

            $service = Service::find($serviceId);

            if (!isset($service)) {
                // Manejar los errores de validación
                return response()->json([
                    'success' => false,
                    'data' => 'Servicio no encontrado'
                ], 400);
            }

            if($service->status == StatusServiceEnum::COMPLETED){
                return response()->json([
                    'success' => false,
                    'data' => 'No se puede finalizar un servicio que ya se completo'
                ], 400);
            }

            if($service->status != StatusServiceEnum::CANCELLED){
                return response()->json([
                    'success' => false,
                    'data' => 'No se puede finalizar un servicio que ya se cancelo'
                ], 400);
            }

            $service->status = StatusServiceEnum::COMPLETED;
            $service->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'data' => 'Servicio cancelado con exito!!'
            ], 200);

        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'data' => 'error ' . $th->getMessage() . ' | Line: ' . $th->getLine()
            ], 500);
        }
    }
}
