<?php
$dir1 = 'images/';
$dir2 = 'images/';   // अगर ऐसा कोई फ़ोल्डर है

require 'db_connect.php';
$q = mysqli_query($conn,"SELECT image_path FROM gallery_photos");

echo "<pre>";
while ($r = mysqli_fetch_assoc($q)) {
    foreach (array_filter(array_map('trim', explode(',', $r['image_path']))) as $f) {
        $p1 = (str_starts_with($f,'uploads/')) ? $f : $dir1.$f;
        $exists = file_exists($p1) ? '✅' : (file_exists($dir2.$f) ? '✅ (alt)' : '❌ MISSING');
        echo $f . " --> " . $exists . PHP_EOL;
    }
}
echo "</pre>";
?>
