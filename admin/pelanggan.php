<?php
include "../src/koneksi.php";

$query = mysqli_query($koneksi, "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC");
?>

<h2>Data Pelanggan</h2>

<p>Kelola data pelanggan yang terdaftar di Kosmetik Reva.</p>

<br>

<a href="halaman_admin.php?open=tambah_pelanggan" class="btn">
    + Tambah Pelanggan
</a>

<br><br>

<table>
    <tr>
        <th>No</th>
        <th>Nama Pelanggan</th>
        <th>Alamat</th>
        <th>No. HP</th>
        <th>Aksi</th>
    </tr>

    <?php
    $no = 1;

    while ($data = mysqli_fetch_assoc($query)) {
    ?>

    <tr>
        <td><?= $no++; ?></td>
        <td><?= htmlspecialchars($data['nama_pelanggan']); ?></td>
        <td><?= htmlspecialchars($data['alamat']); ?></td>
        <td><?= htmlspecialchars($data['no_hp']); ?></td>

        <td>
            <a
                href="halaman_admin.php?open=edit_pelanggan&id=<?= $data['id_pelanggan']; ?>"
                class="btn-edit"
            >Edit</a>

            <a
                href="hapus_pelanggan.php?id=<?= $data['id_pelanggan']; ?>"
                class="btn-hapus"
                onclick="return confirm('Yakin ingin menghapus pelanggan ini?')"
            >Hapus</a>
        </td>
    </tr>

    <?php } ?>

</table>