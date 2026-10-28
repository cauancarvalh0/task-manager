<?php
require_once __DIR__ . '/includes/auth.php';

redirecionar(usuario_logado() ? 'dashboard.php' : 'login.php');
