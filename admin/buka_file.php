<?php

$open = isset($_GET['open'])
    ? $_GET['open']
    : 'informasi_utama';

switch ($open) {

    case 'informasi_utama':
        include "informasi_utama.php";
        break;

    case 'data_pelanggan':
        include "pelanggan.php";
        break;

    case 'tambah_pelanggan':
        include "tambah_pelanggan.php";
        break;

    case 'edit_pelanggan':
        include "edit_pelanggan.php";
        break;

   case 'data_produk':
    include "produk.php";
    break;

case 'tambah_produk':
    include "tambah_produk.php";
    break;

case 'edit_produk':
    include "edit_produk.php";
    break;


case 'data_pemesanan':
    include "pemesanan.php";
    break;

case 'tambah_pemesanan':
    include "tambah_pemesanan.php";
    break;

case 'edit_pemesanan':
    include "edit_pemesanan.php";
    break;
    default:
        include "informasi_utama.php";
        break;
}
?>