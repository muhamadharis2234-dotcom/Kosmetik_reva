<?php
include "../src/koneksi.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id']) || (int)$_GET['id'] < 1) {
    echo "<script>
        alert('ID pemesanan tidak valid!');
        window.location='halaman_admin.php?open=data_pemesanan';
    </script>";
    exit;
}

$id_pemesanan = (int)$_GET['id'];

mysqli_begin_transaction($koneksi);

try {
    // Ambil dan kunci data pemesanan
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_produk, jumlah, status_pemesanan
         FROM pemesanan
         WHERE id_pemesanan = ?
         FOR UPDATE"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_pemesanan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result(
        $stmt,
        $id_produk,
        $jumlah,
        $status
    );

    $ditemukan = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$ditemukan) {
        throw new Exception("Data pemesanan tidak ditemukan.");
    }

    // Kembalikan stok hanya jika pesanan belum dibatalkan
    if ($status !== 'Dibatalkan') {
        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE produk
             SET stok = stok + ?
             WHERE id_produk = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $jumlah,
            $id_produk
        );

        if (
            !mysqli_stmt_execute($stmt) ||
            mysqli_stmt_affected_rows($stmt) !== 1
        ) {
            throw new Exception("Gagal mengembalikan stok produk.");
        }

        mysqli_stmt_close($stmt);
    }

    // Hapus pemesanan
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM pemesanan WHERE id_pemesanan = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id_pemesanan);

    if (
        !mysqli_stmt_execute($stmt) ||
        mysqli_stmt_affected_rows($stmt) !== 1
    ) {
        throw new Exception("Gagal menghapus pemesanan.");
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($koneksi);

    echo "<script>
        alert('Pemesanan berhasil dihapus!');
        window.location='halaman_admin.php?open=data_pemesanan';
    </script>";
    exit;

} catch (Throwable $e) {
    mysqli_rollback($koneksi);

    echo "<script>
        alert(" . json_encode(
            "Gagal menghapus pemesanan: " . $e->getMessage()
        ) . ");
        window.location='halaman_admin.php?open=data_pemesanan';
    </script>";
    exit;
}
?>