<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../Assets/CSS/login.css">
    <title>Login</title>
</head>

<body>
    <form action="../Back-end/processa_login.php" method="post">
        <h1 id="login">Login</h1> <label for="email">Email</label> <input type="email" name="email" id="email"
            placeholder="Digite seu email" required> <label for="senha">Senha</label> <input type="password"
            name="senha" id="senha" placeholder="Digite sua senha" required> <input type="submit" value="Entrar">
        <p>Esqueceu a Senha? <a href="#">Clique aqui</a></p>
        <p>Ainda não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
    </form>
    <script src="../Assets/JS/login.js"></script>
</body>

</html>