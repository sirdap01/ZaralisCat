<?php
include 'includes/config.php';

$query = mysqli_query($koneksi, "SELECT id, nama, gambar FROM produk");

echo "<h2>Daftar Gambar Produk di Database</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Nama Produk</th><th>Nama File Gambar</th><th>Preview</th></tr>";

while ($row = mysqli_fetch_assoc($query)) {
    $gambar_path = "uploads/produk/" . $row['gambar'];
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['nama']}</td>";
    echo "<td>{$row['gambar']}</td>";
    echo "<td>";
    if (file_exists($gambar_path)) {
        echo "<img src='$gambar_path' width='100'><br>";
        echo "<span style='color:green'>✓ File exists</span>";
    } else {
        echo "<span style='color:red'>✗ File not found</span><br>";
        echo "Path: $gambar_path";
    }
    echo "</td>";
    echo "</tr>";
}

echo "</table>";
?>
```