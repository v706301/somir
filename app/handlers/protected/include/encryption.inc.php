<?php
  $seed = base64_encode('seed');
  function mc($s,$b64=true){
    global $seed;
    $s = @mcrypt_ecb(MCRYPT_TRIPLEDES, $seed, $s, MCRYPT_ENCRYPT, 64);
    if($b64)
      return base64_encode($s);
    else
      return $s;
  }
  function dc($s,$b64=true){
    global $seed;
    if($b64)
      $s = base64_decode($s);
    $s = trim(@mcrypt_ecb(MCRYPT_TRIPLEDES, $seed, $s, MCRYPT_DECRYPT, 64));
    return $s;
  }
?>