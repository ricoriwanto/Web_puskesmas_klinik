<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

include 'config.php';
include 'sidebar.php';

// Proses CRUD
if (isset($_POST['tambah'])) {
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $tmp_lahir = $_POST['tmp_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $kelurahan_id = $_POST['kelurahan_id'];
    
    $stmt = $conn->prepare("INSERT INTO pasien (kode, nama, tmp_lahir, tgl_lahir, gender, email, alamat, kelurahan_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $kode, $nama, $tmp_lahir, $tgl_lahir, $gender, $email, $alamat, $kelurahan_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $tmp_lahir = $_POST['tmp_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $kelurahan_id = $_POST['kelurahan_id'];
    
    $stmt = $conn->prepare("UPDATE pasien SET kode=?, nama=?, tmp_lahir=?, tgl_lahir=?, gender=?, email=?, alamat=?, kelurahan_id=? WHERE id=?");
    $stmt->bind_param("sssssssii", $kode, $nama, $tmp_lahir, $tgl_lahir, $gender, $email, $alamat, $kelurahan_id, $id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM pasien WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Ambil data pasien
$pasien = $conn->query("SELECT pasien.*, kelurahan.nama as nama_kelurahan FROM pasien LEFT JOIN kelurahan ON pasien.kelurahan_id = kelurahan.id");
$kelurahan = $conn->query("SELECT * FROM kelurahan");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pasien - Sistem Klinik</title>
    <style>
        .main-content {
            margin-left: 270px;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 8px 15px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover {
            color: black;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <h1>Data Pasien</h1>
        
        <div class="card">
            <button onclick="document.getElementById('tambahModal').style.display='block'">Tambah Pasien</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Tempat/Tgl Lahir</th>
                        <th>Gender</th>
                        <th>Email</th>
                        <th>Alamat</th>
                        <th>Kelurahan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pasien->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['kode']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['tmp_lahir'] . ', ' . date('d-m-Y', strtotime($row['tgl_lahir'])); ?></td>
                        <td><?php echo $row['gender'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                        <td><?php echo $row['email']; ?></td>
                        <td><?php echo $row['alamat']; ?></td>
                        <td><?php echo $row['nama_kelurahan'] ?? '-'; ?></td>
                        <td>
                            <button onclick="editPasien(<?php echo $row['id']; ?>, '<?php echo $row['kode']; ?>', '<?php echo $row['nama']; ?>', '<?php echo $row['tmp_lahir']; ?>', '<?php echo $row['tgl_lahir']; ?>', '<?php echo $row['gender']; ?>', '<?php echo $row['email']; ?>', '<?php echo $row['alamat']; ?>', <?php echo $row['kelurahan_id']; ?>)">Edit</button>
                            <a href="pasien.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')"><button>Hapus</button></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah -->
    <div id="tambahModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('tambahModal').style.display='none'">&times;</span>
            <h2>Tambah Pasien</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="kode">Kode Pasien</label>
                    <input type="text" id="kode" name="kode" required>
                </div>
                <div class="form-group">
                    <label for="nama">Nama Pasien</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="tmp_lahir">Tempat Lahir</label>
                    <input type="text" id="tmp_lahir" name="tmp_lahir" required>
                </div>
                <div class="form-group">
                    <label for="tgl_lahir">Tanggal Lahir</label>
                    <input type="date" id="tgl_lahir" name="tgl_lahir" required>
                </div>
                <div class="form-group">
                    <label for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" id="alamat" name="alamat">
                </div>
                <div class="form-group">
                    <label for="kelurahan_id">Kelurahan</label>
                    <select id="kelurahan_id" name="kelurahan_id">
                        <option value="">Pilih Kelurahan</option>
                        <?php while ($row = $kelurahan->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="tambah">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Pasien</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_kode">Kode Pasien</label>
                    <input type="text" id="edit_kode" name="kode" required>
                </div>
                <div class="form-group">
                    <label for="edit_nama">Nama Pasien</label>
                    <input type="text" id="edit_nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="edit_tmp_lahir">Tempat Lahir</label>
                    <input type="text" id="edit_tmp_lahir" name="tmp_lahir" required>
                </div>
                <div class="form-group">
                    <label for="edit_tgl_lahir">Tanggal Lahir</label>
                    <input type="date" id="edit_tgl_lahir" name="tgl_lahir" required>
                </div>
                <div class="form-group">
                    <label for="edit_gender">Jenis Kelamin</label>
                    <select id="edit_gender" name="gender" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_email">Email</label>
                    <input type="email" id="edit_email" name="email">
                </div>
                <div class="form-group">
                    <label for="edit_alamat">Alamat</label>
                    <input type="text" id="edit_alamat" name="alamat">
                </div>
                <div class="form-group">
                    <label for="edit_kelurahan_id">Kelurahan</label>
                    <select id="edit_kelurahan_id" name="kelurahan_id">
                        <option value="">Pilih Kelurahan</option>
                        <?php 
                        $kelurahan->data_seek(0); // Reset pointer
                        while ($row = $kelurahan->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="update">Update</button>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan modal edit dengan data yang ada
        function editPasien(id, kode, nama, tmp_lahir, tgl_lahir, gender, email, alamat, kelurahan_id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_kode').value = kode;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_tmp_lahir').value = tmp_lahir;
            document.getElementById('edit_tgl_lahir').value = tgl_lahir;
            document.getElementById('edit_gender').value = gender;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_kelurahan_id').value = kelurahan_id;
            
            document.getElementById('editModal').style.display = 'block';
        }

        // Tutup modal ketika klik di luar modal
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>