<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Sistema Imobiliário - Início</title>
    <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
    <!-- Header -->
    <header>
        <div class="logo">Imobiliária Online</div>
        <nav>
            <a href="index.php">Início</a>
            <a href="pesquisar.php">Pesquisar Imóveis</a>
            <a href="sobre.php">Sobre</a>
            <a href="Paginas/login.php" class="btn">Entrar / Cadastrar</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <h1>Encontre o imóvel dos seus sonhos em Moçambique</h1>
        <form action="pesquisar.php" method="get">
            <input type="text" name="cidade" placeholder="Cidade">
            <input type="text" name="tipo" placeholder="Tipo (Casa, Apartamento...)">
            <button type="submit">Pesquisar</button>
        </form>
    </section>

<!-- Seção de Imóveis em Destaque -->
<section class="imoveis destaque">
  <h2>Imóveis em Destaque</h2>
  <div class="cards-container">
    <div class="card">
      <img src="imagens/casa1.jpg" alt="Casa em Maputo">
      <h3>Casa T3 - Maputo</h3>
      <p>Preço: 5.000.000 MT</p>
      <button>Ver Detalhes</button>
    </div>

    <div class="card">
      <img src="imagens/casa2.jpg" alt="Apartamento em Beira">
      <h3>Apartamento T2 - Beira</h3>
      <p>Preço: 2.500.000 MT</p>
      <button>Ver Detalhes</button>
    </div>

    <div class="card">
      <img src="imagens/casa3.jpg" alt="Terreno em Nampula">
      <h3>Terreno - Nampula</h3>
      <p>Preço: 1.200.000 MT</p>
      <button>Ver Detalhes</button>
    </div>
  </div>
</section>

<!-- Seção de Outros Imóveis -->
<section class="imoveis outros">
  <h2>Outros Imóveis</h2>
  <div class="cards-container">
    <div class="card">
      <img src="imagens/casa4.jpg" alt="Apartamento em Tete">
      <h3>Apartamento T1 - Tete</h3>
      <p>Preço: 1.000.000 MT</p>
      <button>Ver Detalhes</button>
    </div>

    <div class="card">
      <img src="imagens/casa5.jpg" alt="Casa em Matola">
      <h3>Casa T4 - Matola</h3>
      <p>Preço: 3.500.000 MT</p>
      <button>Ver Detalhes</button>
    </div>
  </div>
</section>


    <!-- Footer -->
    <footer>
        <p>© 2025 Sistema de Gestão Imobiliária – Todos os direitos reservados.</p>
        <a href="contato.php">Contato</a> |
        <a href="termos.php">Termos de Uso</a> |
        <a href="privacidade.php">Política de Privacidade</a>
    </footer>
</body>

</html>