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
    $nama = $_POST['nama'];
    $gender = $_POST['gender'];
    $tmp_lahir = $_POST['tmp_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $kategori = $_POST['kategori'];
    $telpon = $_POST['telpon'];
    $alamat = $_POST['alamat'];
    $unit_kerja_id = $_POST['unit_kerja_id'];
    
    $stmt = $conn->prepare("INSERT INTO paramedik (nama, gender, tmp_lahir, tgl_lahir, kategori, telpon, alamat, unit_kerja_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $nama, $gender, $tmp_lahir, $tgl_lahir, $kategori, $telpon, $alamat, $unit_kerja_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $gender = $_POST['gender'];
    $tmp_lahir = $_POST['tmp_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $kategori = $_POST['kategori'];
    $telpon = $_POST['telpon'];
    $alamat = $_POST['alamat'];
    $unit_kerja_id = $_POST['unit_kerja_id'];
    
    $stmt = $conn->prepare("UPDATE paramedik SET nama=?, gender=?, tmp_lahir=?, tgl_lahir=?, kategori=?, telpon=?, alamat=?, unit_kerja_id=? WHERE id=?");
    $stmt->bind_param("sssssssii", $nama, $gender, $tmp_lahir, $tgl_lahir, $kategori, $telpon, $alamat, $unit_kerja_id, $id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM paramedik WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Ambil data paramedik
$paramedik = $conn->query("SELECT paramedik.*, unit_kerja.nama as nama_unit FROM paramedik LEFT JOIN unit_kerja ON paramedik.unit_kerja_id = unit_kerja.id");
$unit_kerja = $conn->query("SELECT * FROM unit_kerja");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Paramedik - Sistem Klinik</title>
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
        <h1>Data Paramedik</h1>
        
        <div class="card">
            <button onclick="document.getElementById('tambahModal').style.display='block'">Tambah Paramedik</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Gender</th>
                        <th>Tempat/Tgl Lahir</th>
                        <th>Kategori</th>
                        <th>Telpon</th>
                        <th>Alamat</th>
                        <th>Unit Kerja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $paramedik->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td><?php echo $row['gender'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                        <td><?php echo $row['tmp_lahir'] . ', ' . date('d-m-Y', strtotime($row['tgl_lahir'])); ?></td>
                        <td><?php echo ucfirst($row['kategori']); ?></td>
                        <td><?php echo $row['telpon']; ?></td>
                        <td><?php echo $row['alamat']; ?></td>
                        <td><?php echo $row['nama_unit'] ?? '-'; ?></td>
                        <td>
                            <button onclick="editParamedik(<?php echo $row['id']; ?>, '<?php echo $row['nama']; ?>', '<?php echo $row['gender']; ?>', '<?php echo $row['tmp_lahir']; ?>', '<?php echo $row['tgl_lahir']; ?>', '<?php echo $row['kategori']; ?>', '<?php echo $row['telpon']; ?>', '<?php echo $row['alamat']; ?>', <?php echo $row['unit_kerja_id']; ?>)">Edit</button>
                            <a href="paramedik.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')"><button>Hapus</button></a>
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
            <h2>Tambah Paramedik</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Paramedik</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
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
                    <label for="kategori">Kategori</label>
                    <select id="kategori" name="kategori" required>
                        <option value="dokter">Dokter</option>
                        <option value="bidan">Bidan</option>
                        <option value="perawat">Perawat</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="telpon">Telpon</label>
                    <input type="text" id="telpon" name="telpon">
                </div>
                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <input type="text" id="alamat" name="alamat">
                </div>
                <div class="form-group">
                    <label for="unit_kerja_id">Unit Kerja</label>
                    <select id="unit_kerja_id" name="unit_kerja_id">
                        <option value="">Pilih Unit Kerja</option>
                        <?php while ($row = $unit_kerja->fetch_assoc()): ?>
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
            <h2>Edit Paramedik</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_nama">Nama Paramedik</label>
                    <input type="text" id="edit_nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="edit_gender">Jenis Kelamin</label>
                    <select id="edit_gender" name="gender" required>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
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
                    <label for="edit_kategori">Kategori</label>
                    <select id="edit_kategori" name="kategori" required>
                        <option value="dokter">Dokter</option>
                        <option value="bidan">Bidan</option>
                        <option value="perawat">Perawat</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_telpon">Telpon</label>
                    <input type="text" id="edit_telpon" name="telpon">
                </div>
                <div class="form-group">
                    <label for="edit_alamat">Alamat</label>
                    <input type="text" id="edit_alamat" name="alamat">
                </div>
                <div class="form-group">
                    <label for="edit_unit_kerja_id">Unit Kerja</label>
                    <select id="edit_unit_kerja_id" name="unit_kerja_id">
                        <option value="">Pilih Unit Kerja</option>
                        <?php 
                        $unit_kerja->data_seek(0); // Reset pointer
                        while ($row = $unit_kerja->fetch_assoc()): ?>
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
        function editParamedik(id, nama, gender, tmp_lahir, tgl_lahir, kategori, telpon, alamat, unit_kerja_id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_gender').value = gender;
            document.getElementById('edit_tmp_lahir').value = tmp_lahir;
            document.getElementById('edit_tgl_lahir').value = tgl_lahir;
            document.getElementById('edit_kategori').value = kategori;
            document.getElementById('edit_telpon').value = telpon;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_unit_kerja_id').value = unit_kerja_id;
            
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