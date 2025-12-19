<?php
include 'koneksi.php';

$id = $_GET['id'];

if (isset($_POST['update'])) {
    $status = $_POST['status'];
    mysqli_query($conn,"UPDATE pesanan SET status='$status' WHERE id=$id");
    header("Location: pesanan_admin.php");
}

$p = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM pesanan WHERE id=$id"));
?>

<form method="POST">
    <h3>Edit Status</h3>
    <select name="status">
        <option <?= $p['status']=='Pending'?'selected':'' ?>>Pending</option>
        <option <?= $p['status']=='Lunas'?'selected':'' ?>>Lunas</option>
        <option <?= $p['status']=='Batal'?'selected':'' ?>>Batal</option>
    </select>
    <button name="update">Update</button>
</form>
