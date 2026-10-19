<?php
include "../src/koneksi.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: halaman_admin.php?open=data_pelanggan");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM pelanggan WHERE id_pelanggan = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: halaman_admin.php?open=data_pelanggan");
    exit;
}

if (mysqli_errno($koneksi) == 1451) {
    echo "<script>
        alert('Pelanggan tidak bisa dihapus karena masih memiliki data pemesanan!');
        window.location='halaman_admin.php?open=data_pelanggan';
    </script>";
    exit;
}

echo "Gagal menghapus data pelanggan.";
?>