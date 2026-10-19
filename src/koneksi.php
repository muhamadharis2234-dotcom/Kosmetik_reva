<?php
$koneksi = mysqli_connect("localhost", "root", "", "kosmetik_reva");

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>