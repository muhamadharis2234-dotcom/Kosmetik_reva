<?php
include "../src/koneksi.php";

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    echo "ID pelanggan tidak valid.";
    exit;
}

$id = (int) $_GET['id'];

$query = mysqli_prepare(
    $koneksi,
    "SELECT * FROM pelanggan WHERE id_pelanggan = ?"
);

mysqli_stmt_bind_param($query, "i", $id);
mysqli_stmt_execute($query);

$result = mysqli_stmt_get_result($query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Data pelanggan tidak ditemukan.";
    exit;
}

if (isset($_POST['update'])) {
    $nama_pelanggan = trim($_POST['nama_pelanggan']);
    $alamat = trim($_POST['alamat']);
    $no_hp = trim($_POST['no_hp']);

    if ($nama_pelanggan == "" || $alamat == "" || $no_hp == "") {
        echo "<script>
            alert('Semua data harus diisi!');
            history.back();
        </script>";
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE pelanggan
         SET nama_pelanggan = ?, alamat = ?, no_hp = ?
         WHERE id_pelanggan = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $nama_pelanggan,
        $alamat,
        $no_hp,
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('Data pelanggan berhasil diperbarui!');
            window.location='halaman_admin.php?open=data_pelanggan';
        </script>";
        exit;
    } else {
        echo "Gagal memperbarui data pelanggan.";
    }
}
?>

<h2>Edit Pelanggan</h2>

<p>Ubah informasi pelanggan pada formulir berikut.</p>

<br>

<form method="POST">

    <label>Nama Pelanggan</label>
    <input
        type="text"
        name="nama_pelanggan"
        value="<?= htmlspecialchars($data['nama_pelanggan']); ?>"
        required
    >

    <br>

    <label>Alamat</label>
    <textarea
        name="alamat"
        rows="4"
        required
    ><?= htmlspecialchars($data['alamat']); ?></textarea>

    <br>

    <label>No. HP</label>
    <input
        type="text"
        name="no_hp"
        value="<?= htmlspecialchars($data['no_hp']); ?>"
        required
    >

    <br><br>

    <button type="submit" name="update">Simpan Perubahan</button>

    <a href="halaman_admin.php?open=data_pelanggan" class="btn">
        Kembali
    </a>

</form>