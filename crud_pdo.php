<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
</head>
<?php
// dica de leitura: https://imasters.com.br/back-end/como-usar-pdo-com-banco-de-dados-mysql

/* 
Realize as linhas abaixo no QueryBrowser ou Workbench ou PHPMyAdmin
  CREATE DATABASE bdsite;
  use bdsite;  
  CREATE TABLE `tb_funcionario` (
  `id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `nome` varchar(250) NOT NULL,
  `profissao` varchar(250) NOT NULL
  )

 */

// dados da conexao - Note que dependendo do computador os nomes são diferentes.
// Na etec o padrão é login:root senha:root
$database = 'bdsite';
$db_user = 'root';
$db_password = '';//no xampp deixar vazio, na etec deixar root


// instancia a classe, para USBSERVER, usar porta 3307 do mysql:
// $conn = new PDO('mysql:host=localhost:3307;dbname='. $database, $db_user, $db_password);
//depois é só trabalhar com o $conn

// instancia a classe==> para XAMPP:   MUDE PARA ESTE SE ESTIVER NO XAMPP
$conn = new PDO('mysql:host=localhost;dbname='. $database, $db_user, $db_password);


// pagina resgatada da URL, usando ternário. É um IF em uma linha só.
$page = (isset($_GET['page'])) ? $_GET['page'] : NULL;


// id restagado da URL
$id = (isset($_GET['id'])) ? (int) $_GET['id'] : 0;

// inicia a mensagem vazia
$mensagem = '';


// verifica se o BOTÃO do formulario foi acionado, existe o submit?

if (isset($_POST['submit'])) {
    //se sim
    // prepara os dados do formulario, Utilizando ternário
    $post_nome = (isset($_POST['nome'])) ? $_POST['nome'] : 'NULL';
    $post_profissao = (isset($_POST['profissao'])) ? $_POST['profissao'] : 'NULL';
    $post_id = (isset($_POST['id'])) ? (int) $_POST['id'] : 0;

    // verifica se foi o formulario de INSERT submit VALUE SALVAR
    if ($_POST['submit'] == 'Salvar') {

        // prepara o SQL, perceba aqui o $conn que é o objeto PDO, assim podemos usar o prepare
        $sql = $conn->prepare('INSERT INTO tb_funcionario (nome, profissao)VALUES(:nome, :profissao)');

        // Prepara os dados do formulario
        $data = array(
            ':nome' => $post_nome,
            ':profissao' => $post_profissao,
        );

        try {

            // executa o SQL
            $sql->execute($data);

            // Mensagem de alerta
            $mensagem = alert('Registro Adicionado!');
            
        } catch (PDOException $e) {

            // mostra o erro
            $e->getMessage();
        }
    }


    // verifica se foi o formulario de UPDATE
    if ($_POST['submit'] == 'Alterar dados') {

        // prepara o SQL
        $sql = $conn->prepare('UPDATE tb_funcionario SET nome= :nome, profissao = :profissao WHERE id= :id');


// Prepara os dados do formulario        
        $data = array(
            ':nome' => $post_nome,
            ':profissao' => $post_profissao,
            ':id' => $post_id,
        );

        try {

        // executa
            $sql->execute($data);

        // mensagem
            $mensagem = alert('Registro Alterado com Sucesso!');
        } catch (PDOException $e) {

        // mostra o erro
            $e->getMessage("Erro no banco de dados (PDO)");
        }
    }
}


/* * ***************************************
 * 
 * SELECT    mostrar
 * 
 * **************************************** */


// prepara o SQL
$sql = $conn->prepare('SELECT * FROM tb_funcionario ORDER BY id DESC');

try {
    // executa o SQL
    $sql->execute();

// Cria uma variavel de listagem
//PDOStatement::fetchAll — Retorna uma matriz contendo todas as linhas definidas pelo resultado
//PDO::FETCH_OBJ: retorna um objeto anônimo com nomes de propriedade que correspondem aos nomes das colunas retornados no seu conjunto de resultados
    $listar = $sql->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {

    // mostra o erro
    $e->getMessage();
}


// verifica se o id foi acionado para o update
if ($id > 0) {

    // prepara o SQL
    $sql = $conn->prepare('SELECT * FROM tb_funcionario WHERE id= :id');

    // Prepara os dados
    $data = array(':id' => $id);

    try {
        // executa o SQL
        $sql->execute($data);

    // Prepara o fetch
        $row = $sql->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
    // mostra o erro
        $e->getMessage();
    }
}

/*
 * prepara os dados para serem mostrados
 * no php 5.3 as variaveis devem ser declaradas
 */
$value_id = (isset($row->id)) ? $row->id : FALSE;
$value_nome = (isset($row->nome)) ? $row->nome : '';
$value_profissao = (isset($row->profissao)) ? $row->profissao : '';




/* * ***************************************
 * 
 * AREA DE DELETE
 * 
 * **************************************** */
if (($page == 'delete') && $id > 0) {

    // executa o SQL para excluir
    $sql = $conn->prepare('DELETE FROM tb_funcionario WHERE id= :id');

// prepara os dados
    $data = array(':id' => $id);

    try {
        // executa o SQL
        $sql->execute($data);

// Mostra a mensagem de erro
        $mensagem = alert('Registro deletado!');
    } catch (PDOException $e) {

        // mostra a mensagem
        $e->getMessage();
    }
}



////////////////////////////////////////////////////////

/**  javascript alert() */
function alert($texto, $redirect = TRUE) {
    $redirect = ($redirect) ? 'location.href="crud_pdo.php";' : '';
    return '
        <script type="text/javascript">
        alert("' . $texto . '");
        ' . $redirect . '
        </script>
    ';
}

/* * ***************************************
 * 
 * conteúdo da pagina 
 * 
 * **************************************** */


echo '<a href="crud_pdo.php">Lista de Funcionários Cadastrados</a> |' . "\n";
echo '<a href="crud_pdo.php?page=Salvar">Cadastrar Novo Funcionário</a> | ' . "\n";
echo '<hr />' . "\n";
echo $mensagem;



/* * ***************************************
 * 
 * Listar/Mostrar registros 
 * 
 * **************************************** */

if ($page == NULL) {

    echo "<h1>Lista de Funcionários Cadastrados</h1>\n";

    if (count($listar) > 0):

        foreach ($listar as $row):
            echo "<p>\n";
            echo 'ID: ', $row->id, "<br>\n";
            echo 'Funcionário: ', $row->nome, "<br>\n";
            echo 'Profissão: ', $row->profissao, "<br>\n";
            echo '<a href="crud_pdo.php?page=Alterar dados&id=' . $row->id . '">Editar Dados</a> | ' . "\n";
            echo '<a href="crud_pdo.php?page=delete&id=' . $row->id . '"">Remover Funcionário</a>' . "\n";
            echo "</p>\n";
        endforeach;


    else:

        echo 'Adicione um registro <a href="crud_pdo.php?page=Salvar">Aqui</a>';

    endif;

/* * ***************************************
* 
* Adicionar registro 
* 
* **************************************** */
}elseif ($page == 'Salvar') {

    echo "<h1>Cadastro de Funcionário</h1>\n";

    echo '<form method="post">' . "\n";
    echo 'Nome:<br>' . "\n";
    echo '<input type="text" name="nome" style="width: 350px" /><br>' . "\n";
    echo 'Profissao:<br>' . "\n";
    echo '<input type="text" name="profissao" style="width: 350px"><br>' . "\n";
    echo '<input type="submit" name="submit" value="Salvar">' . "\n";
    echo '</form>' . "\n";


/* * ***************************************
* 
* Editar registro
* 
* **************************************** */
} elseif ($page == 'Alterar dados') {

    echo "<h1>Editar Dados</h1>\n";

    if ($value_id) {

        echo '<form method="post">' . "\n";
        echo '<input type="hidden" name="id" value="' . $value_id . '"><br>' . "\n";
        echo 'Nome:<br>' . "\n";
        echo '<input type="text" name="nome" readonly value="' . $value_nome . '" style="width: 350px"><br>' . "\n";
       /* se desejar que possa editar o nome do funcionario, remova o readonly da linha acima */
        echo 'Profissao:<br>' . "\n";
        echo '<input type="text" name="profissao" value="' . $value_profissao . '" style="width: 350px"><br>' . "\n";
        echo '<input type="submit" name="submit" value="Alterar dados">' . "\n";
        echo '</form>' . "\n";
    } else {

        echo 'Registro não existe';
    }
}
?>
</html>