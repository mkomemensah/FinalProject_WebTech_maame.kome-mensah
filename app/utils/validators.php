<?php
// app/utils/validators.php

function validate_registration($data) {
    $errors = [];

    if (empty($data['name']) || !preg_match('/^[a-zA-Z ]+$/', $data['name'])) {
        $errors['name'] = "Name must only contain letters and spaces";
    }

    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }

    if (empty($data['phone']) || !preg_match('/^0[23549][0-9]{8}$/', $data['phone'])) {
        $errors['phone'] = "Phone must be a valid Ghana phone number";
    }

    if (empty($data['password']) || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).{8,}$/', $data['password'])) {
        $errors['password'] = "Password must be min 8 chars, include uppercase, lowercase, number, special char";
    }
    if (isset($data['confirm_password']) && $data['password'] !== $data['confirm_password']) {
        $errors['confirm_password'] = "Passwords do not match";
    }
    return $errors;
}

function validate_login($data) {
    $errors = [];
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format";
    }
    if (empty($data['password'])) {
        $errors['password'] = "Password required";
    }
    return $errors;
}