<?php
include "../src/koneksi.php";

if (isset($_POST['simpan'])) {

    $nama_produk = trim($_POST['nama_produk']);
    $kategori = trim($_POST['kategori']);
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    if (
        $nama_produk == "" ||
        $kategori == "" ||
        $harga == "" ||
        $stok == ""
    ) {
        echo "<script>
                alert('Semua data harus diisi!');
                window.history.back();
              </script>";
        exit;
    }

    if (!is_numeric($harga) || !is_numeric($stok) || $harga < 0 || $stok < 0) {
        echo "<script>
                alert('Harga dan stok harus berupa angka yang valid!');
                window.history.back();
              </script>";
        exit;
    }

    $query = mysqli_prepare(
        $koneksi,
        "INSERT INTO produk (nama_produk, kategori, harga, stok)
         VALUES (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $query,
        "ssdi",
        $nama_produk,
        $kategori,
        $harga,
        $stok
    );

    if (mysqli_stmt_execute($query)) {
        echo "<script>
                alert('Produk berhasil ditambahkan!');
                window.location='halaman_admin.php?open=data_produk';
              </script>";
        exit;
    } else {
        echo "Gagal menambahkan produk: "
            . htmlspecialchars(mysqli_stmt_error($query));
    }

    mysqli_stmt_close($query);
}
?>

<h2>Tambah Produk</h2>
<p>Masukkan informasi produk kosmetik baru.</p>

<br>

<form method="POST" action="">

    <label>Nama Produk</label>
    <input
        type="text"
        name="nama_produk"
        placeholder="Contoh: Lip Cream"
        required
    >

    <label>Kategori</label>
    <select name="kategori" required>
        <option value="">-- Pilih Kategori --</option>
        <option value="Skincare">Skincare</option>
        <option value="Makeup">Makeup</option>
        <option value="Body Care">Body Care</option>
        <option value="Hair Care">Hair Care</option>
        <option value="Parfum">Parfum</option>
        <option value="Lainnya">Lainnya</option>
    </select>

    <label>Harga</label>
    <input
        type="number"
        name="harga"
        placeholder="Masukkan harga produk"
        min="0"
        step="0.01"
        required
    >

    <label>Stok</label>
    <input
        type="number"
        name="stok"
        placeholder="Masukkan jumlah stok"
        min="0"
        required
    >

    <br><br>

    <button type="submit" name="simpan" class="btn">
        Simpan
    </button>

    <a href="halaman_admin.php?open=data_produk" class="btn-hapus">
        Batal
    </a>

</form>