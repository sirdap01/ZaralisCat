<?php
include 'koneksi.php';

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM produk WHERE id=$id");
$p = mysqli_fetch_assoc($data);

// UPDATE PRODUK
if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $kategori = $_POST['kategori'];

    // Jika gambar baru diupload
    if ($_FILES['gambar']['name'] != "") {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        move_uploaded_file($tmp, "uploads/" . $gambar);
    } else {
        $gambar = $p['gambar']; // pakai gambar lama
    }

    mysqli_query($conn, "UPDATE produk SET 
        nama='$nama', 
        deskripsi='$deskripsi', 
        harga='$harga', 
        kategori='$kategori', 
        gambar='$gambar'
    WHERE id=$id");

    header("Location: produk.php?success=edit");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Produk</title>

<style>
body {
    background: #eee;
    font-family: Arial, sans-serif;
}

.container {
    width: 380px;
    background: #FFE500;
    margin: 30px auto;
    padding: 20px;
    border-radius: 10px;
}

h2 {
    text-align: center;
    color: purple;
}

label {
    font-weight: bold;
}

input[type=text], input[type=number], textarea, select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
}

#btnUpdate {
    width: 100%;
    padding: 10px;
    background: purple;
    color: white;
    border: none;
    border-radius: 20px;
    margin-top: 20px;
    cursor: pointer;
}
</style>
</head>

<body>

<div class="container">
    <h2>EDIT PRODUK</h2>

    <form method="POST" enctype="multipart/form-data">

        <label>Nama</label>
        <input type="text" name="nama" value="<?= $p['nama'] ?>" required>

        <label>Deskripsi</label>
        <textarea name="deskripsi" required><?= $p['deskripsi'] ?></textarea>

        <label>Harga</label>
        <input type="number" name="harga" value="<?= $p['harga'] ?>" required>

        <label>Kategori</label>
        <select name="kategori">
            <option <?= $p['kategori']=='Minuman'?'selected':'' ?>>Minuman</option>
            <option <?= $p['kategori']=='Makanan'?'selected':'' ?>>Makanan</option>
            <option <?= $p['kategori']=='Snack'?'selected':'' ?>>Snack</option>
        </select>

        <label>Gambar</label>
        <input type="file" name="gambar" accept="image/*">

        <p>Gambar sekarang:</p>
        <img src="uploads/<?= $p['gambar'] ?>" width="150">

        <button id="btnUpdate" name="update">Update</button>
    </form>
</div>

</body>
</html>
