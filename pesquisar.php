<?php
$servername = "localhost"; // Altere conforme seu servidor
$username = "root"; // Altere conforme seu usuário
$password = "Sofia174"; // Altere conforme sua senha
$dbname = "berlinerluft"; // Altere conforme seu banco de dados

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexão
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obter número do pedido do parâmetro POST
$numero_pedido = $_POST['pedido'];

// Preparar e executar a consulta para buscar o id e os detalhes do pedido
$sql = "SELECT id, razao_faturamento, dt_confirmacao_pedido FROM pedido WHERE nr_pedido = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $numero_pedido);
$stmt->execute();
$result = $stmt->get_result();

// Preparar a resposta
$response = array();

if ($result->num_rows > 0) {
    // Obter dados do pedido
    $row = $result->fetch_assoc();
    $response['cliente'] = $row['razao_faturamento'];
    $response['data_entrada_colet'] = date("d/m/Y", strtotime($row['dt_confirmacao_pedido'])); // Formatar data

    // Buscar os itens na tabela pedido_item com base no pedido_id
    $pedido_id = $row['id'];
    $sql_items = "SELECT item, mda_id FROM pedido_item WHERE pedido_id = ?";
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $pedido_id); // Usar "i" se for um inteiro
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();

    // Adicionar os itens ao array de resposta
    $response['itens'] = array();
    while ($item_row = $result_items->fetch_assoc()) {
        $response['itens'][] = array(
            'item' => $item_row['item'],
            'mda_id' => $item_row['mda_id']
        );
    }

    // Consulta à tabela timeengven para dados de liberação, agora usando nr_pedido
    $sql_lib = "SELECT liberado_por, data_liberacao FROM timeengven WHERE pedido_tven_id = ?";
    $stmt_lib = $conn->prepare($sql_lib);
    $stmt_lib->bind_param("s", $numero_pedido); // Usar "s" para string
    $stmt_lib->execute();
    $result_lib = $stmt_lib->get_result();

    // Obter os dados de liberação como valores individuais
    if ($result_lib->num_rows > 0) {
        $lib_row = $result_lib->fetch_assoc(); // Pega o primeiro resultado
        // Adicionar valores individuais ao array de resposta
        $response['liberado_por'] = $lib_row['liberado_por'];
        $response['data_liberacao'] = date("d/m/Y", strtotime($lib_row['data_liberacao'])); // Formatar a data
    } else {
        $response['liberado_por'] = null;
        $response['data_liberacao'] = null; // Caso não haja dados de liberação
    }
} else {
    $response['error'] = "Pedido não encontrado.";
}

// Fechar conexões
$stmt->close();
$stmt_items->close();
$stmt_lib->close();
$conn->close();

// Retornar resposta em JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
