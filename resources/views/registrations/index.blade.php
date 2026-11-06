
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hospital Management - Pendaftaran</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 35px;
            background: #f4f6f9;
            color: #202938;
            font-family: Arial, sans-serif;
        }
        .container { max-width: 1100px; margin: auto; }
        h1 { margin-bottom: 8px; }
        .subtitle { color: #687386; margin-bottom: 25px; }
        .panel {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px #00000008;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }
        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 7px;
        }
        input, select, textarea {
            width: 100%;
            padding: 11px;
            border: 1px solid #d8dee8;
            border-radius: 7px;
            font-size: 14px;
            background: white;
        }
        textarea { resize: vertical; }
        .field { margin-bottom: 16px; }
        button {
            background: #202938;
            color: white;
            border: 0;
            border-radius: 7px;
            padding: 12px 22px;
            cursor: pointer;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            text-align: left;
            padding: 13px 10px;
            border-bottom: 1px solid #e9edf2;
            font-size: 14px;
        }
        th { color: #687386; }
        .alert {
            padding: 13px;
            border-radius: 7px;
            margin-bottom: 15px;
        }
        .success { background: #e6f7ed; color: #176b39; }
        .error { background: #fdeaea; color: #a32929; }
        .conditional { display: none; }
        @media(max-width: 650px) {
            body { padding: 15px; }
            .grid { grid-template-columns: 1fr; }
            .panel { padding: 17px; overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="container">

    <h1>Hospital Management System</h1>
    <p class="subtitle">Patient Registration Management</p>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert error">
            <strong>Terjadi kesalahan:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <h2>Patient Registration</h2>

        <form method="POST" action="{{ route('registrations.store') }}">
            @csrf

            <div class="grid">
                <div class="field">
                    <label>Nama Pasien</label>
                    <input name="patient_name" value="{{ old('patient_name') }}" required>
                </div>

                <div class="field">
                    <label>Jenis Kelamin</label>
                    <select name="gender" required>
                        <option value="">Pilih</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                </div>

                <div class="field">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="birth_date">
                </div>

                <div class="field">
                    <label>Nomor Telepon</label>
                    <input name="phone">
                </div>

                <div class="field">
                    <label>Dokter</label>
                    <select name="doctor_id" required>
                        <option value="">Pilih Dokter</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}">
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label>Tanggal Pendaftaran</label>
                    <input type="date" name="registration_date"
                        value="{{ date('Y-m-d') }}" required>
                </div>

                <div class="field">
                    <label>Jenis Layanan</label>
                    <select name="service_type" id="service_type" required>
                        <option value="">Pilih Layanan</option>
                        <option value="outpatient">Rawat Jalan</option>
                        <option value="inpatient">Rawat Inap</option>
                        <option value="surgery">Operasi</option>
                    </select>
                </div>
            </div>

            <div class="field">
                <label>Alamat</label>
                <textarea name="address" rows="2"></textarea>
            </div>

            <div class="field conditional" id="inpatient_field">
                <label>Kamar Rawat Inap</label>
                <select name="inpatient_room_id">
                    <option value="">Pilih Kamar</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">
                            {{ $room->room_number }} - Kelas {{ $room->room_class }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="surgery_fields" class="conditional">
                <div class="grid">
                    <div class="field">
                        <label>Ruang Operasi</label>
                        <select name="operating_room_id">
                            <option value="">Pilih Ruang Operasi</option>
                            @foreach($operatingRooms as $room)
                                <option value="{{ $room->id }}">
                                    {{ $room->room_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Jadwal Operasi</label>
                        <input type="datetime-local" name="scheduled_at">
                    </div>
                </div>
            </div>

            <button type="submit">Simpan Pendaftaran</button>
        </form>
    </div>

    <div class="panel">
        <h2>Registration Records</h2>

        <table>
            <thead>
                <tr>
                    <th>No. Registrasi</th>
                    <th>Pasien</th>
                    <th>Dokter</th>
                    <th>Tanggal</th>
                    <th>Layanan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registrations as $registration)
                    <tr>
                        <td>{{ $registration->registration_number }}</td>
                        <td>{{ $registration->patient_name }}</td>
                        <td>{{ $registration->doctor_name }}</td>
                        <td>{{ $registration->registration_date }}</td>
                        <td>{{ ucfirst($registration->service_type) }}</td>
                        <td>{{ ucfirst($registration->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada data pendaftaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    const serviceType = document.getElementById('service_type');
    const inpatientField = document.getElementById('inpatient_field');
    const surgeryFields = document.getElementById('surgery_fields');

    function updateFields() {
        inpatientField.style.display =
            serviceType.value === 'inpatient' ? 'block' : 'none';

        surgeryFields.style.display =
            serviceType.value === 'surgery' ? 'block' : 'none';
    }

    serviceType.addEventListener('change', updateFields);
    updateFields();
</script>
</body>
</html>
