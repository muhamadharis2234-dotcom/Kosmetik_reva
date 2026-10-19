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

$status_valid = ['Diproses', 'Selesai', 'Dibatalkan'];

// Mengambil data pemesanan
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM pemesanan WHERE id_pemesanan = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id_pemesanan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    echo "<script>
        alert('Data pemesanan tidak ditemukan!');
        window.location='halaman_admin.php?open=data_pemesanan';
    </script>";
    exit;
}

if (isset($_POST['update'])) {
    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $id_produk = (int)$_POST['id_produk'];
    $tanggal = $_POST['tanggal_pemesanan'];
    $jumlah = filter_var($_POST['jumlah'], FILTER_VALIDATE_INT);
    $status = $_POST['status_pemesanan'];

    $tanggal_valid = DateTime::createFromFormat('!Y-m-d', $tanggal);
    $tanggal_valid = $tanggal_valid &&
        $tanggal_valid->format('Y-m-d') === $tanggal;

    if (
        $id_pelanggan < 1 ||
        $id_produk < 1 ||
        !$tanggal_valid ||
        $jumlah === false ||
        $jumlah < 1 ||
        !in_array($status, $status_valid, true)
    ) {
        echo "<script>alert('Data pemesanan tidak valid!');</script>";
    } else {
        mysqli_begin_transaction($koneksi);

        try {
            // Kunci pemesanan agar perubahan stok konsisten
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT * FROM pemesanan
                 WHERE id_pemesanan = ? FOR UPDATE"
            );
            mysqli_stmt_bind_param($stmt, "i", $id_pemesanan);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $lama = mysqli_fetch_assoc($res);
            mysqli_stmt_close($stmt);

            if (!$lama) {
                throw new Exception("Pemesanan tidak ditemukan.");
            }

            // Pastikan pelanggan ada
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT id_pelanggan FROM pelanggan
                 WHERE id_pelanggan = ?"
            );
            mysqli_stmt_bind_param($stmt, "i", $id_pelanggan);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            $pelanggan_ada = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);

            if (!$pelanggan_ada) {
                throw new Exception("Pelanggan tidak ditemukan.");
            }

            $id_produk_lama = (int)$lama['id_produk'];
            $jumlah_lama = (int)$lama['jumlah'];
            $status_lama = $lama['status_pemesanan'];

            // Produk lama dan baru dikunci berdasarkan urutan ID
            $daftar_id = array_unique([$id_produk_lama, $id_produk]);
            sort($daftar_id, SORT_NUMERIC);

            $produk_data = [];

            foreach ($daftar_id as $pid) {
                $stmt = mysqli_prepare(
                    $koneksi,
                    "SELECT harga, stok FROM produk
                     WHERE id_produk = ? FOR UPDATE"
                );
                mysqli_stmt_bind_param($stmt, "i", $pid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_bind_result($stmt, $harga_db, $stok_db);

                if (mysqli_stmt_fetch($stmt)) {
                    $produk_data[$pid] = [
                        'harga' => (float)$harga_db,
                        'stok' => (int)$stok_db
                    ];
                }

                mysqli_stmt_close($stmt);
            }

            if (!isset($produk_data[$id_produk])) {
                throw new Exception("Produk baru tidak ditemukan.");
            }

            if (!isset($produk_data[$id_produk_lama])) {
                throw new Exception("Produk lama tidak ditemukan.");
            }

            // Pesanan yang dibatalkan tidak memakai stok
            $stok_terpakai_lama =
                ($status_lama === 'Dibatalkan') ? 0 : $jumlah_lama;

            $stok_terpakai_baru =
                ($status === 'Dibatalkan') ? 0 : $jumlah;

            // Kembalikan stok pesanan lama terlebih dahulu
            if ($stok_terpakai_lama > 0) {
                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE produk SET stok = stok + ?
                     WHERE id_produk = ?"
                );
                mysqli_stmt_bind_param(
                    $stmt,
                    "ii",
                    $stok_terpakai_lama,
                    $id_produk_lama
                );

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception(mysqli_stmt_error($stmt));
                }

                mysqli_stmt_close($stmt);
            }

            // Ambil stok terbaru setelah pengembalian stok lama
            $stmt = mysqli_prepare(
                $koneksi,
                "SELECT stok FROM produk
                 WHERE id_produk = ? FOR UPDATE"
            );
            mysqli_stmt_bind_param($stmt, "i", $id_produk);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $stok_tersedia);
            $produk_ditemukan = mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if (!$produk_ditemukan) {
                throw new Exception("Produk tidak ditemukan.");
            }

            if ($stok_terpakai_baru > $stok_tersedia) {
                throw new Exception("Stok produk tidak mencukupi.");
            }

            // Kurangi stok untuk pesanan baru jika tidak dibatalkan
            if ($stok_terpakai_baru > 0) {
                $stmt = mysqli_prepare(
                    $koneksi,
                    "UPDATE produk SET stok = stok - ?
                     WHERE id_produk = ? AND stok >= ?"
                );
                mysqli_stmt_bind_param(
                    $stmt,
                    "iii",
                    $stok_terpakai_baru,
                    $id_produk,
                    $stok_terpakai_baru
                );

                if (
                    !mysqli_stmt_execute($stmt) ||
                    mysqli_stmt_affected_rows($stmt) !== 1
                ) {
                    throw new Exception("Stok produk tidak mencukupi.");
                }

                mysqli_stmt_close($stmt);
            }

            $harga = $produk_data[$id_produk]['harga'];
            $total_harga = $harga * $jumlah;

            // Simpan perubahan pemesanan
            $stmt = mysqli_prepare(
                $koneksi,
                "UPDATE pemesanan
                 SET id_pelanggan = ?,
                     id_produk = ?,
                     tanggal_pemesanan = ?,
                     jumlah = ?,
                     total_harga = ?,
                     status_pemesanan = ?
                 WHERE id_pemesanan = ?"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iisidsi",
                $id_pelanggan,
                $id_produk,
                $tanggal,
                $jumlah,
                $total_harga,
                $status,
                $id_pemesanan
            );

            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception(mysqli_stmt_error($stmt));
            }

            mysqli_stmt_close($stmt);

            mysqli_commit($koneksi);

            echo "<script>
                alert('Pemesanan berhasil diperbarui!');
                window.location='halaman_admin.php?open=data_pemesanan';
            </script>";
            exit;

        } catch (Throwable $e) {
            mysqli_rollback($koneksi);

            echo "<script>
                alert(" . json_encode(
                    "Gagal mengubah pemesanan: " . $e->getMessage()
                ) . ");
            </script>";
        }
    }
}

// Ambil ulang data untuk formulir
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT * FROM pemesanan WHERE id_pemesanan = ?"
);
mysqli_stmt_bind_param($stmt, "i", $id_pemesanan);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

$pelanggan = mysqli_query(
    $koneksi,
    "SELECT id_pelanggan, nama_pelanggan
     FROM pelanggan ORDER BY nama_pelanggan"
);

$produk = mysqli_query(
    $koneksi,
    "SELECT id_produk, nama_produk, harga, stok
     FROM produk ORDER BY nama_produk"
);
?>

<h2>Edit Pemesanan</h2>
<p>Ubah informasi pemesanan produk kosmetik.</p>

<br>

<form method="POST" action="">

    <label>Nama Pelanggan</label>
    <select name="id_pelanggan" required>
        <?php while ($p = mysqli_fetch_assoc($pelanggan)) { ?>
            <option
                value="<?php echo $p['id_pelanggan']; ?>"
                <?php echo ((int)$data['id_pelanggan'] === (int)$p['id_pelanggan']) ? 'selected' : ''; ?>
            >
                <?php echo htmlspecialchars($p['nama_pelanggan']); ?>
            </option>
        <?php } ?>
    </select>

    <label>Nama Produk</label>
    <select name="id_produk" required>
        <?php while ($pr = mysqli_fetch_assoc($produk)) { ?>
            <option
                value="<?php echo $pr['id_produk']; ?>"
                <?php echo ((int)$data['id_produk'] === (int)$pr['id_produk']) ? 'selected' : ''; ?>
            >
                <?php
                echo htmlspecialchars($pr['nama_produk']);
                echo " | Rp " . number_format($pr['harga'], 0, ',', '.');
                echo " | Stok: " . (int)$pr['stok'];
                ?>
            </option>
        <?php } ?>
    </select>

    <label>Tanggal Pemesanan</label>
    <input
        type="date"
        name="tanggal_pemesanan"
        value="<?php echo htmlspecialchars($data['tanggal_pemesanan']); ?>"
        required
    >

    <label>Jumlah</label>
    <input
        type="number"
        name="jumlah"
        min="1"
        value="<?php echo (int)$data['jumlah']; ?>"
        required
    >

    <label>Status Pemesanan</label>
    <select name="status_pemesanan" required>
        <?php foreach ($status_valid as $s) { ?>
            <option
                value="<?php echo $s; ?>"
                <?php echo ($data['status_pemesanan'] === $s) ? 'selected' : ''; ?>
            >
                <?php echo $s; ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    <button type="submit" name="update" class="btn">
        Simpan Perubahan
    </button>

    <a href="halaman_admin.php?open=data_pemesanan" class="btn-hapus">
        Batal
    </a>

</form>