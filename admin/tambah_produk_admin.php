<?php
include 'koneksi.php';

if (isset($_POST['tambah'])) {

    $nama      = mysqli_real_escape_string($conn, $_POST['nama']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga     = (int) $_POST['harga'];
    $kategori  = mysqli_real_escape_string($conn, $_POST['kategori']);

    $gambar = $_FILES['gambar']['name'];
    $tmp    = $_FILES['gambar']['tmp_name'];

    if ($gambar != "") {
        move_uploaded_file($tmp, "uploads/" . $gambar);

        $query = "INSERT INTO produk 
                  (nama, deskripsi, harga, kategori, gambar) 
                  VALUES 
                  ('$nama','$deskripsi','$harga','$kategori','$gambar')";

        if (mysqli_query($conn, $query)) {
            header("Location: produk_admin.php");
            exit;
        } else {
            echo "Gagal menyimpan data!";
        }
    } else {
        echo "Gambar wajib diupload!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Tambah Produk</title>
<style>
body {
    background: #eee;
    font-family: Arial, sans-serif;
}
.container {
    width: 380px;
    background: yellow;
    margin: 30px auto;
    padding: 20px 25px;
    border-radius: 10px;
    border: 2px solid #444;
}
h2 {
    text-align: center;
    color: purple;
    margin-bottom: 20px;
}
label {
    font-weight: bold;
    display: block;
    margin-top: 15px;
}
input[type=text],
input[type=number],
textarea,
select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #888;
    border-radius: 5px;
}
#btnTambah {
    width: 100%;
    padding: 10px;
    background: purple;
    color: white;
    margin-top: 20px;
    border: none;
    border-radius: 20px;
    font-size: 16px;
    cursor: pointer;
}
</style>
</head>

<body>

<div class="container">
<h2>TAMBAH PRODUK</h2>

<form method="POST" enctype="multipart/form-data">

    <label>Nama</label>
    <input type="text" name="nama" required>

    <label>Deskripsi</label>
    <textarea name="deskripsi" required></textarea>

    <label>Harga</label>
    <input type="number" name="harga" required>

    <label>Kategori</label>
    <select name="kategori" required>
        <option value="">--Pilih Kategori--</option>
        <option value="Minuman">Minuman</option>
        <option value="Makanan">Makanan</option>
        <option value="Snack">Snack</option>
    </select>

    <label>Gambar</label>
    <input type="file" name="gambar" accept="image/*" required>

    <button id="btnTambah" name="tambah">Tambah</button>

</form>
</div>

</body>
</html>
