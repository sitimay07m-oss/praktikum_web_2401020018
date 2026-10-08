<!DOCTYPE html>
<html>
<head>
    <title>Data Mahasiswa</title>
</head>
<body>

    <h1>Data Mahasiswa</h1>

    @if(isset($mahasiswa) && isset($mahasiswa['nim']))

        <table border="1" cellpadding="8">
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Usia</th>
                <th>Program Studi</th>
            </tr>

            <tr>
                <td>{{ $mahasiswa['nim'] }}</td>
                <td>{{ $mahasiswa['nama'] }}</td>
                <td>{{ $mahasiswa['email'] }}</td>
                <td>{{ $mahasiswa['usia'] }}</td>
                <td>{{ $mahasiswa['nama_prodi'] }}</td>
            </tr>
        </table>

    @elseif(isset($mahasiswa))

        <table border="1" cellpadding="8">
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Usia</th>
                <th>Program Studi</th>
            </tr>

           @foreach($mahasiswa as $mhs)
    <tr>
        <td>{{ $mhs['nim'] }}</td>
        <td>{{ $mhs['nama'] }}</td>
        <td>{{ $mhs['email'] }}</td>
        <td>{{ $mhs['usia'] }}</td>
        <td>{{ $mhs['nama_prodi'] }}</td>
    </tr>
@endforeach
        </table>

    @endif

</body>
</html>