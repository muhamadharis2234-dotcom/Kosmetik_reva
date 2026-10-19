<?php
include "../src/koneksi.php";

// Mengambil data pelanggan untuk pilihan dropdown
$pelanggan = mysqli_query(
    $koneksi,
    "SELECT id_pelanggan, nama_pelanggan
     FROM pelanggan
     ORDER BY nama_pelanggan ASC"
);

// Mengambil data produk untuk pilihan dropdown
$produk = mysqli_query(
    $koneksi,
    "SELECT id_produk, nama_produk, harga, stok
     FROM produk
     ORDER BY nama_produk ASC"
);

if (!$pelanggan || !$produk) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

// Proses simpan pemesanan
if (isset($_POST['simpan'])) {

    $id_pelanggan = (int) $_POST['id_pelanggan'];
    $id_produk = (int) $_POST['id_produk'];
    $tanggal = $_POST['tanggal_pemesanan'];
    $jumlah = (int) $_POST['jumlah'];
    $status = $_POST['status_pemesanan'];

    $status_valid = ['Diproses', 'Selesai', 'Dibatalkan'];

    if (
        $id_pelanggan <= 0 ||
        $id_produk <= 0 ||
        $tanggal === '' ||
        $jumlah <= 0 ||
        !in_array($status, $status_valid, true)
    ) {
        echo "<script>
                alert('Periksa kembali data pemesanan!');
                window.history.back();
              </script>";
        exit;
    }

    // Memastikan pelanggan benar-benar ada
    $cek_pelanggan = mysqli_prepare(
        $koneksi,
        "SELECT id_pelanggan FROM pelanggan WHERE id_pelanggan = ?"
    );

    mysqli_stmt_bind_param($cek_pelanggan, "i", $id_pelanggan);
    mysqli_stmt_execute($cek_pelanggan);
    mysqli_stmt_store_result($cek_pelanggan);

    $pelanggan_ada = mysqli_stmt_num_rows($cek_pelanggan) > 0;
    mysqli_stmt_close($cek_pelanggan);

    if (!$pelanggan_ada) {
        die("Pelanggan tidak ditemukan.");
    }

    // Mengambil harga dan stok terbaru dari database
    $cek_produk = mysqli_prepare(
        $koneksi,
        "SELECT harga, stok FROM produk WHERE id_produk = ?"
    );

    mysqli_stmt_bind_param($cek_produk, "i", $id_produk);
    mysqli_stmt_execute($cek_produk);
    mysqli_stmt_bind_result($cek_produk, $harga_produk, $stok_produk);

    $produk_ada = mysqli_stmt_fetch($cek_produk);

    mysqli_stmt_close($cek_produk);

    if (!$produk_ada) {
        die("Produk tidak ditemukan.");
    }

    if ($jumlah > $stok_produk) {
        echo "<script>
                alert('Jumlah pemesanan melebihi stok yang tersedia!');
                window.history.back();
              </script>";
        exit;
    }

    $total_harga = $harga_produk * $jumlah;

    // Simpan pemesanan dan kurangi stok dalam satu transaksi database
    mysqli_begin_transaction($koneksi);

    try {
        $insert = mysqli_prepare(
            $koneksi,
            "INSERT INTO pemesanan
                (id_pelanggan, id_produk, tanggal_pemesanan,
                 jumlah, total_harga, status_pemesanan)
             VALUES (?, ?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $insert,
            "iisids",
            $id_pelanggan,
            $id_produk,
            $tanggal,
            $jumlah,
            $total_harga,
            $status
        );

        if (!mysqli_stmt_execute($insert)) {
            throw new Exception(mysqli_stmt_error($insert));
        }

        mysqli_stmt_close($insert);

        // Kurangi stok hanya jika stok masih mencukupi
        $update_stok = mysqli_prepare(
            $koneksi,
            "UPDATE produk
             SET stok = stok - ?
             WHERE id_produk = ? AND stok >= ?"
        );

        mysqli_stmt_bind_param(
            $update_stok,
            "iii",
            $jumlah,
            $id_produk,
            $jumlah
        );

        if (!mysqli_stmt_execute($update_stok)) {
            throw new Exception(mysqli_stmt_error($update_stok));
        }

        if (mysqli_stmt_affected_rows($update_stok) !== 1) {
            throw new Exception("Stok produk tidak mencukupi.");
        }

        mysqli_stmt_close($update_stok);

        mysqli_commit($koneksi);

        echo "<script>
                alert('Pemesanan berhasil ditambahkan!');
                window.location='halaman_admin.php?open=data_pemesanan';
              </script>";
        exit;

    } catch (Throwable $e) {
        mysqli_rollback($koneksi);

        echo "<script>
                alert(" . json_encode(
                    "Pemesanan gagal disimpan: " . $e->getMessage()
                ) . ");
                window.history.back();
              </script>";
        exit;
    }
}
?>

<h2>Tambah Pemesanan</h2>
<p>Masukkan informasi pemesanan produk kosmetik.</p>

<br>

<form method="POST" action="">

    <label>Nama Pelanggan</label>
    <select name="id_pelanggan" required>
        <option value="">-- Pilih Pelanggan --</option>

        <?php while ($p = mysqli_fetch_assoc($pelanggan)) { ?>
            <option value="<?php echo $p['id_pelanggan']; ?>">
                <?php echo htmlspecialchars($p['nama_pelanggan']); ?>
            </option>
        <?php } ?>
    </select>

    <label>Nama Produk</label>
    <select name="id_produk" required>
        <option value="">-- Pilih Produk --</option>

        <?php while ($pr = mysqli_fetch_assoc($produk)) { ?>
            <option value="<?php echo $pr['id_produk']; ?>">
                <?php
                echo htmlspecialchars($pr['nama_produk']);
                echo " | Rp " . number_format($pr['harga'], 0, ',', '.');
                echo " | Stok: " . (int) $pr['stok'];
                ?>
            </option>
        <?php } ?>
    </select>

    <label>Tanggal Pemesanan</label>
    <input
        type="date"
        name="tanggal_pemesanan"
        value="<?php echo date('Y-m-d'); ?>"
        required
    >

    <label>Jumlah</label>
    <input
        type="number"
        name="jumlah"
        min="1"
        placeholder="Masukkan jumlah produk"
        required
    >

    <label>Status Pemesanan</label>
    <select name="status_pemesanan" required>
        <option value="Diproses">Diproses</option>
        <option value="Selesai">Selesai</option>
        <option value="Dibatalkan">Dibatalkan</option>
    </select>

    <br><br>

    <button type="submit" name="simpan" class="btn">
        Simpan Pemesanan
    </button>

    <a href="halaman_admin.php?open=data_pemesanan" class="btn-hapus">
        Batal
    </a>

</form>