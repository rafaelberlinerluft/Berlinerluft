function pesquisarPedido() {
    $.ajax({
        url: 'pesquisar.php', // Nome do seu arquivo PHP
        type: 'POST',
        data: {
            action: 'pesquisa',
            pedido: document.getElementById('numeroPedido').value // Pega o valor do input
        },
        dataType: 'json', // Especifica que a resposta deve ser JSON
        success: function(response) {
            console.log(response); // Para depuração

            // Manipula a resposta para exibir os dados da pesquisa
            if (response.error) {
                alert(response.error); // Exibe mensagem de erro
            } else {
                // Exibe os dados recebidos
                document.getElementById('resultado').innerText = 
                    "Cliente: " + response.cliente + "\n" + 
                    "Data de Entrada: " + response.data_entrada_colet;

                // Preenche os campos com os valores do banco de dados, se estiverem disponíveis
                if (response.liberado_por) {
                    document.getElementById('liberado_por').value = response.liberado_por;
                } else {
                    document.getElementById('liberado_por').value = ""; // Limpa o campo se não houver valor
                }
                
                if (response.data_liberacao) {
                    document.getElementById('data_liberacao').value = response.data_liberacao;
                } else {
                    document.getElementById('data_liberacao').value = ""; // Limpa o campo se não houver valor
                }
            }
        },
        error: function() {
            alert("Erro na pesquisa."); // Mensagem de erro genérica
        }
    });
}
