<?php
include "../src/koneksi.php";

// Mengambil data produk
$query = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY id_produk DESC");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<h2>Data Produk</h2>
<p>Kelola data produk kosmetik yang tersedia di Kosmetik Reva.</p>

<br>

<a href="halaman_admin.php?open=tambah_produk" class="btn">
    + Tambah Produk
</a>

<br><br>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        <?php
        $no = 1;

        if (mysqli_num_rows($query) > 0) {
            while ($data = mysqli_fetch_assoc($query)) {
        ?>
                <tr>
                    <td><?php echo $no++; ?></td>

                    <td>
                        <?php echo htmlspecialchars($data['nama_produk']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($data['kategori']); ?>
                    </td>

                    <td>
                        Rp <?php echo number_format($data['harga'], 0, ',', '.'); ?>
                    </td>

                    <td>
                        <?php echo $data['stok']; ?>
                    </td>

                    <td>
                        <a
                            href="halaman_admin.php?open=edit_produk&id=<?php echo $data['id_produk']; ?>"
                            class="btn-edit"
                        >Edit</a>

                        <a
                            href="hapus_produk.php?id=<?php echo $data['id_produk']; ?>"
                            class="btn-hapus"
                            onclick="return confirm('Yakin ingin menghapus produk ini?')"
                        >Hapus</a>
                    </td>
                </tr>
        <?php
            }
        } else {
        ?>
            <tr>
                <td colspan="6" style="text-align: center;">
                    Belum ada data produk.
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>