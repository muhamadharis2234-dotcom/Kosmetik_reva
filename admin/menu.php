<style>
    .menu {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 20px;
    }

    .menu-label {
        font-size: 11px;
        font-weight: bold;
        color: #b27b91;
        letter-spacing: 2px;
        padding: 0 12px;
        margin-bottom: 8px;
    }

    .menu a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 14px;
        text-decoration: none;
        color: #795064;
        background: transparent;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.25s ease;
        border-left: 4px solid transparent;
    }

    .menu a .icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #ffe4ed;
        border-radius: 9px;
        font-size: 18px;
        flex-shrink: 0;
    }

    .menu a:hover {
        background: #ffe4ed;
        color: #c4386c;
        transform: translateX(4px);
    }

    .menu a.active {
        background: linear-gradient(135deg, #d94f80, #ed85a7);
        color: white;
        font-weight: bold;
        box-shadow: 0 4px 10px rgba(217, 79, 128, 0.2);
        border-left-color: #a82b59;
    }

    .menu a.active .icon {
        background: rgba(255, 255, 255, 0.25);
    }

    .menu a .arrow {
        margin-left: auto;
        font-size: 18px;
        opacity: 0.6;
    }

    @media (max-width: 700px) {
        .menu {
            margin-top: 10px;
        }
    }
</style>

<?php
$open = isset($_GET['open'])
    ? $_GET['open']
    : 'informasi_utama';
?>

<div class="menu">

    <div class="menu-label">MENU UTAMA</div>

    <a href="halaman_admin.php?open=informasi_utama"
       class="<?= $open == 'informasi_utama' ? 'active' : '' ?>">

        <span class="icon">🏠</span>
        <span>Informasi Utama</span>
        <span class="arrow">›</span>
    </a>

    <a href="halaman_admin.php?open=data_pelanggan"
       class="<?= $open == 'data_pelanggan' ? 'active' : '' ?>">

        <span class="icon">👩🏻</span>
        <span>Data Pelanggan</span>
        <span class="arrow">›</span>
    </a>

    <a href="halaman_admin.php?open=data_produk"
       class="<?= $open == 'data_produk' ? 'active' : '' ?>">

        <span class="icon">💄</span>
        <span>Data Produk</span>
        <span class="arrow">›</span>
    </a>

    <a href="halaman_admin.php?open=data_pemesanan"
       class="<?= $open == 'data_pemesanan' ? 'active' : '' ?>">

        <span class="icon">🛍️</span>
        <span>Data Pemesanan</span>
        <span class="arrow">›</span>
    </a>

</div>