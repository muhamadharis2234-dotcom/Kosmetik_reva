<?php
include "../src/koneksi.php";

// Mengambil data pemesanan beserta nama pelanggan dan produk
$query = mysqli_query($koneksi, "
    SELECT
        pemesanan.*,
        pelanggan.nama_pelanggan,
        produk.nama_produk
    FROM pemesanan
    INNER JOIN pelanggan
        ON pemesanan.id_pelanggan = pelanggan.id_pelanggan
    INNER JOIN produk
        ON pemesanan.id_produk = produk.id_produk
    ORDER BY pemesanan.id_pemesanan DESC
");

if (!$query) {
    die("Query gagal: " . mysqli_error($koneksi));
}
?>

<h2>Data Pemesanan</h2>
<p>Kelola transaksi pemesanan produk kosmetik.</p>

<br>

<a href="halaman_admin.php?open=tambah_pemesanan" class="btn">
    + Tambah Pemesanan
</a>

<br><br>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Nama Pelanggan</th>
            <th>Nama Produk</th>
            <th>Tanggal</th>
            <th>Jumlah</th>
            <th>Total Harga</th>
            <th>Status</th>
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
                        <?php echo htmlspecialchars($data['nama_pelanggan']); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($data['nama_produk']); ?>
                    </td>

                    <td>
                        <?php echo date('d-m-Y', strtotime($data['tanggal_pemesanan'])); ?>
                    </td>

                    <td>
                        <?php echo (int) $data['jumlah']; ?>
                    </td>

                    <td>
                        Rp <?php echo number_format($data['total_harga'], 0, ',', '.'); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($data['status_pemesanan']); ?>
                    </td>

                    <td>
                        <a
                            href="halaman_admin.php?open=edit_pemesanan&id=<?php echo $data['id_pemesanan']; ?>"
                            class="btn-edit"
                        >Edit</a>

                        <a
                            href="hapus_pemesanan.php?id=<?php echo $data['id_pemesanan']; ?>"
                            class="btn-hapus"
                            onclick="return confirm('Yakin ingin menghapus pemesanan ini?')"
                        >Hapus</a>
                    </td>
                </tr>
        <?php
            }
        } else {
        ?>
            <tr>
                <td colspan="8" style="text-align: center;">
                    Belum ada data pemesanan.
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>