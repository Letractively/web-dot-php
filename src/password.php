<?php
namespace password;
function hash($password, $iterations = 8) {
  // Password (PHP >= 5.3 version of http://www.openwall.com/phpass/)
  $random = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
  $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  if ($iterations < 4 || $iterations > 31) $iterations = 8;
  $salt = '$2a$';
  $salt .= chr(ord('0') + $iterations / 10);
  $salt .= chr(ord('0') + $iterations % 10);
  $salt .= '$';
  $i = 0;
  calc:
  $c1 = ord($random[$i++]);
  $salt .= $itoa64[$c1 >> 2];
  $c1 = ($c1 & 0x03) << 4;
  if ($i >= 16) {
    $salt .= $itoa64[$c1];
    return crypt($password, $salt);
  }
  $c2 = ord($random[$i++]);
  $c1 |= $c2 >> 4;
  $salt .= $itoa64[$c1];
  $c1 = ($c2 & 0x0f) << 2;
  $c2 = ord($random[$i++]);
  $c1 |= $c2 >> 6;
  $salt .= $itoa64[$c1];
  $salt .= $itoa64[$c2 & 0x3f];
  goto calc;
}
function check($password, $hash) {
  return crypt($password, $hash) == $hash;
}