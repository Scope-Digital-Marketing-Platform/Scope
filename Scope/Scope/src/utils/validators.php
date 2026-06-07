<?php

/**
 * Şifre güçlü mü? (En az 8 karakter, harf ve rakam içermeli)
 */
function isStrongPassword($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match("/[a-zA-Z]/", $password)) return false; // En az bir harf
    if (!preg_match("/[0-9]/", $password)) return false;    // En az bir rakam
    return true;
}

/**
 * Kullanıcı adı geçerli mi? (Sadece harf, rakam ve alt çizgi. 3-20 karakter)
 */
function isValidUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/**
 * Girilen fiyat geçerli bir pozitif sayı mı?
 */
function isValidPrice($price) {
    return is_numeric($price) && $price >= 0;
}

/**
 * Yüklenen dosyanın uzantısı izin verilenler listesinde mi?
 * Örnek kullanım: isAllowedFileType('model.obj', ['zip', 'rar', 'obj', 'png', 'wav'])
 */
function isAllowedFileType($filename, $allowedExtensions) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $allowedExtensions);
}

/**
 * Temel XSS koruması (Kullanıcıdan gelen metni HTML etiketlerinden temizler)
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>