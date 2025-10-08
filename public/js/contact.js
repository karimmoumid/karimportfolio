document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".contact-form");
    const consentCheckbox = document.getElementById("rgpdConsent");
    const submitButton = document.querySelector('button[type="submit"]');

    if (!form) {
        console.error("⚠️ Formulaire non trouvé");
        return;
    }

    const rules = {
        senderName: { required: "Votre nom ne peut pas être vide" },
        senderEmail: {
            required: "Veuillez renseigner votre adresse email.",
            email: "L'adresse email n'est pas valide"
        },
        subject: { required: "Votre sujet ne peut pas être vide" },
        content: { required: "Votre message ne peut pas être vide" }
    };

    function validateField(field) {
        const nameMatch = field.name.match(/\[(.+)\]/);
        const name = nameMatch ? nameMatch[1] : field.name;
        const value = field.value.trim();
        let errorMessage = "";

        if (rules[name]) {
            if (rules[name].required && value === "") {
                errorMessage = rules[name].required;
            } else if (rules[name].email && value !== "") {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    errorMessage = rules[name].email;
                }
            }
        }

        let errorDiv = field.parentNode.querySelector(".error-message");
        if (!errorDiv) {
            errorDiv = document.createElement("div");
            errorDiv.classList.add("error-message", "invalid-feedback");
            field.parentNode.appendChild(errorDiv);
        }

        errorDiv.textContent = errorMessage;
        field.classList.toggle("is-invalid", !!errorMessage);

        return !errorMessage;
    }

    // --- Validation complète ---
    form.addEventListener("submit", function (e) {
        let isValid = true;

        // Validation des champs texte
        form.querySelectorAll("input:not([type='checkbox']), textarea").forEach(field => {
            if (!validateField(field)) isValid = false;
        });

        // ✅ Vérification RGPD
        if (!consentCheckbox || !consentCheckbox.checked) {
            e.preventDefault(); // Bloque l’envoi AVANT TOUT
            alert("⚠️ Vous devez accepter le traitement de vos données pour envoyer le message.");
            consentCheckbox.focus();
            return; // stop ici
        }

        // Bloquer si autre champ invalide
        if (!isValid) {
            e.preventDefault();
        }
    });

    // Validation au blur
    form.querySelectorAll("input:not([type='checkbox']), textarea").forEach(field => {
        field.addEventListener("blur", () => validateField(field));
    });

    // Gestion dynamique du bouton submit
    if (consentCheckbox && submitButton) {
        function toggleSubmitButton() {
            submitButton.disabled = !consentCheckbox.checked;
            submitButton.classList.toggle("btn-primary", consentCheckbox.checked);
            submitButton.classList.toggle("btn-secondary", !consentCheckbox.checked);
        }

        consentCheckbox.addEventListener("change", toggleSubmitButton);
        toggleSubmitButton();
    }
});
