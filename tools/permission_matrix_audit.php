<?php
$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';
if (is_file($root . '/app/Core/Helpers.php')) require_once $root . '/app/Core/Helpers.php';
if (function_exists('load_env_file')) load_env_file($root . '/.env');
$pdo = new PDO('mysql:host=' . env('DB_HOST','localhost') . ';dbname=' . env('DB_DATABASE', env('DB_NAME','')) . ';charset=utf8mb4', env('DB_USERNAME', env('DB_USER','root')), env('DB_PASSWORD', env('DB_PASS','')), [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$sql = "SELECT t.nome_tipo, COUNT(tp.permissao_id) total FROM tb_tipos_usuarios t LEFT JOIN tb_acl_tipo_usuario_permissoes tp ON tp.tipo_usuario_id=t.id GROUP BY t.id,t.nome_tipo ORDER BY t.prioridade,t.nome_tipo";
foreach ($pdo->query($sql) as $r) echo str_pad($r['nome_tipo'], 22) . ' ' . $r['total'] . " permissoes\n";
echo "\nPara detalhe por perfil, consulte Configuracoes > Permissoes de usuarios.\n";
