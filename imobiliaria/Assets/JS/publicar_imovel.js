// Espera o carregamento total do DOM
document.addEventListener("DOMContentLoaded", () => {

    /* ==============================
       PRÉ-VISUALIZAÇÃO DAS IMAGENS
    ============================== */

    const previewPrincipal = document.getElementById("previewPrincipal");
    const previewOutras = document.getElementById("previewOutras");
    const inputPrincipal = document.getElementById("imagem_principal");
    const inputOutras = document.getElementById("outras_imagens");

    // Função genérica para exibir pré-visualização de imagens
    function mostrarPreview(input, container, tamanho = 180) {
        if (!input || !container) return;

        container.innerHTML = ""; // limpa previews anteriores

        const files = input.files;
        if (!files || files.length === 0) return;

        [...files].forEach((file) => {
            if (!file.type.startsWith("image/")) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = document.createElement("img");
                img.src = e.target.result;
                img.alt = "Pré-visualização";
                img.style.width = `${tamanho}px`;
                img.style.height = `${tamanho}px`;
                img.style.objectFit = "cover";
                img.style.borderRadius = "10px";
                img.style.margin = "5px";
                img.style.boxShadow = "0 0 5px rgba(0,0,0,0.2)";
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        });
    }

    // Eventos de mudança de input
    if (inputPrincipal) {
        inputPrincipal.addEventListener("change", () => {
            mostrarPreview(inputPrincipal, previewPrincipal, 200);
        });
    }

    if (inputOutras) {
        inputOutras.addEventListener("change", () => {
            mostrarPreview(inputOutras, previewOutras, 140);
        });
    }

    /* ==============================
       VALIDAÇÃO DO FORMULÁRIO
    ============================== */

    const form = document.getElementById("formPublicar");

    if (form) {
        form.addEventListener("submit", (e) => {
            const tipo = document.getElementById("tipo").value.trim();
            const finalidade = document.getElementById("finalidade").value.trim();
            const preco = document.getElementById("preco").value.trim();
            const endereco = document.getElementById("endereco").value.trim();
            const descricao = document.getElementById("descricao").value.trim();

            if (!tipo || !finalidade || !preco || !endereco || !descricao) {
                e.preventDefault();
                alert("Por favor, preencha todos os campos obrigatórios.");
                return;
            }

            const precoValor = parseFloat(preco);
            if (isNaN(precoValor) || precoValor <= 0) {
                e.preventDefault();
                alert("Por favor, insira um preço válido.");
            }
        });
    }
});
