<?php
include_once "../src/koneksi.php";

// Menghitung jumlah pelanggan
$query_pelanggan = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM pelanggan
");
$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$total_pelanggan = $data_pelanggan['total'];

// Menghitung jumlah produk
$query_produk = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM produk
");
$data_produk = mysqli_fetch_assoc($query_produk);
$total_produk = $data_produk['total'];

// Menghitung jumlah pemesanan
$query_pemesanan = mysqli_query($koneksi, "
    SELECT COUNT(*) AS total FROM pemesanan
");
$data_pemesanan = mysqli_fetch_assoc($query_pemesanan);
$total_pemesanan = $data_pemesanan['total'];

// Menghitung total pendapatan dari pemesanan yang selesai
$query_pendapatan = mysqli_query($koneksi, "
    SELECT COALESCE(SUM(total_harga), 0) AS total
    FROM pemesanan
    WHERE status_pemesanan = 'Selesai'
");
$data_pendapatan = mysqli_fetch_assoc($query_pendapatan);
$total_pendapatan = $data_pendapatan['total'];

// Mengambil jumlah pemesanan berdasarkan status
$query_status = mysqli_query($koneksi, "
    SELECT status_pemesanan, COUNT(*) AS jumlah
    FROM pemesanan
    GROUP BY status_pemesanan
");

$status_data = [
    'Diproses' => 0,
    'Selesai' => 0,
    'Dibatalkan' => 0
];

while ($row = mysqli_fetch_assoc($query_status)) {
    $status = $row['status_pemesanan'];

    if (array_key_exists($status, $status_data)) {
        $status_data[$status] = (int) $row['jumlah'];
    }
}

$status_labels = array_keys($status_data);
$status_values = array_values($status_data);
?>

<style>
    .dashboard {
        padding: 20px;
        font-family: Arial, sans-serif;
    }

    .dashboard h1 {
        color: #ad4775;
        margin-bottom: 8px;
    }

    .dashboard .deskripsi {
        color: #777;
        margin-bottom: 25px;
    }

    .kartu-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .kartu {
        background: #fff;
        padding: 22px;
        border-radius: 15px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        border-left: 5px solid #e88bb3;
    }

    .kartu h3 {
        color: #777;
        font-size: 15px;
        font-weight: normal;
        margin: 0 0 12px;
    }

    .kartu .angka {
        color: #ad4775;
        font-size: 27px;
        font-weight: bold;
        margin: 0;
    }

    .kartu .ikon {
        font-size: 25px;
        margin-bottom: 12px;
    }

    .bagian-diagram {
        background: #fff;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        margin-top: 20px;
    }

    .bagian-diagram h2 {
        color: #ad4775;
        margin-top: 0;
    }

    .bagian-diagram p {
        color: #777;
    }

    .diagram-container {
        max-width: 450px;
        height: 350px;
        margin: 25px auto 0;
    }

    @media (max-width: 600px) {
        .dashboard {
            padding: 10px;
        }

        .kartu-container {
            grid-template-columns: 1fr;
        }

        .diagram-container {
            height: 300px;
        }
    }
</style>

<div class="dashboard">

    <h1>Informasi Utama</h1>

    <p class="deskripsi">
        Selamat datang di Dashboard Kosmetik Reva.
        Berikut ringkasan data toko kosmetik kamu.
    </p>

    <!-- Kartu Informasi -->
    <div class="kartu-container">

        <div class="kartu">
            <div class="ikon">👥</div>
            <h3>Total Pelanggan</h3>
            <p class="angka">
                <?= number_format($total_pelanggan); ?>
            </p>
        </div>

        <div class="kartu">
            <div class="ikon">💄</div>
            <h3>Total Produk</h3>
            <p class="angka">
                <?= number_format($total_produk); ?>
            </p>
        </div>

        <div class="kartu">
            <div class="ikon">🛍️</div>
            <h3>Total Pemesanan</h3>
            <p class="angka">
                <?= number_format($total_pemesanan); ?>
            </p>
        </div>

        <div class="kartu">
            <div class="ikon">💰</div>
            <h3>Total Pendapatan</h3>
            <p class="angka" style="font-size: 22px;">
                Rp <?= number_format((float) $total_pendapatan, 0, ',', '.'); ?>
            </p>
        </div>

    </div>

    <!-- Diagram Pie -->
    <div class="bagian-diagram">

        <h2>Diagram Pie Pemesanan</h2>

        <p>
            Perbandingan jumlah pemesanan berdasarkan statusnya.
        </p>

        <?php if ($total_pemesanan > 0) : ?>

            <div class="diagram-container">
                <canvas id="diagramPemesanan"></canvas>
            </div>

        <?php else : ?>

            <p style="text-align: center; padding: 40px 0;">
                Belum ada data pemesanan untuk ditampilkan.
            </p>

        <?php endif; ?>

    </div>

</div>

<?php if ($total_pemesanan > 0) : ?>

    <!-- Library Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const labelsPemesanan = <?= json_encode(
            $status_labels,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
        ); ?>;

        const dataPemesanan = <?= json_encode($status_values); ?>;

        const canvasPemesanan = document.getElementById('diagramPemesanan');

        new Chart(canvasPemesanan, {
            type: 'pie',

            data: {
                labels: labelsPemesanan,

                datasets: [{
                    label: 'Jumlah Pemesanan',

                    data: dataPemesanan,

                    backgroundColor: [
                        '#F48FB1',
                        '#CE93D8',
                        '#F8BBD0'
                    ],

                    borderColor: '#FFFFFF',
                    borderWidth: 3
                }]
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        position: 'bottom',

                        labels: {
                            padding: 20,
                            font: {
                                size: 13
                            }
                        }
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' +
                                    context.raw + ' pemesanan';
                            }
                        }
                    }
                }
            }
        });
    </script>

<?php endif; ?>