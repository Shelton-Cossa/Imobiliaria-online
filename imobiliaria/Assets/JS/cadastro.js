document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");

    const nomeInput = document.getElementById("nome");
    const emailInput = document.getElementById("email");
    const telefoneInput = document.getElementById("telefone");
    const provinciaInput = document.getElementById("provincia");
    const senhaInput = document.getElementById("senha");

    const feedback = document.createElement("p");
    feedback.style.marginTop = "10px";
    feedback.style.fontWeight = "bold";
    feedback.style.textAlign = "center";
    form.appendChild(feedback);

    form.addEventListener("submit", async (e) => {
        e.preventDefault(); // evita envio padrão

        feedback.textContent = "";
        feedback.style.color = "";

        const nome = nomeInput.value.trim();
        const email = emailInput.value.trim();
        const telefone = telefoneInput.value.trim();
        const provincia = provinciaInput.value;
        const senha = senhaInput.value.trim();

        if (!nome || !email || !telefone || !provincia || !senha) {
            feedback.textContent = "Por favor, preencha todos os campos.";
            feedback.style.color = "red";
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            feedback.textContent = "Email inválido.";
            feedback.style.color = "red";
            return;
        }

        if (senha.length < 6) {
            feedback.textContent = "A senha deve ter no mínimo 6 caracteres.";
            feedback.style.color = "red";
            return;
        }

        const telRegex = /^\+?\d{1,4}\s?\d{2}\s?\d{3}\s?\d{4}$/;
        if (!telRegex.test(telefone)) {
            feedback.textContent = "Telefone inválido. Use formato +258 84 000 0000.";
            feedback.style.color = "red";
            return;
        }
        form.submit();
    });
});