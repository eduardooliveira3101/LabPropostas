// script.js — validação de formulário (lógica de escolha de estado removida)
(function () {
  const form = document.getElementById("proposalForm");
  const errorsDiv = document.getElementById("form-errors");

  function showErrors(errors) {
    if (!errors || !errors.length) {
      if (errorsDiv) {
        errorsDiv.style.display = "none";
        errorsDiv.innerHTML = "";
      }
      return;
    }
    if (errorsDiv) {
      errorsDiv.innerHTML =
        "<ul>" + errors.map((e) => "<li>" + e + "</li>").join("") + "</ul>";
      errorsDiv.style.display = "block";
      errorsDiv.scrollIntoView({ behavior: "smooth", block: "center" });
    }
  }

  function validateForm() {
    const errors = [];
    const nameEl = document.getElementById("client_name");
    const enterpriseEl = document.getElementById("enterprise");
    const telEl = document.getElementById("tel");
    const emailEl = document.getElementById("email");
    const initialEl = document.getElementById("initial");
    const finaleEl = document.getElementById("finale");

    const name = nameEl ? nameEl.value.trim() : "";
    const enterprise = enterpriseEl ? enterpriseEl.value.trim() : "";
    const tel = telEl ? telEl.value.trim() : "";
    const email = emailEl ? emailEl.value.trim() : "";
    const initial = initialEl ? initialEl.value : "";
    const finale = finaleEl ? finaleEl.value : "";

    const services = Array.from(
      document.querySelectorAll('input[name="servico[]"]'),
    ).some((i) => i.checked);

    if (!name) errors.push("Por favor, informe seu nome.");
    if (!enterprise) errors.push("Informe o nome da sua empresa.");

    const digitsTel = tel.replace(/\D/g, "");
    if (!digitsTel) {
      errors.push("Informe um número de celular para contato.");
    } else if (digitsTel.length < 10) {
      errors.push("O telefone parece incompleto. Verifique o DDD e o número.");
    }

    const emailOk = /\S+@\S+\.\S+/.test(email);
    if (!email) {
      errors.push("Informe um e‑mail para contato.");
    } else if (!emailOk) {
      errors.push("O e‑mail informado não parece válido.");
    }

    if (!services) errors.push("Selecione pelo menos um tipo de serviço.");

    if (initial && finale && new Date(initial) > new Date(finale)) {
      errors.push("A data de início não pode ser posterior à data de término.");
    }

    return errors;
  }

  form &&
    form.addEventListener("submit", function (e) {
      const errors = validateForm();
      if (errors.length) {
        e.preventDefault();
        showErrors(errors);
      } else {
        showErrors([]);
      }
    });

  form &&
    form.querySelectorAll("input, textarea, select").forEach((el) => {
      el.addEventListener("input", function () {
        if (errorsDiv && errorsDiv.style.display !== "none") showErrors([]);
      });
    });
})();
