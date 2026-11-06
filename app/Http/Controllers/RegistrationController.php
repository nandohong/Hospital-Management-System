<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function index()
    {
        $doctors = DB::table('doctors')->orderBy('name')->get();

        $rooms = DB::table('inpatient_rooms')
            ->where('status', 'available')
            ->orderBy('room_class')
            ->orderBy('room_number')
            ->get();

        $operatingRooms = DB::table('operating_rooms')
            ->orderBy('room_name')
            ->get();

        $registrations = DB::table('registrations')
            ->join('patients', 'registrations.patient_id', '=', 'patients.id')
            ->join('doctors', 'registrations.doctor_id', '=', 'doctors.id')
            ->select(
                'registrations.*',
                'patients.name as patient_name',
                'doctors.name as doctor_name'
            )
            ->orderByDesc('registrations.id')
            ->get();

        return view('registrations.index', compact(
            'doctors',
            'rooms',
            'operatingRooms',
            'registrations'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'birth_date' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'doctor_id' => 'required|exists:doctors,id',
            'registration_date' => 'required|date',
            'service_type' => 'required|in:outpatient,inpatient,surgery',
            'inpatient_room_id' => 'nullable|exists:inpatient_rooms,id',
            'operating_room_id' => 'nullable|exists:operating_rooms,id',
            'scheduled_at' => 'nullable|date',
        ]);

        if (
            $validated['service_type'] === 'inpatient'
            && empty($validated['inpatient_room_id'])
        ) {
            throw ValidationException::withMessages([
                'inpatient_room_id' => 'Pilih kamar rawat inap.',
            ]);
        }

        if (
            $validated['service_type'] === 'surgery'
            && (empty($validated['operating_room_id']) || empty($validated['scheduled_at']))
        ) {
            throw ValidationException::withMessages([
                'operating_room_id' => 'Pilih ruang operasi dan jadwal operasi.',
            ]);
        }

        DB::transaction(function () use ($validated) {

            // Maksimal 50 pasien per dokter per hari
            $doctor = DB::table('doctors')
                ->where('id', $validated['doctor_id'])
                ->lockForUpdate()
                ->first();

            $dailyPatients = DB::table('registrations')
                ->where('doctor_id', $validated['doctor_id'])
                ->whereDate('registration_date', $validated['registration_date'])
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($dailyPatients >= 50) {
                throw ValidationException::withMessages([
                    'doctor_id' => 'Dokter sudah mencapai batas 50 pasien pada tanggal tersebut.',
                ]);
            }

            // Simpan data pasien
            $patientId = DB::table('patients')->insertGetId([
                'name' => $validated['patient_name'],
                'gender' => $validated['gender'],
                'birth_date' => $validated['birth_date'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Data master pendaftaran
            $registrationId = DB::table('registrations')->insertGetId([
                'registration_number' => 'REG-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4)),
                'patient_id' => $patientId,
                'doctor_id' => $validated['doctor_id'],
                'registration_date' => $validated['registration_date'],
                'service_type' => $validated['service_type'],
                'status' => 'registered',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $detail = [
                'registration_id' => $registrationId,
                'service_name' => match ($validated['service_type']) {
                    'outpatient' => 'Pemeriksaan Rawat Jalan',
                    'inpatient' => 'Perawatan Rawat Inap',
                    'surgery' => 'Tindakan Operasi',
                },
                'inpatient_room_id' => null,
                'operating_room_id' => null,
                'scheduled_at' => null,
                'notes' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Periksa dan alokasikan kamar rawat inap
            if ($validated['service_type'] === 'inpatient') {
                $room = DB::table('inpatient_rooms')
                    ->where('id', $validated['inpatient_room_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$room || $room->status !== 'available') {
                    throw ValidationException::withMessages([
                        'inpatient_room_id' => 'Kamar tidak tersedia. Silakan pilih kamar lain.',
                    ]);
                }

                DB::table('inpatient_rooms')
                    ->where('id', $room->id)
                    ->update([
                        'status' => 'occupied',
                        'updated_at' => now(),
                    ]);

                $detail['inpatient_room_id'] = $room->id;
            }

            // Periksa jadwal ruang operasi
            if ($validated['service_type'] === 'surgery') {
                $roomId = $validated['operating_room_id'];
                $schedule = $validated['scheduled_at'];

                $room = DB::table('operating_rooms')
                    ->where('id', $roomId)
                    ->lockForUpdate()
                    ->first();

                $conflict = DB::table('registration_details')
                    ->join('registrations', 'registration_details.registration_id', '=', 'registrations.id')
                    ->where('registration_details.operating_room_id', $roomId)
                    ->where('registration_details.scheduled_at', $schedule)
                    ->where('registrations.status', '!=', 'cancelled')
                    ->exists();

                if (!$room || $conflict) {
                    throw ValidationException::withMessages([
                        'operating_room_id' => 'Ruang operasi sudah digunakan pada jadwal tersebut.',
                    ]);
                }

                $detail['operating_room_id'] = $roomId;
                $detail['scheduled_at'] = $schedule;
            }

            DB::table('registration_details')->insert($detail);
        });

        return redirect()
            ->route('registrations.index')
            ->with('success', 'Pendaftaran pasien berhasil disimpan.');
    }
}
