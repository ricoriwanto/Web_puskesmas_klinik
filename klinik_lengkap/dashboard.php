<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Klinik</title>
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
        .stats {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            flex: 1;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <h1>Dashboard</h1>
        <div class="card">
            <h2>Selamat Datang di Sistem Manajemen Klinik</h2>
            
            <div class="stats">
                <?php
                // Hitung jumlah data
                $pasien = $conn->query("SELECT COUNT(*) as total FROM pasien")->fetch_assoc();
                $paramedik = $conn->query("SELECT COUNT(*) as total FROM paramedik")->fetch_assoc();
                $periksa = $conn->query("SELECT COUNT(*) as total FROM periksa")->fetch_assoc();
                ?>
                
                <div class="stat-card">
                    <h3>Pasien</h3>
                    <p><?php echo $pasien['total']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Paramedik</h3>
                    <p><?php echo $paramedik['total']; ?></p>
                </div>
                
                <div class="stat-card">
                    <h3>Periksa</h3>
                    <p><?php echo $periksa['total']; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>