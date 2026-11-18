    <?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Caminhos dos arquivos e pastas
$jsonFile = '../database/bikes.json';
$uploadDir = '../uploads/';

// Garante que a pasta database e o arquivo JSON existam
if (!is_dir('../database/')) {
    mkdir('../database/', 0755, true);
}
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Garante que a pasta uploads exista
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Retorna todas as bikes cadastradas no JSON
        $dados = json_decode(file_get_contents($jsonFile), true) ?? [];
        echo json_encode($dados);
        break;

    case 'POST':
        // Recebe os dados enviados via FormData (incluindo a imagem)
        $codigo = $_POST['codigo'] ?? null;
        $modelo = $_POST['modelo'] ?? null;
        $localizacao = $_POST['localizacao'] ?? 'Estação Central';
        $status = $_POST['status'] ?? 'disponivel';
        $imagemPath = null;

        if (!$codigo || !$modelo) {
            http_response_code(400);
            echo json_encode(['erro' => 'Código e Modelo são obrigatórios.']);
            exit;
        }

        // Processa o upload da imagem se houver
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $permitidas)) {
                $nomeArquivo = uniqid('bike_', true) . '.' . $ext;
                $caminhoCompleto = $uploadDir . $nomeArquivo;

                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoCompleto)) {
                    // Caminho relativo salvo no JSON
                    $imagemPath = 'uploads/' . $nomeArquivo;
                }
            } else {
                http_response_code(400);
                echo json_encode(['erro' => 'Formato de imagem inválido. Utilize JPG, PNG ou WebP.']);
                exit;
            }
        }

        // Lê o arquivo JSON atual
        $bikes = json_decode(file_get_contents($jsonFile), true) ?? [];

        // Cria o novo registro da bicicleta
        $novaBike = [
            'id' => time(), // ID único baseado no tempo atual
            'codigo' => $codigo,
            'modelo' => $modelo,
            'localizacao' => $localizacao,
            'status' => $status,
            'imagem' => $imagemPath,
            'criado_em' => date('Y-m-d H:i:s')
        ];

        // Adiciona a nova bike no topo da lista
        array_unshift($bikes, $novaBike);

        // Salva de volta no arquivo JSON com formatação bonita
        file_put_contents($jsonFile, json_encode($bikes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        http_response_code(201);
        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Bike cadastrada com sucesso!',
            'bike' => $novaBike
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['erro' => 'Método não permitido.']);
        break;
}
?>