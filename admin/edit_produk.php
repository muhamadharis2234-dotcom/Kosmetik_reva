<?php
include "../src/koneksi.php";

// Mengambil ID produk dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>
            alert('ID produk tidak valid!');
            window.location='halaman_admin.php?open=data_produk';
          </script>";
    exit;
}

$id_produk = (int) $_GET['id'];

// Mengambil data produk berdasarkan ID
$query = mysqli_prepare(
    $koneksi,
    "SELECT * FROM produk WHERE id_produk = ?"
);

mysqli_stmt_bind_param($query, "i", $id_produk);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<script>
            alert('Data produk tidak ditemukan!');
            window.location='halaman_admin.php?open=data_produk';
          </script>";
    exit;
}

// Proses menyimpan perubahan
if (isset($_POST['update'])) {

    $nama_produk = trim($_POST['nama_produk']);
    $kategori = trim($_POST['kategori']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    if (
        $nama_produk === "" ||
        $kategori === "" ||
        $harga === "" ||
        $stok === ""
    ) {
        echo "<script>alert('Semua data harus diisi!');</script>";
    } elseif (
        !is_numeric($harga) ||
        !is_numeric($stok) ||
        $harga < 0 ||
        $stok < 0 ||
        !is_finite((float)$harga) ||
        !is_finite((float)$stok) ||
        (float)$stok != (int)$stok
    ) {
        echo "<script>alert('Harga atau stok tidak valid!');</script>";
    } else {

        $harga = (float) $harga;
        $stok = (int) $stok;

        $update = mysqli_prepare(
            $koneksi,
            "UPDATE produk
             SET nama_produk = ?, kategori = ?, harga = ?, stok = ?
             WHERE id_produk = ?"
        );

        mysqli_stmt_bind_param(
            $update,
            "ssdii",
            $nama_produk,
            $kategori,
            $harga,
            $stok,
            $id_produk
        );

        if (mysqli_stmt_execute($update)) {
            echo "<script>
                    alert('Data produk berhasil diperbarui!');
                    window.location='halaman_admin.php?open=data_produk';
                  </script>";
            exit;
        } else {
            echo "Gagal memperbarui produk: "
                . htmlspecialchars(mysqli_stmt_error($update));
        }

        mysqli_stmt_close($update);
    }

    // Mempertahankan nilai yang diketik jika validasi gagal
    $data['nama_produk'] = $nama_produk;
    $data['kategori'] = $kategori;
    $data['harga'] = $harga;
    $data['stok'] = $stok;
}

mysqli_stmt_close($query);
?>

<h2>Edit Produk</h2>
<p>Ubah informasi produk kosmetik.</p>

<br>

<form method="POST" action="">

    <label>Nama Produk</label>
    <input
        type="text"
        name="nama_produk"
        value="<?php echo htmlspecialchars($data['nama_produk']); ?>"
        required
    >

    <label>Kategori</label>
    <select name="kategori" required>
        <option value="">-- Pilih Kategori --</option>

        <?php
        $kategori_list = [
            "Skincare",
            "Makeup",
            "Body Care",
            "Hair Care",
            "Parfum",
            "Lainnya"
        ];

        foreach ($kategori_list as $kategori_item) {
            $selected = ($data['kategori'] === $kategori_item)
                ? "selected"
                : "";

            echo '<option value="' .
                htmlspecialchars($kategori_item) . '" ' .
                $selected . '>' .
                htmlspecialchars($kategori_item) .
                '</option>';
        }

        // Jika kategori lama tidak ada dalam pilihan
        if (!in_array($data['kategori'], $kategori_list, true)) {
            echo '<option value="' .
                htmlspecialchars($data['kategori']) .
                '" selected>' .
                htmlspecialchars($data['kategori']) .
                '</option>';
        }
        ?>
    </select>

    <label>Harga</label>
    <input
        type="number"
        name="harga"
        min="0"
        step="0.01"
        value="<?php echo htmlspecialchars((string)$data['harga']); ?>"
        required
    >

    <label>Stok</label>
    <input
        type="number"
        name="stok"
        min="0"
        step="1"
        value="<?php echo htmlspecialchars((string)$data['stok']); ?>"
        required
    >

    <br><br>

    <button type="submit" name="update" class="btn">
        Simpan Perubahan
    </button>

    <a href="halaman_admin.php?open=data_produk" class="btn-hapus">
        Batal
    </a>

</form>