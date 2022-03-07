<?php 
require_once 'signature-to-image.php';
$name = $_POST['name'];
$json = $_POST['output']; // From Signature Pad
$img = sigJsonToImage($json);
imagepng($img, "images/$name.signature.png");
imagedestroy($img);
  echo "<div align=\"center\">$name</div>";
  echo('<img src="images/'); 
  echo $name;
  echo ('.signature.png">');
  ?>
