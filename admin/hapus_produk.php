<?php
include "../src/koneksi.php";

// Memeriksa ID produk
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('ID produk tidak valid!');
            window.location='halaman_admin.php?open=data_produk';
          </script>";
    exit;
}

$id_produk = (int) $_GET['id'];

// Menghapus produk berdasarkan ID
$query = mysqli_prepare(
    $koneksi,
    "DELETE FROM produk WHERE id_produk = ?"
);

mysqli_stmt_bind_param($query, "i", $id_produk);

if (mysqli_stmt_execute($query)) {

    if (mysqli_stmt_affected_rows($query) > 0) {
        echo "<script>
                alert('Produk berhasil dihapus!');
                window.location='halaman_admin.php?open=data_produk';
              </script>";
    } else {
        echo "<script>
                alert('Produk tidak ditemukan!');
                window.location='halaman_admin.php?open=data_produk';
              </script>";
    }

} else {

    // Produk tidak dapat dihapus jika sudah digunakan dalam pemesanan
    if (mysqli_stmt_errno($query) == 1451) {
        echo "<script>
                alert('Produk tidak dapat dihapus karena sudah digunakan dalam pemesanan!');
                window.location='halaman_admin.php?open=data_produk';
              </script>";
    } else {
        echo "Gagal menghapus produk: "
            . htmlspecialchars(mysqli_stmt_error($query));
    }
}

mysqli_stmt_close($query);
?>