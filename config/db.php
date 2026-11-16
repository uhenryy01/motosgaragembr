<?php
if (!class_exists('Database')) {
    class Database
    {
        private static ?PDO $conn = null;
 
        /** True quando rodando no XAMPP / máquina local. */
        private static function isLocal(): bool
        {
            if (PHP_SAPI === 'cli') {
                return true;
            }
            $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
            $host = preg_replace('/:\d+$/', '', $host);
            return $host === 'localhost' || $host === '127.0.0.1' || $host === '::1'
                || substr($host, -6) === '.local';
        }
 
        public static function config(): array
        {
            if (self::isLocal()) {
                // XAMPP
                return [
                    'host' => 'localhost',
                    'name' => 'garagembr',
                    'user' => 'root',
                    'pass' => '',
                    'local' => true,
                ];
            }
            // InfinityFree
            return [
                'host' => 'sql112.infinityfree.com',
                'name' => 'if0_43312217_garagem_motoss',
                'user' => 'if0_43312217',
                'pass' => '12345670hk',
                'local' => false,
            ];
        }
 
        public static function getConnection(): PDO
        {
            if (self::$conn !== null) {
                return self::$conn;
            }
 
            $c = self::config();
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
 
            try {
                self::$conn = new PDO(
                    "mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4",
                    $c['user'],
                    $c['pass'],
                    $options
                );
            } catch (PDOException $e) {
                // No XAMPP o banco pode ainda não existir: cria e conecta.
                if ($c['local']) {
                    try {
                        $server = new PDO("mysql:host={$c['host']};charset=utf8mb4", $c['user'], $c['pass'], $options);
                        $server->exec('CREATE DATABASE IF NOT EXISTS `' . $c['name'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
                        self::$conn = new PDO(
                            "mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4",
                            $c['user'],
                            $c['pass'],
                            $options
                        );
                    } catch (PDOException $e2) {
                        error_log('Erro de conexão: ' . $e2->getMessage());
                        die('Erro ao conectar ao banco de dados: ' . $e2->getMessage());
                    }
                } else {
                    error_log('Erro de conexão: ' . $e->getMessage());
                    die('Erro ao conectar ao banco de dados.');
                }
            }
 
            self::ensureSchema(self::$conn);
            return self::$conn;
        }
 
    /**
     * Cria as tabelas que estiverem faltando (ex.: vendas, entradas),
     * na ordem certa por causa das chaves estrangeiras.
     */
    private static function ensureSchema(PDO $pdo): void
    {
        $existentes = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $existentes = array_map('strtolower', $existentes);
 
        $tabelas = [
            'usuarios' => "CREATE TABLE IF NOT EXISTS usuarios (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                senha VARCHAR(255) NOT NULL,
                tipo ENUM('admin','cliente') DEFAULT 'cliente',
                criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
 
            'carros' => "CREATE TABLE IF NOT EXISTS carros (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(150) NOT NULL,
                marca VARCHAR(100) DEFAULT NULL,
                modelo VARCHAR(100) DEFAULT NULL,
                ano INT DEFAULT NULL,
                cor VARCHAR(50) DEFAULT NULL,
                km INT DEFAULT NULL,
                preco_inicial DECIMAL(10,2) NOT NULL,
                preco_atual DECIMAL(10,2) NOT NULL,
                imagem VARCHAR(255) DEFAULT NULL,
                descricao TEXT DEFAULT NULL,
                ativo TINYINT(1) DEFAULT 1,
                criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                preco DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                admin_id INT NOT NULL,
                FOREIGN KEY (admin_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
 
            'categoria' => "CREATE TABLE IF NOT EXISTS categoria (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(100) NOT NULL,
                ativo TINYINT(1) NOT NULL DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
 
            'entradas' => "CREATE TABLE IF NOT EXISTS entradas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                carro_id INT NOT NULL,
                quantidade INT NOT NULL DEFAULT 1,
                preco_custo DECIMAL(10,2) NOT NULL,
                fornecedor VARCHAR(200) DEFAULT NULL,
                data_entrada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                usuario_id INT NOT NULL,
                FOREIGN KEY (carro_id) REFERENCES carros(id) ON DELETE CASCADE,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
 
            'vendas' => "CREATE TABLE IF NOT EXISTS vendas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                carro_id INT NOT NULL,
                cliente_nome VARCHAR(150) NOT NULL,
                cliente_email VARCHAR(150) DEFAULT NULL,
                cliente_telefone VARCHAR(50) DEFAULT NULL,
                valor DECIMAL(10,2) NOT NULL,
                data_venda TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                usuario_id INT NOT NULL,
                FOREIGN KEY (carro_id) REFERENCES carros(id) ON DELETE CASCADE,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
 
            'whatsapp_config' => "CREATE TABLE IF NOT EXISTS whatsapp_config (
                id INT AUTO_INCREMENT PRIMARY KEY,
                numero VARCHAR(50) NOT NULL,
                criado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
        ];
 
        foreach ($tabelas as $nome => $sql) {
            if (in_array($nome, $existentes, true)) {
                continue;
            }
            try {
                $pdo->exec($sql);
            } catch (PDOException $e) {
                // Não derruba a página por causa de uma tabela; o erro real aparece na consulta que a usa.
            }
        }
    }
    }
}
return Database::config();
 