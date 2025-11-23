<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Assets/CSS/cadastro.css">
    <title>Cadastro</title>
</head>

<body>
    <form action="../Back-end/processa_cadastro.php" method="post" enctype="multipart/form-data">
        <p id="login">Criar uma conta</p>

        <label for="nome">Nome
            <input type="text" name="nome" id="nome" placeholder="Nome">
        </label>

        <label for="email">Email
            <input type="email" name="email" id="email" placeholder="Email">
        </label>

        <label for="telefone">Telefone
            <input type="tel" name="telefone" id="telefone" placeholder="Telefone">
        </label>

        <label for="provincia">Província
            <select name="provincia" id="provincia">
                <option value="">Selecione a província</option>
                <option value="Maputo">Maputo</option>
                <option value="Gaza">Gaza</option>
                <option value="Inhambane">Inhambane</option>
                <option value="Sofala">Sofala</option>
                <option value="Manica">Manica</option>
                <option value="Tete">Tete</option>
                <option value="Zambézia">Zambézia</option>
                <option value="Nampula">Nampula</option>
                <option value="Cabo Delgado">Cabo Delgado</option>
                <option value="Niassa">Niassa</option>
            </select>
        </label>

        <label for="senha">Senha
            <input type="password" name="senha" id="senha" placeholder="Senha">
        </label>

        <input type="submit" value="Cadastrar">
        <p>Já tem uma conta? <a href="login.php">Login</a></p>
    </form>

</body>
<script src="../Assets/JS/Cadastro.js"></script>
</html>