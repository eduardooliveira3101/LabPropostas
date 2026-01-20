// script.js — lógica de cidades, troca de estado e validação amigável
(function () {
  const stateCities = {
    bahia: ["Salvador", "Feira de Santana", "Ilhéus"],
    ceara: ["Fortaleza", "Sobral", "Juazeiro do Norte"],
    maranhao: ["São Luís", "Imperatriz", "Caxias"],
    piaui: ["Teresina", "Parnaíba", "Picos"],
    pernambuco: ["Recife", "Olinda", "Caruaru"],
    alagoas: ["Maceió", "Arapiraca", "Penedo"],
    sergipe: ["Aracaju", "Nossa Senhora do Socorro", "Lagarto"],
    paraiba: ["João Pessoa", "Campina Grande", "Sousa"],
    "rio-grande-do-norte": ["Natal", "Mossoró", "Parnamirim"],
  };

  const form = document.getElementById("proposalForm");
  const cityContainer = document.getElementById("city-container");
  const citySelect = document.getElementById("city");
  const changeBtn = document.getElementById("change-state-btn");
  const errorsDiv = document.getElementById("form-errors");

  function clearCities() {
    if (!citySelect) return;
    citySelect.innerHTML = '<option value="">Selecione uma cidade</option>';
  }

  function setRadioWrappersDisabled(stateValue) {
    document.querySelectorAll('input[name="state"]').forEach((r) => {
      const wrapper = r.closest(".radio-wrapper");
      if (!wrapper) return;
      if (r.value !== stateValue) {
        r.disabled = true;
        wrapper.classList.add("disabled");
      } else {
        wrapper.classList.remove("disabled");
      }
    });
  }

  function populateCities(stateValue) {
    if (!citySelect || !cityContainer) return;
    clearCities();
    const list = stateCities[stateValue] || [];
    list.forEach((c) => {
      const opt = document.createElement("option");
      opt.value = c;
      opt.textContent = c;
      citySelect.appendChild(opt);
    });

    if (list.length) {
      cityContainer.style.display = "block";
      citySelect.setAttribute("required", "required");
      changeBtn && (changeBtn.style.display = "block");
    } else {
      cityContainer.style.display = "none";
      citySelect.removeAttribute("required");
      changeBtn && (changeBtn.style.display = "none");
    }

    setRadioWrappersDisabled(stateValue);
  }

  document.querySelectorAll('input[name="state"]').forEach((radio) => {
    radio.addEventListener("change", function () {
      if (this.checked) populateCities(this.value);
    });
  });

  changeBtn &&
    changeBtn.addEventListener("click", function () {
      document.querySelectorAll('input[name="state"]').forEach((r) => {
        r.disabled = false;
        const wrapper = r.closest(".radio-wrapper");
        if (wrapper) wrapper.classList.remove("disabled");
        r.checked = false;
      });
      clearCities();
      cityContainer.style.display = "none";
      citySelect.removeAttribute("required");
      changeBtn.style.display = "none";
    });

  function showErrors(errors) {
    if (!errors || !errors.length) {
      errorsDiv.style.display = "none";
      errorsDiv.innerHTML = "";
      return;
    }
    errorsDiv.innerHTML =
      "<ul>" + errors.map((e) => "<li>" + e + "</li>").join("") + "</ul>";
    errorsDiv.style.display = "block";
    errorsDiv.scrollIntoView({ behavior: "smooth", block: "center" });
  }

  function validateForm() {
    const errors = [];
    const name = document.getElementById("name")
      ? document.getElementById("name").value.trim()
      : "";
    const enterprise = document.getElementById("enterprise")
      ? document.getElementById("enterprise").value.trim()
      : "";
    const tel = document.getElementById("tel")
      ? document.getElementById("tel").value.trim()
      : "";
    const email = document.getElementById("email")
      ? document.getElementById("email").value.trim()
      : "";
    const initial = document.getElementById("initial")
      ? document.getElementById("initial").value
      : "";
    const finale = document.getElementById("finale")
      ? document.getElementById("finale").value
      : "";
    const services = Array.from(
      document.querySelectorAll('input[name="servico"]'),
    ).some((i) => i.checked);
    const selectedState = document.querySelector('input[name="state"]:checked');
    const selectedCity = citySelect ? citySelect.value : "";

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
    if (!selectedState)
      errors.push("Escolha o estado onde o serviço será realizado.");
    if (
      cityContainer &&
      cityContainer.style.display !== "none" &&
      !selectedCity
    )
      errors.push("Escolha uma cidade do estado selecionado.");

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

  document.addEventListener("DOMContentLoaded", function () {
    const checked = document.querySelector('input[name="state"]:checked');
    if (checked) populateCities(checked.value);
  });
})();
