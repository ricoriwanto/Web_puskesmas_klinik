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
    $tanggal = $_POST['tanggal'];
    $berat = $_POST['berat'];
    $tinggi = $_POST['tinggi'];
    $tensi = $_POST['tensi'];
    $keterangan = $_POST['keterangan'];
    $pasien_id = $_POST['pasien_id'];
    $dokter_id = $_POST['dokter_id'];
    
    $stmt = $conn->prepare("INSERT INTO periksa (tanggal, berat, tinggi, tensi, keterangan, pasien_id, dokter_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sddsiii", $tanggal, $berat, $tinggi, $tensi, $keterangan, $pasien_id, $dokter_id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $tanggal = $_POST['tanggal'];
    $berat = $_POST['berat'];
    $tinggi = $_POST['tinggi'];
    $tensi = $_POST['tensi'];
    $keterangan = $_POST['keterangan'];
    $pasien_id = $_POST['pasien_id'];
    $dokter_id = $_POST['dokter_id'];
    
    $stmt = $conn->prepare("UPDATE periksa SET tanggal=?, berat=?, tinggi=?, tensi=?, keterangan=?, pasien_id=?, dokter_id=? WHERE id=?");
    $stmt->bind_param("sddsiiii", $tanggal, $berat, $tinggi, $tensi, $keterangan, $pasien_id, $dokter_id, $id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM periksa WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Ambil data periksa
$periksa = $conn->query("SELECT periksa.*, pasien.nama as nama_pasien, paramedik.nama as nama_dokter FROM periksa LEFT JOIN pasien ON periksa.pasien_id = pasien.id LEFT JOIN paramedik ON periksa.dokter_id = paramedik.id");
$pasien = $conn->query("SELECT * FROM pasien");
$dokter = $conn->query("SELECT * FROM paramedik WHERE kategori = 'dokter'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Periksa - Sistem Klinik</title>
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
        <h1>Data Pemeriksaan</h1>
        
        <div class="card">
            <button onclick="document.getElementById('tambahModal').style.display='block'">Tambah Pemeriksaan</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Dokter</th>
                        <th>Berat (kg)</th>
                        <th>Tinggi (cm)</th>
                        <th>Tensi</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $periksa->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                        <td><?php echo $row['nama_pasien']; ?></td>
                        <td><?php echo $row['nama_dokter']; ?></td>
                        <td><?php echo $row['berat']; ?></td>
                        <td><?php echo $row['tinggi']; ?></td>
                        <td><?php echo $row['tensi']; ?></td>
                        <td><?php echo $row['keterangan']; ?></td>
                        <td>
                            <button onclick="editPeriksa(<?php echo $row['id']; ?>, '<?php echo $row['tanggal']; ?>', <?php echo $row['berat']; ?>, <?php echo $row['tinggi']; ?>, '<?php echo $row['tensi']; ?>', '<?php echo $row['keterangan']; ?>', <?php echo $row['pasien_id']; ?>, <?php echo $row['dokter_id']; ?>)">Edit</button>
                            <a href="periksa.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')"><button>Hapus</button></a>
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
            <h2>Tambah Pemeriksaan</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" required>
                </div>
                <div class="form-group">
                    <label for="pasien_id">Pasien</label>
                    <select id="pasien_id" name="pasien_id" required>
                        <option value="">Pilih Pasien</option>
                        <?php while ($row = $pasien->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="dokter_id">Dokter</label>
                    <select id="dokter_id" name="dokter_id" required>
                        <option value="">Pilih Dokter</option>
                        <?php while ($row = $dokter->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="berat">Berat Badan (kg)</label>
                    <input type="number" step="0.1" id="berat" name="berat" required>
                </div>
                <div class="form-group">
                    <label for="tinggi">Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" id="tinggi" name="tinggi" required>
                </div>
                <div class="form-group">
                    <label for="tensi">Tensi</label>
                    <input type="text" id="tensi" name="tensi">
                </div>
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan">
                </div>
                <button type="submit" name="tambah">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Pemeriksaan</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_tanggal">Tanggal</label>
                    <input type="date" id="edit_tanggal" name="tanggal" required>
                </div>
                <div class="form-group">
                    <label for="edit_pasien_id">Pasien</label>
                    <select id="edit_pasien_id" name="pasien_id" required>
                        <option value="">Pilih Pasien</option>
                        <?php 
                        $pasien->data_seek(0); // Reset pointer
                        while ($row = $pasien->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_dokter_id">Dokter</label>
                    <select id="edit_dokter_id" name="dokter_id" required>
                        <option value="">Pilih Dokter</option>
                        <?php 
                        $dokter->data_seek(0); // Reset pointer
                        while ($row = $dokter->fetch_assoc()): ?>
                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edit_berat">Berat Badan (kg)</label>
                    <input type="number" step="0.1" id="edit_berat" name="berat" required>
                </div>
                <div class="form-group">
                    <label for="edit_tinggi">Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" id="edit_tinggi" name="tinggi" required>
                </div>
                <div class="form-group">
                    <label for="edit_tensi">Tensi</label>
                    <input type="text" id="edit_tensi" name="tensi">
                </div>
                <div class="form-group">
                    <label for="edit_keterangan">Keterangan</label>
                    <input type="text" id="edit_keterangan" name="keterangan">
                </div>
                <button type="submit" name="update">Update</button>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan modal edit dengan data yang ada
        function editPeriksa(id, tanggal, berat, tinggi, tensi, keterangan, pasien_id, dokter_id) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_tanggal').value = tanggal;
            document.getElementById('edit_berat').value = berat;
            document.getElementById('edit_tinggi').value = tinggi;
            document.getElementById('edit_tensi').value = tensi;
            document.getElementById('edit_keterangan').value = keterangan;
            document.getElementById('edit_pasien_id').value = pasien_id;
            document.getElementById('edit_dokter_id').value = dokter_id;
            
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