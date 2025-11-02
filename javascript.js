//Show / Hide Password
const forms = document.querySelectorAll('form');
forms.forEach(form => {
  form.addEventListener("submit", event => {
    if (event.submitter && event.submitter.hasAttribute("formnovalidate")) {
      return;
    }

    if (!form.checkValidity()) {
      event.preventDefault();
    }
  
    form.classList.add("was-validated");
  });
});

const toggleButtons = document.querySelectorAll('.eye');

toggleButtons.forEach(button => {
  button.addEventListener('click', () => {
    const parent = button.closest('.form-floating');
    const input = parent.querySelector('.input-password');

    input.type = input.type === 'password' ? 'text' : 'password';

    button.classList.toggle('bi-eye');
    button.classList.toggle('bi-eye-slash');
  });
});