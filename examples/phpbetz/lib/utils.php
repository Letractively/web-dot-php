<?php
function password($password) {
    return hash_hmac('sha512', $password, config::secret);
}