<?php
include "../src/koneksi.php";

if (isset($_POST['simpan'])) {

    $nama_pelanggan = trim($_POST['nama_pelanggan']);
    $alamat = trim($_POST['alamat']);
    $no_hp = trim($_POST['no_hp']);

    if ($nama_pelanggan == "" || $alamat == "" || $no_hp == "") {
        echo "<script>
                alert('Semua data harus diisi!');
                window.history.back();
              </script>";
        exit;
    }

    $query = mysqli_prepare(
        $koneksi,
        "INSERT INTO pelanggan (nama_pelanggan, alamat, no_hp)
         VALUES (?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $query,
        "sss",
        $nama_pelanggan,
        $alamat,
        $no_hp
    );

    if (mysqli_stmt_execute($query)) {
        echo "<script>
                alert('Data pelanggan berhasil ditambahkan!');
                window.location='halaman_admin.php?open=data_pelanggan';
              </script>";
        exit;
    } else {
        echo "Gagal menambahkan data: " . htmlspecialchars(mysqli_stmt_error($query));
    }

    mysqli_stmt_close($query);
}
?>

<h2>Tambah Pelanggan</h2>
<p>Masukkan informasi pelanggan baru.</p>

<br>

<form method="POST" action="">

    <label>Nama Pelanggan</label>
    <input
        type="text"
        name="nama_pelanggan"
        placeholder="Masukkan nama pelanggan"
        required
    >

    <label>Alamat</label>
    <textarea
        name="alamat"
        placeholder="Masukkan alamat pelanggan"
        required
    ></textarea>

    <label>No. HP</label>
    <input
        type="text"
        name="no_hp"
        placeholder="Masukkan nomor HP"
        required
    >

    <br><br>

    <button type="submit" name="simpan" class="btn">
        Simpan
    </button>

    <a href="halaman_admin.php?open=data_pelanggan" class="btn-hapus">
        Batal
    </a>

</form>