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
    
    $stmt = $conn->prepare("INSERT INTO unit_kerja (nama) VALUES (?)");
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    $stmt->close();
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    
    $stmt = $conn->prepare("UPDATE unit_kerja SET nama=? WHERE id=?");
    $stmt->bind_param("si", $nama, $id);
    $stmt->execute();
    $stmt->close();
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM unit_kerja WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// Ambil data unit kerja
$unit_kerja = $conn->query("SELECT * FROM unit_kerja");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Unit Kerja - Sistem Klinik</title>
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
        <h1>Data Unit Kerja</h1>
        
        <div class="card">
            <button onclick="document.getElementById('tambahModal').style.display='block'">Tambah Unit Kerja</button>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Unit Kerja</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $unit_kerja->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['nama']; ?></td>
                        <td>
                            <button onclick="editUnitKerja(<?php echo $row['id']; ?>, '<?php echo $row['nama']; ?>')">Edit</button>
                            <a href="unit_kerja.php?hapus=<?php echo $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus?')"><button>Hapus</button></a>
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
            <h2>Tambah Unit Kerja</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nama">Nama Unit Kerja</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <button type="submit" name="tambah">Simpan</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h2>Edit Unit Kerja</h2>
            <form method="POST" action="">
                <input type="hidden" id="edit_id" name="id">
                <div class="form-group">
                    <label for="edit_nama">Nama Unit Kerja</label>
                    <input type="text" id="edit_nama" name="nama" required>
                </div>
                <button type="submit" name="update">Update</button>
            </form>
        </div>
    </div>

    <script>
        // Fungsi untuk menampilkan modal edit dengan data yang ada
        function editUnitKerja(id, nama) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_nama').value = nama;
            
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