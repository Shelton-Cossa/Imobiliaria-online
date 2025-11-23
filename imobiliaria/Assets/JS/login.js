document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const emailInput = document.getElementById("email");
    const senhaInput = document.getElementById("senha");

    // Mensagem de feedback
    const feedback = document.createElement("p");
    feedback.style.marginTop = "10px";
    feedback.style.fontWeight = "bold";
    feedback.style.textAlign = "center";
    form.appendChild(feedback);

    form.addEventListener("submit", (e) => {
        const email = emailInput.value.trim();
        const senha = senhaInput.value.trim();

        feedback.textContent = "";
        feedback.style.color = "";

        // Verifica se os campos estão preenchidos
        if (!email || !senha) {
            e.preventDefault();
            feedback.textContent = "Por favor, preencha todos os campos.";
            feedback.style.color = "red";
            return;
        }

        // Verifica formato do email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            feedback.textContent = "Formato de email inválido.";
            feedback.style.color = "red";
            return;
        }

        // Verifica tamanho mínimo da senha
        if (senha.length < 6) {
            e.preventDefault();
            feedback.textContent = "A senha deve ter pelo menos 6 caracteres.";
            feedback.style.color = "red";
            return;
        }

        // ✅ Se tudo estiver correto, o formulário é enviado normalmente
    });
});